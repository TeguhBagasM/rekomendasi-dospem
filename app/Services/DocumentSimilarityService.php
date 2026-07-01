<?php

namespace App\Services;

class DocumentSimilarityService
{
    public function __construct(
        private readonly TfidfService $tfidfService,
        private readonly CosineSimilarityService $cosineSimilarityService,
    ) {}

    /**
     * Bandingkan satu dokumen terhadap satu dokumen lain.
     */
    public function compareOneToOne(string $textA, string $textB): float
    {
        $scores = $this->compareOneToMany($textA, [$textB]);

        return $scores[0] ?? 0.0;
    }

    /**
     * Bandingkan satu dokumen query terhadap banyak dokumen target
     * dalam satu korpus TF-IDF yang sama, agar nilai IDF konsisten
     * antar seluruh target (mis. 1 judul skripsi vs N dokumen_tfidf dosen).
     *
     * Return: array<int, float> — index selaras dengan $targets.
     */
    public function compareOneToMany(string $query, array $targets): array
    {
        $query = trim($query);

        if ($query === '' || $targets === []) {
            return array_fill(0, count($targets), 0.0);
        }

        $documents = array_merge([$query], array_map(fn ($t): string => trim((string) $t), $targets));
        $matrix = $this->tfidfService->buildMatrix($documents);

        if ($matrix === []) {
            return array_fill(0, count($targets), 0.0);
        }

        $queryVector = $this->tfidfService->extractDocumentVector($matrix, 0);

        $scores = [];
        foreach (array_values($targets) as $index => $target) {
            $targetVector = $this->tfidfService->extractDocumentVector($matrix, $index + 1);
            $scores[$index] = $this->cosineSimilarityService->calculate($queryVector, $targetVector);
        }

        return $scores;
    }
}
