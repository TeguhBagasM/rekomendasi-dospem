<?php

namespace App\Services;

use App\Models\PengajuanJudul;

class TextSimilarityService
{
    private float $threshold = 0.75;

    private array $stopwords = [
        'dan', 'yang', 'di', 'ke', 'dari', 'untuk', 'pada', 'dengan', 'atau', 'sebagai',
        'dalam', 'adalah', 'itu', 'ini', 'karena', 'saat', 'oleh', 'tentang', 'antara',
        'the', 'a', 'an', 'of', 'to', 'in', 'on', 'for', 'with', 'is', 'are',
    ];

    public function calculateSimilarity(string $text1, string $text2): float
    {
        if ($this->normalize($text1) === $this->normalize($text2)) {
            return 1.0;
        }

        $tokens1 = $this->preprocess($text1);
        $tokens2 = $this->preprocess($text2);

        if ($tokens1 === [] || $tokens2 === []) {
            return 0.0;
        }

        // Gunakan kedua dokumen sebagai corpus agar IDF bermakna
        $documents = [$tokens1, $tokens2];
        $vocabulary = $this->buildVocabulary($documents);

        $vector1 = $this->buildTfIdfVector($tokens1, $documents, $vocabulary);
        $vector2 = $this->buildTfIdfVector($tokens2, $documents, $vocabulary);

        return $this->cosineSimilarity($vector1, $vector2);
    }

    /**
     * Cek similarity judul baru terhadap seluruh corpus existing.
     * Ini yang paling penting: semua judul diload dulu, lalu IDF dihitung
     * dari seluruh corpus, bukan pair by pair.
     */
    public function checkSimilarity(string $judulBaru, ?int $excludeId = null): array
    {
        $judulBaru = trim($judulBaru);

        if ($judulBaru === '') {
            return [
                'is_similar' => false,
                'percent'     => 0,
                'matched_title' => null,
            ];
        }

        $existingTitles = PengajuanJudul::query()
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->pluck('judul_skripsi', 'id')
            ->filter()
            ->values();

        if ($existingTitles->isEmpty()) {
            return [
                'is_similar'    => false,
                'percent'       => 0,
                'matched_title' => null,
            ];
        }

        $normalizedBaru = $this->normalize($judulBaru);

        // Exact match check dulu
        foreach ($existingTitles as $existingTitle) {
            if ($normalizedBaru === $this->normalize($existingTitle)) {
                return [
                    'is_similar'    => true,
                    'percent'       => 100.0,
                    'matched_title' => $existingTitle,
                ];
            }
        }

        // Bangun corpus dari seluruh judul existing + judul baru
        // agar IDF dihitung secara representatif
        $allTokens = $existingTitles
            ->map(fn ($title) => $this->preprocess($title))
            ->filter(fn ($tokens) => $tokens !== [])
            ->values()
            ->toArray();

        $newTokens = $this->preprocess($judulBaru);

        if ($newTokens === []) {
            return [
                'is_similar'    => false,
                'percent'       => 0,
                'matched_title' => null,
            ];
        }

        // Corpus = semua judul existing + judul baru
        $corpus    = array_merge($allTokens, [$newTokens]);
        $vocabulary = $this->buildVocabulary($corpus);

        // Hitung vektor judul baru sekali saja
        $vectorBaru = $this->buildTfIdfVector($newTokens, $corpus, $vocabulary);

        $highestSimilarity = 0.0;
        $matchedTitle      = null;

        foreach ($existingTitles as $existingTitle) {
            $tokens = $this->preprocess($existingTitle);

            if ($tokens === []) {
                continue;
            }

            $vectorExisting = $this->buildTfIdfVector($tokens, $corpus, $vocabulary);
            $similarity     = $this->cosineSimilarity($vectorBaru, $vectorExisting);

            if ($similarity > $highestSimilarity) {
                $highestSimilarity = $similarity;
                $matchedTitle      = $existingTitle;
            }
        }

        return [
            'is_similar'    => $highestSimilarity >= $this->threshold,
            'percent'       => round($highestSimilarity * 100, 2),
            'matched_title' => $matchedTitle,
        ];
    }

    private function normalize(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s]/u', '', $text) ?? '';

        return trim($text);
    }

    private function preprocess(string $text): array
    {
        $text = $this->normalize($text);

        if ($text === '') {
            return [];
        }

        $tokens = preg_split('/\s+/', $text) ?: [];

        return array_values(array_filter($tokens, function (string $token) {
            return $token !== '' && ! in_array($token, $this->stopwords, true);
        }));
    }

    private function buildVocabulary(array $documents): array
    {
        $vocabulary = [];

        foreach ($documents as $document) {
            foreach ($document as $token) {
                $vocabulary[$token] = true;
            }
        }

        return array_keys($vocabulary);
    }

    private function buildTfIdfVector(array $document, array $documents, array $vocabulary): array
    {
        $tf     = $this->termFrequency($document);
        $total  = count($documents);
        $vector = [];

        foreach ($vocabulary as $term) {
            $docsWithTerm = 0;

            foreach ($documents as $doc) {
                if (in_array($term, $doc, true)) {
                    $docsWithTerm++;
                }
            }

            $idf            = $docsWithTerm > 0
                ? log(($total + 1) / ($docsWithTerm + 1)) + 1
                : 0.0;

            $vector[$term] = ($tf[$term] ?? 0.0) * $idf;
        }

        return $vector;
    }

    private function termFrequency(array $document): array
    {
        $counts = array_count_values($document);
        $total  = max(count($document), 1);
        $tf     = [];

        foreach ($counts as $term => $count) {
            $tf[$term] = $count / $total;
        }

        return $tf;
    }

    private function cosineSimilarity(array $vector1, array $vector2): float
    {
        $dotProduct = 0.0;
        $magnitude1 = 0.0;
        $magnitude2 = 0.0;

        foreach ($vector1 as $term => $value1) {
            $value2      = $vector2[$term] ?? 0.0;
            $dotProduct += $value1 * $value2;
            $magnitude1 += $value1 ** 2;
        }

        foreach ($vector2 as $value2) {
            $magnitude2 += $value2 ** 2;
        }

        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);

        if ($magnitude1 === 0.0 || $magnitude2 === 0.0) {
            return 0.0;
        }

        return $dotProduct / ($magnitude1 * $magnitude2);
    }
}