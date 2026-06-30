<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\HasilPerhitungan;
use App\Models\PengajuanJudul;
use App\Models\RekomendasiDospembing;
use App\Models\RiwayatRekomendasi;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileMatchingService
{
    // ==================== KONSTANTA PROFILE MATCHING ====================

    /**
     * Bobot kelompok Core Factor (60%) dan Secondary Factor (40%).
     * Standar teori Profile Matching — Kusrini (2007).
     */
    private const CORE_FACTOR_WEIGHT = 0.6;

    private const SECONDARY_FACTOR_WEIGHT = 0.4;

    /**
     * Profil ideal untuk semua kriteria.
     * Dalam Profile Matching, profil ideal = nilai tertinggi skala (5).
     * GAP dihitung: profil_kandidat - profil_ideal.
     */
    private const IDEAL_PROFILE = 5;

    /**
     * Tabel konversi GAP → Bobot GAP.
     * Sumber: Kusrini, "Konsep dan Aplikasi Sistem Pendukung Keputusan" (2007).
     *
     *  GAP |  Bobot | Keterangan
     *  ----+--------+------------------------------------------
     *   0  |  5.0   | Tidak ada selisih (kompetensi sesuai)
     *   1  |  4.5   | Kompetensi individu kelebihan 1 tingkat
     *  -1  |  4.0   | Kompetensi individu kekurangan 1 tingkat
     *   2  |  3.5   | Kompetensi individu kelebihan 2 tingkat
     *  -2  |  3.0   | Kompetensi individu kekurangan 2 tingkat
     *   3  |  2.5   | Kompetensi individu kelebihan 3 tingkat
     *  -3  |  2.0   | Kompetensi individu kekurangan 3 tingkat
     *   4  |  1.5   | Kompetensi individu kelebihan 4 tingkat
     *  -4  |  1.0   | Kompetensi individu kekurangan 4 tingkat
     */
    private const GAP_WEIGHT_MAP = [
        0  => 5.0,
        1  => 4.5,
        -1 => 4.0,
        2  => 3.5,
        -2 => 3.0,
        3  => 2.5,
        -3 => 2.0,
        4  => 1.5,
        -4 => 1.0,
    ];

    private const GAP_WEIGHT_DEFAULT = 0.5;

    public function __construct(
        private readonly TextSimilarityService $similarityService
    ) {}

    // ==================== PUBLIC API ====================

    /**
     * Generate rekomendasi untuk satu pengajuan.
     */
    public function recommend(PengajuanJudul $pengajuan): array
    {
        return $this->generateRecommendationBatch(collect([$pengajuan]))->first() ?? [
            'riwayat_rekomendasi_id' => null,
            'rekomendasi_id' => null,
            'pengajuan_id' => $pengajuan->id,
            'dospem1' => null,
            'dospem2' => null,
            'scores' => [],
        ];
    }

    /**
     * Alias bahasa Indonesia.
     */
    public function rekomendasikan(PengajuanJudul $pengajuan): array
    {
        return $this->recommend($pengajuan);
    }

    /**
     * Alias bulk bahasa Indonesia.
     */
    public function rekomendasikanBulk(Collection $pengajuans): Collection
    {
        return $pengajuans->map(fn (PengajuanJudul $pengajuan) => $this->recommend($pengajuan));
    }

    /**
     * Generate rekomendasi batch: hitung semua pengajuan dalam satu riwayat.
     */
    public function generateRecommendationBatch(Collection $pengajuans): Collection
    {
        $pengajuans->loadMissing('mahasiswa.programStudi');

        $generatedBy = Auth::id();

        return DB::transaction(function () use ($pengajuans, $generatedBy): Collection {
            $riwayat = RiwayatRekomendasi::create([
                'generated_by' => $generatedBy,
                'generated_at' => now(),
                'status' => 'draft',
                'keterangan' => 'Batch rekomendasi dospem generated oleh admin/kaprodi.',
            ]);

            return $pengajuans->map(function (PengajuanJudul $pengajuan) use ($riwayat): array {
                $result = $this->buildRecommendationResult($pengajuan);

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

                $this->persistHasilPerhitungan($rekomendasi, $result['scores']);

                return [
                    'riwayat_rekomendasi_id' => $riwayat->id,
                    'rekomendasi_id' => $rekomendasi->id,
                    'pengajuan_id' => $pengajuan->id,
                    'dospem1' => $result['dospem1'],
                    'dospem2' => $result['dospem2'],
                    'dospem1_score' => $result['dospem1_score'],
                    'dospem2_score' => $result['dospem2_score'],
                    'scores' => $result['scores'],
                ];
            });
        });
    }

    // ==================== RECOMMENDATION BUILDER ====================

    /**
     * Bangun hasil rekomendasi: hitung skor semua dosen, pilih dospem1 & dospem2.
     */
    private function buildRecommendationResult(PengajuanJudul $pengajuan): array
    {
        $pengajuan->loadMissing('mahasiswa.programStudi');

        $dosens = Dosen::with([
            'keahlianDosens',
            'programStudi',
        ])
        ->withCount([
            'dospem1Pengajuan as approved_dospem1_count' => fn ($query) => $query->where('status', 'disetujui'),
            'dospem2Pengajuan as approved_dospem2_count' => fn ($query) => $query->where('status', 'disetujui'),
        ])->get();

        $dosenKeahlianTexts = $dosens->map(function (Dosen $dosen) {
            return $dosen->keahlianDosens
                ->pluck('deskripsi_keahlian')
                ->filter()
                ->implode(' ');
        })->all();

        $similarityScores = $this->similarityService->getSimilarityScoresBatch(
            $pengajuan->judul_skripsi,
            $dosenKeahlianTexts
        );

        $scores = $dosens->map(function (Dosen $dosen, int $index) use ($pengajuan, $similarityScores): array {
            $topicSimilarity = $similarityScores[$index] ?? 0.0;
            return $this->buildScoreRow($pengajuan, $dosen, $topicSimilarity);
        })->values();

        $rankedForDospem1 = $scores->sortByDesc('nilai_akhir_dospem1')->values();
        $rankedForDospem2 = $scores->sortByDesc('nilai_akhir_dospem2')->values();

        $dospem1Row = $rankedForDospem1->first(function (array $row): bool {
            return $row['eligible_dospem1'] && $row['remaining_kuota_dospem1'] > 0;
        });

        $dospem2Row = $rankedForDospem2->first(function (array $row) use ($dospem1Row): bool {
            if (! $dospem1Row) {
                return false;
            }

            return $row['dosen']->id !== $dospem1Row['dosen']->id
                && $row['eligible_dospem2']
                && $row['remaining_kuota_dospem2'] > 0;
        });

        return [
            'dospem1' => $dospem1Row['dosen'] ?? null,
            'dospem2' => $dospem2Row['dosen'] ?? null,
            'dospem1_score' => $dospem1Row['nilai_akhir_dospem1'] ?? null,
            'dospem2_score' => $dospem2Row['nilai_akhir_dospem2'] ?? null,
            'scores' => $scores->map(fn (array $row) => $this->formatScoreRow($row))->all(),
        ];
    }

    // ==================== SCORE CALCULATION (PROFILE MATCHING) ====================

    /**
     * Hitung semua skor untuk satu pasangan pengajuan-dosen.
     *
     * Alur Profile Matching:
     *  1. Konversi atribut → skala 1-5
     *  2. GAP = profil_kandidat - profil_ideal (5)
     *  3. GAP → Bobot GAP (tabel Kusrini)
     *  4. NCF = rata-rata bobot GAP kriteria Core (topik, homebase, kuota)
     *  5. NSF = rata-rata bobot GAP kriteria Secondary (jabatan, pendidikan)
     *  6. Nilai Akhir = (NCF × 0.6) + (NSF × 0.4)
     */
    private function buildScoreRow(PengajuanJudul $pengajuan, Dosen $dosen, float $topicSimilarity): array
    {
        // === STEP 1: Konversi atribut → skala 1-5 ===
        $topicScale = $this->similarityToScale($topicSimilarity);
        $homebaseScale = $this->homebaseToScale($pengajuan, $dosen);
        $jabatanScale = $this->jabatanToScale($dosen->jabatan_fungsional);
        $pendidikanScale = $this->pendidikanToScale($dosen->pendidikan_terakhir);

        $remainingKuotaDospem1 = $dosen->getRemainingKuotaDospem1();
        $remainingKuotaDospem2 = $dosen->getRemainingKuotaDospem2();
        $quotaScaleDospem1 = $this->quotaToScale($remainingKuotaDospem1);
        $quotaScaleDospem2 = $this->quotaToScale($remainingKuotaDospem2);

        // === STEP 2 & 3: GAP → Bobot GAP per kriteria ===
        $topicGap = $this->calculateGap($topicScale);
        $homebaseGap = $this->calculateGap($homebaseScale);
        $jabatanGap = $this->calculateGap($jabatanScale);
        $pendidikanGap = $this->calculateGap($pendidikanScale);
        $quotaGapDospem1 = $this->calculateGap($quotaScaleDospem1);
        $quotaGapDospem2 = $this->calculateGap($quotaScaleDospem2);

        $topicWeight = $this->gapToWeight($topicGap);
        $homebaseWeight = $this->gapToWeight($homebaseGap);
        $jabatanWeight = $this->gapToWeight($jabatanGap);
        $pendidikanWeight = $this->gapToWeight($pendidikanGap);
        $quotaWeightDospem1 = $this->gapToWeight($quotaGapDospem1);
        $quotaWeightDospem2 = $this->gapToWeight($quotaGapDospem2);

        // === STEP 4: NCF = rata-rata bobot GAP kriteria Core ===
        // Core Factor: Topik, Homebase, Kuota (3 kriteria)
        $ncfDospem1 = $this->calculateNcf($topicWeight, $homebaseWeight, $quotaWeightDospem1);
        $ncfDospem2 = $this->calculateNcf($topicWeight, $homebaseWeight, $quotaWeightDospem2);

        // === STEP 5: NSF = rata-rata bobot GAP kriteria Secondary ===
        // Secondary Factor: Jabatan, Pendidikan (2 kriteria)
        $nsf = $this->calculateNsf($jabatanWeight, $pendidikanWeight);

        // === STEP 6: Nilai Akhir ===
        $nilaiAkhirDospem1 = $this->calculateFinalScore($ncfDospem1, $nsf);
        $nilaiAkhirDospem2 = $this->calculateFinalScore($ncfDospem2, $nsf);

        // Eligibility check
        $eligibleDospem1 = $this->isEligibleForDospem1($pengajuan, $dosen);
        $eligibleDospem2 = $this->isEligibleForDospem2($pengajuan, $dosen);

        return [
            'dosen' => $dosen,
            'dosen_id' => $dosen->id,
            'dosen_nama' => $dosen->nama,

            // Raw similarity
            'topic_similarity' => $topicSimilarity,

            // Skala 1-5 per kriteria
            'topic_scale' => $topicScale,
            'homebase_scale' => $homebaseScale,
            'quota_scale_dospem1' => $quotaScaleDospem1,
            'quota_scale_dospem2' => $quotaScaleDospem2,
            'jabatan_scale' => $jabatanScale,
            'pendidikan_scale' => $pendidikanScale,

            // GAP per kriteria (profil_kandidat - profil_ideal)
            'gap_topik' => $topicGap,
            'gap_homebase' => $homebaseGap,
            'gap_kuota_dospem1' => $quotaGapDospem1,
            'gap_kuota_dospem2' => $quotaGapDospem2,
            'gap_jabatan' => $jabatanGap,
            'gap_pendidikan' => $pendidikanGap,

            // Bobot GAP per kriteria
            'bobot_gap_topik' => $topicWeight,
            'bobot_gap_homebase' => $homebaseWeight,
            'bobot_gap_kuota_dospem1' => $quotaWeightDospem1,
            'bobot_gap_kuota_dospem2' => $quotaWeightDospem2,
            'bobot_gap_jabatan' => $jabatanWeight,
            'bobot_gap_pendidikan' => $pendidikanWeight,

            // NCF & NSF
            'ncf_dospem1' => $ncfDospem1,
            'ncf_dospem2' => $ncfDospem2,
            'nsf' => $nsf,

            // Nilai Akhir
            'nilai_akhir_dospem1' => $nilaiAkhirDospem1,
            'nilai_akhir_dospem2' => $nilaiAkhirDospem2,

            // Kuota & eligibility
            'remaining_kuota_dospem1' => $remainingKuotaDospem1,
            'remaining_kuota_dospem2' => $remainingKuotaDospem2,
            'eligible_dospem1' => $eligibleDospem1,
            'eligible_dospem2' => $eligibleDospem2,
        ];
    }



    // ==================== PROFILE MATCHING FORMULAS ====================

    /**
     * Hitung GAP: profil_kandidat - profil_ideal.
     *
     * Contoh: topicScale=3, ideal=5 → GAP = 3-5 = -2
     * Artinya: kandidat kekurangan 2 tingkat dari profil ideal.
     */
    private function calculateGap(int $profileValue): int
    {
        return $profileValue - self::IDEAL_PROFILE;
    }

    /**
     * Konversi GAP → Bobot GAP menggunakan tabel standar Kusrini.
     */
    private function gapToWeight(int $gap): float
    {
        return self::GAP_WEIGHT_MAP[$gap] ?? self::GAP_WEIGHT_DEFAULT;
    }

    /**
     * NCF (Nilai Core Factor) = rata-rata bobot GAP kriteria core.
     *
     * Core Factor: Topik, Homebase, Kuota (3 kriteria).
     * NCF = (bobot_topik + bobot_homebase + bobot_kuota) / 3
     */
    private function calculateNcf(float $topicWeight, float $homebaseWeight, float $quotaWeight): float
    {
        return ($topicWeight + $homebaseWeight + $quotaWeight) / 3;
    }

    /**
     * NSF (Nilai Secondary Factor) = rata-rata bobot GAP kriteria secondary.
     *
     * Secondary Factor: Jabatan, Pendidikan (2 kriteria).
     * NSF = (bobot_jabatan + bobot_pendidikan) / 2
     */
    private function calculateNsf(float $jabatanWeight, float $pendidikanWeight): float
    {
        return ($jabatanWeight + $pendidikanWeight) / 2;
    }

    /**
     * Nilai Akhir = (NCF × %CF) + (NSF × %SF).
     *
     * %CF = 60%, %SF = 40% (standar Profile Matching).
     */
    private function calculateFinalScore(float $ncf, float $nsf): float
    {
        return ($ncf * self::CORE_FACTOR_WEIGHT) + ($nsf * self::SECONDARY_FACTOR_WEIGHT);
    }

    // ==================== KONVERSI ATRIBUT → SKALA 1-5 ====================

    /**
     * Konversi nilai cosine similarity (0.0 - 1.0) → skala 1-5.
     *
     * Threshold disesuaikan dengan distribusi cosine similarity pada TF-IDF:
     *  >= 0.75 → 5 (Sangat sesuai)
     *  >= 0.50 → 4 (Sesuai)
     *  >= 0.30 → 3 (Cukup sesuai)
     *  >= 0.15 → 2 (Kurang sesuai)
     *  <  0.15 → 1 (Tidak sesuai)
     */
    private function similarityToScale(float $similarity): int
    {
        return match (true) {
            $similarity >= 0.75 => 5,
            $similarity >= 0.50 => 4,
            $similarity >= 0.30 => 3,
            $similarity >= 0.15 => 2,
            default => 1,
        };
    }

    /**
     * Konversi homebase dosen → skala 1-5.
     *
     * 5 = Satu program studi (match)
     * 1 = Lintas program studi (tidak match)
     */
    private function homebaseToScale(PengajuanJudul $pengajuan, Dosen $dosen): int
    {
        $mahasiswaProdi = strtolower(trim((string) $pengajuan->mahasiswa?->programStudi?->program_studi));
        $dosenProdi = strtolower(trim((string) ($dosen->programStudi?->program_studi ?? $dosen->program_studi)));

        if ($mahasiswaProdi !== '' && $dosenProdi !== '' && $mahasiswaProdi === $dosenProdi) {
            return 5;
        }

        return 1;
    }

    /**
     * Konversi jabatan fungsional → skala 1-5.
     */
    private function jabatanToScale(?string $jabatan): int
    {
        return match (trim((string) $jabatan)) {
            'Guru Besar' => 5,
            'Lektor Kepala' => 4,
            'Lektor' => 3,
            'Asisten Ahli' => 2,
            default => 1,
        };
    }

    /**
     * Konversi pendidikan terakhir → skala 1-5.
     */
    private function pendidikanToScale(?string $pendidikan): int
    {
        return match (trim((string) $pendidikan)) {
            'S3' => 5,
            'S2' => 3,
            default => 1,
        };
    }

    /**
     * Konversi sisa kuota bimbingan → skala 1-5.
     */
    private function quotaToScale(int $remainingQuota): int
    {
        return match (true) {
            $remainingQuota >= 5 => 5,
            $remainingQuota >= 3 => 4,
            $remainingQuota >= 1 => 3,
            default => 1,
        };
    }

    // ==================== ELIGIBILITY ====================

    /**
     * Apakah dosen eligible menjadi Dospem 1.
     * Dospem 1 harus dari program studi Teknik Informatika.
     */
    private function isEligibleForDospem1(PengajuanJudul $pengajuan, Dosen $dosen): bool
    {
        $prodi = strtolower(trim((string) ($dosen->programStudi?->program_studi ?? $dosen->program_studi)));

        return $prodi === 'teknik informatika';
    }

    /**
     * Apakah dosen eligible menjadi Dospem 2.
     * Semua dosen eligible menjadi Dospem 2.
     */
    private function isEligibleForDospem2(PengajuanJudul $pengajuan, Dosen $dosen): bool
    {
        return $dosen->id !== null;
    }

    // ==================== PERSISTENCE ====================

    /**
     * Simpan semua hasil perhitungan ke database.
     * Setiap dosen disimpan 2 record: satu untuk dospem1, satu untuk dospem2.
     */
    private function persistHasilPerhitungan(RekomendasiDospembing $rekomendasi, array $scores): void
    {
        foreach ($scores as $ranking => $scoreRow) {
            HasilPerhitungan::create([
                'rekomendasi_dospembing_id' => $rekomendasi->id,
                'dosen_id' => $scoreRow['dosen_id'],
                'jenis_dospem' => 'dospem1',
                'topic_similarity' => $scoreRow['topic_similarity'],
                'topic_scale' => $scoreRow['topic_scale'],
                'homebase_scale' => $scoreRow['homebase_scale'],
                'quota_scale' => $scoreRow['quota_scale_dospem1'],
                'jabatan_scale' => $scoreRow['jabatan_scale'],
                'pendidikan_scale' => $scoreRow['pendidikan_scale'],
                'cf_score' => $scoreRow['ncf_dospem1'],
                'sf_score' => $scoreRow['nsf'],
                'final_score' => $scoreRow['nilai_akhir_dospem1'],
                'ranking' => $ranking + 1,
                'detail' => $scoreRow,
            ]);

            HasilPerhitungan::create([
                'rekomendasi_dospembing_id' => $rekomendasi->id,
                'dosen_id' => $scoreRow['dosen_id'],
                'jenis_dospem' => 'dospem2',
                'topic_similarity' => $scoreRow['topic_similarity'],
                'topic_scale' => $scoreRow['topic_scale'],
                'homebase_scale' => $scoreRow['homebase_scale'],
                'quota_scale' => $scoreRow['quota_scale_dospem2'],
                'jabatan_scale' => $scoreRow['jabatan_scale'],
                'pendidikan_scale' => $scoreRow['pendidikan_scale'],
                'cf_score' => $scoreRow['ncf_dospem2'],
                'sf_score' => $scoreRow['nsf'],
                'final_score' => $scoreRow['nilai_akhir_dospem2'],
                'ranking' => $ranking + 1,
                'detail' => $scoreRow,
            ]);
        }
    }

    /**
     * Format score row untuk output — hapus object Dosen dari array.
     */
    private function formatScoreRow(array $row): array
    {
        unset($row['dosen']);

        return $row;
    }
}