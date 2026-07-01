<?php

namespace App\Services;

class CosineSimilarityService
{
    /**
     * Hitung cosine similarity antara dua vektor bobot (term => weight).
     * Return nilai 0.0 - 1.0.
     */
    public function calculate(array $vectorA, array $vectorB): float
    {
        $dotProduct = 0.0;
        $magnitudeA = 0.0;
        $magnitudeB = 0.0;

        $terms = array_unique(array_merge(array_keys($vectorA), array_keys($vectorB)));

        foreach ($terms as $term) {
            $valueA = $vectorA[$term] ?? 0.0;
            $valueB = $vectorB[$term] ?? 0.0;

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
}
