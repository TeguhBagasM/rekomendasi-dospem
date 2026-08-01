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

    /**
     * Status pengajuan judul yang dianggap "resmi" sehingga judulnya
     * wajib dibandingkan untuk mencegah plagiarisme. Pengajuan dengan
     * status di luar daftar ini (pending/ditolak) diabaikan karena belum
     * disahkan, sehingga tidak memicu false positive saat mahasiswa
     * mengajukan ulang judul yang ditolak atau merevisi judulnya sendiri.
     *
     * Catatan: pada aplikasi ini status 'finalized'/'ditetapkan' tidak ada
     * pada tabel pengajuan_juduls — judul yang sudah difinalisasi tetap
     * berstatus 'disetujui' (ditandai oleh record PenetapanDospembing).
     */
    private const OFFICIAL_STATUSES = ['disetujui'];

    public function __construct(
        private readonly DocumentSimilarityService $documentSimilarityService,
        private readonly TextPreprocessingService $preprocessingService,
    ) {}

    public function checkSimilarity(
        string $judulBaru,
        ?int $excludePengajuanId = null,
        ?int $excludeMahasiswaId = null,
    ): array {
        $judulBaru = trim($judulBaru);

        if ($judulBaru === '') {
            return $this->emptyResult();
        }

        $existingTitles = PengajuanJudul::query()
            ->whereIn('status', self::OFFICIAL_STATUSES)
            ->when($excludePengajuanId, fn ($query) => $query->where('id', '!=', $excludePengajuanId))
            ->when($excludeMahasiswaId, fn ($query) => $query->where('mahasiswa_id', '!=', $excludeMahasiswaId))
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
