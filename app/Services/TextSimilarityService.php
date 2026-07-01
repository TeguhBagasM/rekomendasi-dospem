<?php

namespace App\Services;

use App\Models\PengajuanJudul;

/**
 * Khusus dipakai untuk mengecek kemiripan judul skripsi antar mahasiswa
 * (bukan bagian dari perhitungan rekomendasi dospem).
 *
 * @see RecommendationService untuk kesesuaian topik terhadap dosen.
 */
class TextSimilarityService
{
    private const DUPLICATE_THRESHOLD = 0.75;

    public function __construct(
        private readonly DocumentSimilarityService $documentSimilarityService,
        private readonly TextPreprocessingService $preprocessingService,
    ) {}

    public function checkSimilarity(string $judulBaru, ?int $excludeId = null): array
    {
        $judulBaru = trim($judulBaru);

        if ($judulBaru === '') {
            return $this->emptyResult();
        }

        $existingTitles = PengajuanJudul::query()
            ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
            ->pluck('judul_skripsi')
            ->filter()
            ->values();

        if ($existingTitles->isEmpty()) {
            return $this->emptyResult();
        }

        $normalizedBaru = $this->preprocessingService->normalize($judulBaru);

        foreach ($existingTitles as $existingTitle) {
            if ($normalizedBaru === $this->preprocessingService->normalize($existingTitle)) {
                return [
                    'is_similar' => true,
                    'percent' => 100.0,
                    'matched_title' => $existingTitle,
                ];
            }
        }

        $titlesArray = $existingTitles->toArray();
        $similarities = $this->documentSimilarityService->compareOneToMany($judulBaru, $titlesArray);

        $highestSimilarity = 0.0;
        $matchedTitle = null;

        foreach ($similarities as $index => $similarity) {
            if ($similarity > $highestSimilarity) {
                $highestSimilarity = $similarity;
                $matchedTitle = $titlesArray[$index];
            }
        }

        return [
            'is_similar' => $highestSimilarity >= self::DUPLICATE_THRESHOLD,
            'percent' => round($highestSimilarity * 100, 2),
            'matched_title' => $matchedTitle,
        ];
    }

    private function emptyResult(): array
    {
        return [
            'is_similar' => false,
            'percent' => 0,
            'matched_title' => null,
        ];
    }
}
