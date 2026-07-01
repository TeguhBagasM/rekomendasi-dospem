<?php

namespace App\Services;

class TfidfService
{
    public function __construct(
        private readonly TextPreprocessingService $preprocessingService
    ) {}

    /**
     * Bangun matriks TF-IDF dari kumpulan dokumen.
     *
     * Return: [term => [docIndex => weight, ...], ...]
     */
    public function buildMatrix(array $documents): array
    {
        $tokenizedDocuments = array_map(
            fn ($document): array => $this->preprocessingService->tokenize((string) $document),
            $documents
        );

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

            foreach ($this->termFrequency($tokens) as $term => $frequency) {
                $idf = log(($documentCount + 1) / (($documentFrequency[$term] ?? 0) + 1)) + 1;
                $matrix[$term][$documentIndex] = $frequency * $idf;
            }
        }

        return $matrix;
    }

    /**
     * Ambil vektor bobot TF-IDF untuk satu dokumen dari matriks.
     */
    public function extractDocumentVector(array $matrix, int $documentIndex): array
    {
        $vector = [];

        foreach ($matrix as $term => $weights) {
            $vector[$term] = $weights[$documentIndex] ?? 0.0;
        }

        return $vector;
    }

    private function termFrequency(array $tokens): array
    {
        $counts = array_count_values($tokens);
        $total = max(count($tokens), 1);

        $tf = [];
        foreach ($counts as $term => $count) {
            $tf[$term] = $count / $total;
        }

        return $tf;
    }
}
