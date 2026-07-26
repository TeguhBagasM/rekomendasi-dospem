<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\HasilPerhitungan;
use App\Models\KuotaDosenGelombang;
use App\Models\PengajuanJudul;
use App\Models\RekomendasiDospembing;
use App\Models\RiwayatRekomendasi;
use Illuminate\Support\Collection;

/**
 * Orkestrator proses generate rekomendasi untuk satu batch (riwayat) sekaligus:
 *
 * Tahap 1-2: Text Preprocessing + TF-IDF + Cosine Similarity
 *            -> DocumentSimilarityService (via TfidfService, CosineSimilarityService)
 * Tahap 3  : Konversi similarity → skala 1-5   -> ProfileMatchingService::similarityToScale()
 * Tahap 4  : Profile Matching (GAP + Bobot GAP) -> ProfileMatchingService
 * Tahap 5  : Core Factor (60%)                 -> ProfileMatchingService
 * Tahap 6  : Secondary Factor (40%)            -> ProfileMatchingService
 * Tahap 7  : Nilai Akhir (CF × 0.6 + SF × 0.4) -> ProfileMatchingService
 * Tahap 8  : Ranking DESC                      -> buildRankedCandidates()
 * Tahap 9  : Seleksi Dospem1 (rules: TI, kuota)-> RecommendationAssignmentService
 * Tahap 10 : Seleksi Dospem2 (rules: non-TI优先, jabatan, kuota)
 *            -> RecommendationAssignmentService
 * Tahap 11 : Validasi kuota total (lifetime)    -> RecommendationAssignmentService
 * Tahap 12 : Validasi kuota per gelombang       -> RecommendationAssignmentService
 * Tahap 13 : Simpan hasil rekomendasi          -> RekomendasiDospembing::create()
 */
class RecommendationGeneratorService
{
    public function __construct(
        private readonly DocumentSimilarityService $documentSimilarityService,
        private readonly ProfileMatchingService $profileMatchingService,
        private readonly RecommendationAssignmentService $assignmentService,
    ) {}

    /**
     * @param  Collection<int, PengajuanJudul>  $pengajuans
     * @return Collection<int, array>
     */
    public function generateForBatch(Collection $pengajuans, RiwayatRekomendasi $riwayat): Collection
    {
        $pengajuans->loadMissing('mahasiswa.programStudi');

        $dosens = $this->loadDosenCandidates();

        $gelombangId = $pengajuans->first()->gelombang_id;

        // Load kuota per-gelombang untuk semua dosen sekaligus (menghindari N+1).
        $kuotaGelombangMap = $this->loadKuotaGelombangMap($dosens, $gelombangId);

        // Tahap 7 - state kuota sementara per gelombang, dipakai & dikurangi sepanjang batch ini.
        $quotaState = $this->assignmentService->initializeQuotaState($dosens, $gelombangId);

        $hasilPerhitunganRows = [];
        $results = collect();

        foreach ($pengajuans as $pengajuan) {
            /** @var PengajuanJudul $pengajuan */
            [$rankedDospem1, $rankedDospem2] = $this->buildRankedCandidates($pengajuan, $dosens, $kuotaGelombangMap);

            $assignment = $this->assignmentService->assign($rankedDospem1, $rankedDospem2, $quotaState);
            $dospem1Row = $assignment['dospem1_row'];
            $dospem2Row = $assignment['dospem2_row'];

            $rekomendasi = RekomendasiDospembing::create([
                'riwayat_rekomendasi_id' => $riwayat->id,
                'pengajuan_id' => $pengajuan->id,
                'rekomendasi_dospem1_id' => $dospem1Row['dosen_id'] ?? null,
                'rekomendasi_dospem2_id' => $dospem2Row['dosen_id'] ?? null,
                'skor_dospem1' => $dospem1Row['final_score_dospem1'] ?? null,
                'skor_dospem2' => $dospem2Row['final_score_dospem2'] ?? null,
                'ringkasan' => [
                    'judul' => $pengajuan->judul_skripsi,
                    'mahasiswa' => $pengajuan->mahasiswa?->nama,
                ],
            ]);

            // Simpan SELURUH kandidat (bukan hanya yang terpilih) untuk halaman detail perhitungan.
            $hasilPerhitunganRows = array_merge(
                $hasilPerhitunganRows,
                $this->buildHasilPerhitunganRows($rekomendasi->id, $rankedDospem1, 'dospem1', 'final_score_dospem1', 'sf_dospem1', 'quota_scale_dospem1'),
                $this->buildHasilPerhitunganRows($rekomendasi->id, $rankedDospem2, 'dospem2', 'final_score_dospem2', 'sf_dospem2', 'quota_scale_dospem2'),
            );

            $results->push([
                'riwayat_rekomendasi_id' => $riwayat->id,
                'rekomendasi_id' => $rekomendasi->id,
                'pengajuan_id' => $pengajuan->id,
                'dospem1' => $dospem1Row['dosen'] ?? null,
                'dospem2' => $dospem2Row['dosen'] ?? null,
                'dospem1_score' => $dospem1Row['final_score_dospem1'] ?? null,
                'dospem2_score' => $dospem2Row['final_score_dospem2'] ?? null,
            ]);
        }

        $this->bulkInsertHasilPerhitungan($hasilPerhitunganRows);

        return $results;
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
     * Load kuota per-gelombang untuk semua dosen, di-index oleh dosen_id.
     *
     * @return array<int, KuotaDosenGelombang>
     */
    private function loadKuotaGelombangMap(Collection $dosens, int $gelombangId): array
    {
        $dosenIds = $dosens->pluck('id')->all();

        return KuotaDosenGelombang::where('gelombang_id', $gelombangId)
            ->whereIn('dosen_id', $dosenIds)
            ->get()
            ->keyBy('dosen_id')
            ->all();
    }

    /**
     * Tahap 1-6: TF-IDF -> Cosine Similarity -> Profile Matching -> Ranking DESC.
     *
     * @param  array<int, KuotaDosenGelombang>  $kuotaGelombangMap
     * @return array{0: Collection, 1: Collection} [rankedDospem1, rankedDospem2]
     */
    private function buildRankedCandidates(PengajuanJudul $pengajuan, Collection $dosens, array $kuotaGelombangMap): array
    {
        $dokumenTfidfList = $dosens->map(
            fn (Dosen $dosen): string => (string) ($dosen->profile?->dokumen_tfidf ?? '')
        )->all();

        $similarities = $this->documentSimilarityService->compareOneToMany(
            (string) $pengajuan->judul_skripsi,
            $dokumenTfidfList
        );

        $scores = $dosens->values()->map(
            fn (Dosen $dosen, int $index) => $this->profileMatchingService->buildScoreRow(
                $pengajuan,
                $dosen,
                $similarities[$index] ?? 0.0,
                $kuotaGelombangMap[$dosen->id] ?? null,
            )
        );

        $rankedDospem1 = $scores->sortByDesc('final_score_dospem1')->values();
        $rankedDospem2 = $scores->sortByDesc('final_score_dospem2')->values();

        return [$rankedDospem1, $rankedDospem2];
    }

    /**
     * Siapkan baris insert hasil_perhitungans untuk SELURUH kandidat (bukan hanya yang terpilih).
     */
    private function buildHasilPerhitunganRows(
        int $rekomendasiId,
        Collection $ranked,
        string $jenisDospem,
        string $finalScoreKey,
        string $sfKey,
        string $quotaScaleKey,
    ): array {
        $rows = [];
        $now = now();

        foreach ($ranked->values() as $index => $row) {
            $rows[] = [
                'rekomendasi_dospembing_id' => $rekomendasiId,
                'dosen_id' => $row['dosen_id'],
                'jenis_dospem' => $jenisDospem,
                'topic_similarity' => $row['topic_similarity'],
                'topic_scale' => $row['topic_scale'],
                'quota_scale' => $row[$quotaScaleKey],
                'jabatan_scale' => $row['jabatan_scale'],
                'pendidikan_scale' => $row['pendidikan_scale'],
                'cf_score' => $row['cf'],
                'sf_score' => $row[$sfKey],
                'final_score' => $row[$finalScoreKey],
                'ranking' => $index + 1,
                'detail' => json_encode($this->stripDosenObject($row)),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $rows;
    }

    /**
     * Bulk insert untuk menghindari N+1 saat menyimpan banyak baris ranking sekaligus.
     */
    private function bulkInsertHasilPerhitungan(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        collect($rows)->chunk(500)->each(function (Collection $chunk): void {
            HasilPerhitungan::insert($chunk->all());
        });
    }

    private function stripDosenObject(array $row): array
    {
        unset($row['dosen']);

        return $row;
    }
}
