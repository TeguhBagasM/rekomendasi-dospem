<?php

namespace App\Services;

use App\Imports\PenetapanDospembingImport;
use App\Models\Dosen;
use App\Models\HasilPerhitungan;
use App\Models\PengajuanJudul;
use App\Models\RekomendasiDospembing;
use App\Models\RiwayatRekomendasi;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Satu-satunya service yang boleh dipanggil oleh Controller.
 *
 * Flow:
 * Ambil pengajuan layak -> TF-IDF -> Cosine Similarity ->
 * Konversi skala -> Profile Matching -> Ranking ->
 * Tentukan Dospem1 -> Tentukan Dospem2 -> Simpan -> (opsional) Export Excel
 */
class RecommendationService
{
    public function __construct(
        private readonly DocumentSimilarityService $documentSimilarityService,
        private readonly ProfileMatchingService $profileMatchingService,
        private readonly ExcelExportService $excelExportService,
    ) {}

    /**
     * Generate rekomendasi untuk sekumpulan pengajuan sekaligus (satu batch/riwayat).
     */
    public function generateBatch(Collection $pengajuans): Collection
    {
        $pengajuans->loadMissing('mahasiswa.programStudi');

        $dosens = $this->loadDosenCandidates();

        $generatedBy = Auth::id();

        return DB::transaction(function () use ($pengajuans, $dosens, $generatedBy): Collection {
            $riwayat = RiwayatRekomendasi::create([
                'generated_by' => $generatedBy,
                'generated_at' => now(),
                'status' => 'draft',
                'keterangan' => 'Batch rekomendasi dospem generated oleh admin/kaprodi.',
            ]);

            return $pengajuans->map(function (PengajuanJudul $pengajuan) use ($riwayat, $dosens): array {
                $result = $this->buildRecommendationResult($pengajuan, $dosens);

                $rekomendasi = RekomendasiDospembing::create([
                    'riwayat_rekomendasi_id' => $riwayat->id,
                    'pengajuan_id' => $pengajuan->id,
                    'rekomendasi_dospem1_id' => $result['dospem1']?->id,
                    'rekomendasi_dospem2_id' => $result['dospem2']?->id,
                    'skor_dospem1' => $result['dospem1_score'],
                    'skor_dospem2' => $result['dospem2_score'],
                    'ringkasan' => [
                        'judul' => $pengajuan->judul_skripsi,
                        'mahasiswa' => $pengajuan->mahasiswa?->nama,
                    ],
                ]);

                $this->persistHasilPerhitungan($rekomendasi, $result['ranked_dospem1'], $result['ranked_dospem2']);

                return [
                    'riwayat_rekomendasi_id' => $riwayat->id,
                    'rekomendasi_id' => $rekomendasi->id,
                    'pengajuan_id' => $pengajuan->id,
                    'dospem1' => $result['dospem1'],
                    'dospem2' => $result['dospem2'],
                    'dospem1_score' => $result['dospem1_score'],
                    'dospem2_score' => $result['dospem2_score'],
                ];
            });
        });
    }

    /**
     * Generate rekomendasi untuk satu pengajuan saja (di luar batch riwayat baru).
     */
    public function generateForSingle(PengajuanJudul $pengajuan): array
    {
        return $this->generateBatch(collect([$pengajuan]))->first() ?? [
            'pengajuan_id' => $pengajuan->id,
            'dospem1' => null,
            'dospem2' => null,
        ];
    }

    public function exportLatestToExcel(): BinaryFileResponse
    {
        return $this->excelExportService->exportLatest();
    }

    public function finalizeFromImport(UploadedFile $file): void
    {
        Excel::import(new PenetapanDospembingImport(), $file);
    }

    /**
     * Eager load seluruh kandidat dosen sekali saja untuk dipakai
     * pada seluruh pengajuan dalam batch (menghindari N+1).
     */
    private function loadDosenCandidates(): Collection
    {
        return Dosen::with(['programStudi', 'profile'])->get();
    }

    /**
     * Hitung similarity, skor Profile Matching, ranking, lalu tentukan
     * Dospem1 dan Dospem2 sesuai seluruh business rule.
     */
    private function buildRecommendationResult(PengajuanJudul $pengajuan, Collection $dosens): array
    {
        $pengajuan->loadMissing('mahasiswa.programStudi');

        // ==== Hitung TF-IDF + Cosine Similarity ====
        // Kesesuaian Topik WAJIB dari dosen_profiles.dokumen_tfidf vs judul_skripsi.
        $dokumenTfidfList = $dosens->map(
            fn (Dosen $dosen): string => (string) ($dosen->profile?->dokumen_tfidf ?? '')
        )->all();

        $similarities = $this->documentSimilarityService->compareOneToMany(
            (string) $pengajuan->judul_skripsi,
            $dokumenTfidfList
        );

        // ==== Hitung Profile Matching ====
        $scores = $dosens->values()->map(
            fn (Dosen $dosen, int $index) => $this->profileMatchingService->buildScoreRow(
                $pengajuan,
                $dosen,
                $similarities[$index] ?? 0.0
            )
        );

        // ==== Ranking ====
        $rankedForDospem1 = $scores->sortByDesc('final_score_dospem1')->values();
        $rankedForDospem2 = $scores->sortByDesc('final_score_dospem2')->values();

        // ==== Tentukan Dospem 1 ====
        // Wajib eligible (prodi Teknik Informatika) & sisa kuota dospem1 > 0.
        $dospem1Row = $rankedForDospem1->first(
            fn (array $row): bool => $row['eligible_dospem1'] && $row['remaining_kuota_dospem1'] > 0
        );

        // ==== Tentukan Dospem 2 ====
        // Tidak boleh dosen yang sama, sisa kuota dospem2 > 0,
        // dan jabatan Dospem1 harus >= jabatan Dospem2 (jika tidak, lanjut ke kandidat berikutnya).
        $dospem2Row = $rankedForDospem2->first(function (array $row) use ($dospem1Row): bool {
            if (! $dospem1Row) {
                return false;
            }

            if ($row['dosen']->id === $dospem1Row['dosen']->id) {
                return false;
            }

            if ($row['remaining_kuota_dospem2'] <= 0) {
                return false;
            }

            return $this->profileMatchingService->jabatanRank($dospem1Row['dosen']->jabatan_fungsional)
                >= $this->profileMatchingService->jabatanRank($row['dosen']->jabatan_fungsional);
        });

        return [
            'dospem1' => $dospem1Row['dosen'] ?? null,
            'dospem2' => $dospem2Row['dosen'] ?? null,
            'dospem1_score' => $dospem1Row['final_score_dospem1'] ?? null,
            'dospem2_score' => $dospem2Row['final_score_dospem2'] ?? null,
            'ranked_dospem1' => $rankedForDospem1->map(fn (array $row) => $this->stripDosenObject($row))->all(),
            'ranked_dospem2' => $rankedForDospem2->map(fn (array $row) => $this->stripDosenObject($row))->all(),
        ];
    }

    /**
     * Simpan seluruh hasil perhitungan (ranking dospem1 & dospem2 disimpan terpisah).
     */
    private function persistHasilPerhitungan(RekomendasiDospembing $rekomendasi, array $rankedDospem1, array $rankedDospem2): void
    {
        foreach ($rankedDospem1 as $ranking => $row) {
            HasilPerhitungan::create([
                'rekomendasi_dospembing_id' => $rekomendasi->id,
                'dosen_id' => $row['dosen_id'],
                'jenis_dospem' => 'dospem1',
                'topic_similarity' => $row['topic_similarity'],
                'topic_scale' => $row['topic_scale'],
                'homebase_scale' => $row['homebase_scale'],
                'quota_scale' => $row['quota_scale_dospem1'],
                'jabatan_scale' => $row['jabatan_scale'],
                'pendidikan_scale' => 0, // kriteria pendidikan tidak lagi dipakai
                'cf_score' => $row['cf'],
                'sf_score' => $row['sf_dospem1'],
                'final_score' => $row['final_score_dospem1'],
                'ranking' => $ranking + 1,
                'detail' => $row,
            ]);
        }

        foreach ($rankedDospem2 as $ranking => $row) {
            HasilPerhitungan::create([
                'rekomendasi_dospembing_id' => $rekomendasi->id,
                'dosen_id' => $row['dosen_id'],
                'jenis_dospem' => 'dospem2',
                'topic_similarity' => $row['topic_similarity'],
                'topic_scale' => $row['topic_scale'],
                'homebase_scale' => $row['homebase_scale'],
                'quota_scale' => $row['quota_scale_dospem2'],
                'jabatan_scale' => $row['jabatan_scale'],
                'pendidikan_scale' => 0, // kriteria pendidikan tidak lagi dipakai
                'cf_score' => $row['cf'],
                'sf_score' => $row['sf_dospem2'],
                'final_score' => $row['final_score_dospem2'],
                'ranking' => $ranking + 1,
                'detail' => $row,
            ]);
        }
    }

    private function stripDosenObject(array $row): array
    {
        unset($row['dosen']);

        return $row;
    }
}
