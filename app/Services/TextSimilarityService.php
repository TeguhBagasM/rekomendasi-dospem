<?php

namespace App\Services;

use App\Models\PengajuanJudul;

class TextSimilarityService
{
    private float $threshold = 0.75;

    private array $stopwords = [
        'dan', 'untuk', 'pada', 'dengan', 'yang', 'menggunakan', 'dalam', 'adalah', 'di', 'ke', 'dari',
        'ini', 'itu', 'atau', 'juga', 'sudah', 'akan', 'bisa', 'dapat', 'sebagai', 'karena', 'oleh',
        'tentang', 'antara', 'the', 'a', 'an', 'of', 'to', 'in', 'on', 'for', 'with', 'is', 'are',
    ];

    public function buildCorpus(array $documents): array
    {
        $tokenizedDocuments = [];

        foreach ($documents as $document) {
            $tokenizedDocuments[] = $this->preprocess((string) $document);
        }

        $documentCount = count($tokenizedDocuments);

        if ($documentCount === 0) {
            return [];
        }

        $documentFrequency = [];

        foreach ($tokenizedDocuments as $tokens) {
            foreach (array_unique($tokens) as $term) {
                $documentFrequency[$term] = ($documentFrequency[$term] ?? 0) + 1;
            }
        }

        $matrix = [];

        foreach ($tokenizedDocuments as $documentIndex => $tokens) {
            if ($tokens === []) {
                continue;
            }

            $termFrequency = $this->termFrequency($tokens);

            foreach ($termFrequency as $term => $frequency) {
                $idf = log(($documentCount + 1) / (($documentFrequency[$term] ?? 0) + 1)) + 1;
                $matrix[$term][$documentIndex] = $frequency * $idf;
            }
        }

        return $matrix;
    }

    public function cosineSimilarity(array $vecA, array $vecB): float
    {
        $dotProduct = 0.0;
        $magnitudeA = 0.0;
        $magnitudeB = 0.0;

        $allTerms = array_unique(array_merge(array_keys($vecA), array_keys($vecB)));

        foreach ($allTerms as $term) {
            $valueA = $vecA[$term] ?? 0.0;
            $valueB = $vecB[$term] ?? 0.0;

            $dotProduct += $valueA * $valueB;
            $magnitudeA += $valueA ** 2;
            $magnitudeB += $valueB ** 2;
        }

        $magnitudeA = sqrt($magnitudeA);
        $magnitudeB = sqrt($magnitudeB);

        if ($magnitudeA === 0.0 || $magnitudeB === 0.0) {
            return 0.0;
        }

        return $dotProduct / ($magnitudeA * $magnitudeB);
    }

    public function getSimilarityScore(string $judulMahasiswa, array $dosenCorpusTexts): float
    {
        $judulMahasiswa = trim($judulMahasiswa);

        if ($judulMahasiswa === '') {
            return 0.0;
        }

        $joinedCorpus = trim(implode(' ', array_filter(array_map('trim', $dosenCorpusTexts))));

        if ($joinedCorpus === '') {
            return 0.0;
        }

        $corpus = [$judulMahasiswa, $joinedCorpus];
        $matrix = $this->buildCorpus($corpus);

        return $this->cosineSimilarity(
            $this->extractDocumentVector($matrix, 0),
            $this->extractDocumentVector($matrix, 1)
        );
    }

    public function getSimilarityScoresBatch(string $queryDocument, array $targetDocuments): array
    {
        $queryDocument = trim($queryDocument);
        if ($queryDocument === '' || empty($targetDocuments)) {
            return array_fill(0, count($targetDocuments), 0.0);
        }

        $corpus = [$queryDocument];
        foreach ($targetDocuments as $target) {
            $corpus[] = trim((string) $target);
        }

        $matrix = $this->buildCorpus($corpus);
        if (empty($matrix)) {
            return array_fill(0, count($targetDocuments), 0.0);
        }

        $queryVector = $this->extractDocumentVector($matrix, 0);
        $scores = [];

        foreach ($targetDocuments as $index => $target) {
            $docIndex = $index + 1;
            $targetVector = $this->extractDocumentVector($matrix, $docIndex);
            $scores[$index] = $this->cosineSimilarity($queryVector, $targetVector);
        }

        return $scores;
    }

    public function calculateSimilarity(string $text1, string $text2): float
    {
        return $this->getSimilarityScore($text1, [$text2]);
    }

    public function checkSimilarity(string $judulBaru, ?int $excludeId = null): array
    {
        $judulBaru = trim($judulBaru);

        if ($judulBaru === '') {
            return [
                'is_similar' => false,
                'percent' => 0,
                'matched_title' => null,
            ];
        }

        $existingTitles = PengajuanJudul::query()
            ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
            ->pluck('judul_skripsi')
            ->filter()
            ->values();

        if ($existingTitles->isEmpty()) {
            return [
                'is_similar' => false,
                'percent' => 0,
                'matched_title' => null,
            ];
        }

        $normalizedBaru = $this->normalize($judulBaru);

        foreach ($existingTitles as $existingTitle) {
            if ($normalizedBaru === $this->normalize($existingTitle)) {
                return [
                    'is_similar' => true,
                    'percent' => 100.0,
                    'matched_title' => $existingTitle,
                ];
            }
        }

        $highestSimilarity = 0.0;
        $matchedTitle = null;

        $titlesArray = $existingTitles->toArray();
        $similarities = $this->getSimilarityScoresBatch($judulBaru, $titlesArray);

        foreach ($similarities as $index => $similarity) {
            if ($similarity > $highestSimilarity) {
                $highestSimilarity = $similarity;
                $matchedTitle = $titlesArray[$index];
            }
        }

        return [
            'is_similar' => $highestSimilarity >= $this->threshold,
            'percent' => round($highestSimilarity * 100, 2),
            'matched_title' => $matchedTitle,
        ];
    }

    private function extractDocumentVector(array $matrix, int $documentIndex): array
    {
        $vector = [];

        foreach ($matrix as $term => $weights) {
            $vector[$term] = $weights[$documentIndex] ?? 0.0;
        }

        return $vector;
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

        return array_values(array_filter($tokens, function (string $token): bool {
            return $token !== '' && ! in_array($token, $this->stopwords, true);
        }));
    }

    private function termFrequency(array $document): array
    {
        $counts = array_count_values($document);
        $total = max(count($document), 1);
        $tf = [];

        foreach ($counts as $term => $count) {
            $tf[$term] = $count / $total;
        }

        return $tf;
    }
}