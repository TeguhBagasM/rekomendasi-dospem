<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\KuotaDosenGelombang;
use App\Models\PengajuanJudul;

/**
 * Kalkulator Profile Matching murni.
 *
 * Tidak melakukan query database maupun persistensi — hanya menerima
 * data yang sudah di-load (Dosen, PengajuanJudul, nilai similarity)
 * dan mengembalikan hasil perhitungan dalam bentuk array.
 *
 * ==================== BUSINESS RULE ====================
 * Kriteria:
 *   1. Topik Penelitian  → dari Cosine Similarity (konversi skala 1-5)
 *   2. Pendidikan Terakhir → S3=5, S2=3
 *   3. Jabatan Fungsional → Lektor=5, Asisten Ahli=3, Tenaga Pengajar=1
 *   4. Kuota Bimbingan   → persentase sisa kuota (0-100% → skala 1-5)
 *
 * Core Factor    : Topik Penelitian
 * Secondary Factor: Pendidikan, Jabatan, Kuota (rata-rata ketiganya)
 *
 * Nilai Total = (Core Factor × 0.6) + (Secondary Factor × 0.4)
 *
 * Kuota dihitung terpisah untuk peran Dospem1 dan Dospem2, sehingga
 * Secondary Factor & Nilai Akhir juga dihitung terpisah per peran.
 *
 * CATATAN: quota_scale di sini hanya dipakai untuk KRITERIA SCORING
 * (nilai profile matching), BUKAN untuk penentuan kuota assignment.
 * Penentuan kuota assignment yang sebenarnya (agar tidak overload)
 * dilakukan terpisah oleh RecommendationAssignmentService dengan
 * state kuota sementara yang berjalan sepanjang batch generate.
 * =========================================================
 */
class ProfileMatchingService
{
    private const CORE_FACTOR_WEIGHT = 0.6;

    private const SECONDARY_FACTOR_WEIGHT = 0.4;

    private const IDEAL_PROFILE = 5;

    /**
     * Tabel konversi GAP → Bobot GAP (standar Kusrini, 2007).
     *
     * GAP = nilai_aktual - nilai_ideal (ideal = 5).
     * Bobot menurun seiring GAP menjauhi 0.
     */
    private const GAP_WEIGHT_MAP = [
        0 => 5.0,
        1 => 4.5,
        -1 => 4.0,
        2 => 3.5,
        -2 => 3.0,
        3 => 2.5,
        -3 => 2.0,
        4 => 1.5,
        -4 => 1.0,
    ];

    private const GAP_WEIGHT_DEFAULT = 0.5;

    /**
     * Urutan jabatan fungsional untuk hard-rule pairing:
     * Dospem1 harus >= Dospem2. Di luar 3 nilai ini dianggap rank 0.
     */
    private const JABATAN_RANK = [
        'lektor' => 3,
        'asisten ahli' => 2,
        'tenaga pengajar' => 1,
    ];

    /**
     * Konversi jabatan → skala 1-5 untuk perhitungan GAP.
     */
    private const JABATAN_SCALE = [
        'lektor' => 5,
        'asisten ahli' => 3,
        'tenaga pengajar' => 1,
    ];

    /**
     * Konversi pendidikan terakhir → skala 1-5 untuk perhitungan GAP.
     */
    private const PENDIDIKAN_SCALE = [
        's3' => 5,
        's2' => 3,
    ];

    /**
     * Hitung seluruh skor Profile Matching untuk satu pasangan pengajuan-dosen.
     *
     * @param  KuotaDosenGelombang|null  $kuotaGelombang  Kuota per-gelombang (opsional, fallback ke lifetime)
     */
    public function buildScoreRow(PengajuanJudul $pengajuan, Dosen $dosen, float $topicSimilarity, ?KuotaDosenGelombang $kuotaGelombang = null): array
    {
        // === Tahap 1: Konversi atribut → skala 1-5 ===
        $topicScale = $this->similarityToScale($topicSimilarity);
        $pendidikanScale = $this->pendidikanToScale($dosen->pendidikan_terakhir);
        $jabatanScale = $this->jabatanToScale($dosen->jabatan_fungsional);

        if ($kuotaGelombang) {
            $remainingKuotaDospem1 = $kuotaGelombang->remainingDospem1();
            $remainingKuotaDospem2 = $kuotaGelombang->remainingDospem2();
            $quotaScaleDospem1 = $this->quotaToScale($remainingKuotaDospem1, $kuotaGelombang->kuota_dospem1);
            $quotaScaleDospem2 = $this->quotaToScale($remainingKuotaDospem2, $kuotaGelombang->kuota_dospem2);
        } else {
            $remainingKuotaDospem1 = $this->remainingKuota($dosen->kuota_dospem1, $dosen->kuota_terpakai_dospem1);
            $remainingKuotaDospem2 = $this->remainingKuota($dosen->kuota_dospem2, $dosen->kuota_terpakai_dospem2);
            $quotaScaleDospem1 = $this->quotaToScale($remainingKuotaDospem1, $dosen->kuota_dospem1);
            $quotaScaleDospem2 = $this->quotaToScale($remainingKuotaDospem2, $dosen->kuota_dospem2);
        }

        // === Tahap 2: Hitung GAP (nilai_aktual - nilai_ideal) ===
        $gapTopik = $this->calculateGap($topicScale);
        $gapPendidikan = $this->calculateGap($pendidikanScale);
        $gapJabatan = $this->calculateGap($jabatanScale);
        $gapKuotaDospem1 = $this->calculateGap($quotaScaleDospem1);
        $gapKuotaDospem2 = $this->calculateGap($quotaScaleDospem2);

        // === Tahap 3: Mapping GAP → Bobot GAP ===
        $bobotGapTopik = $this->gapToWeight($gapTopik);
        $bobotGapPendidikan = $this->gapToWeight($gapPendidikan);
        $bobotGapJabatan = $this->gapToWeight($gapJabatan);
        $bobotGapKuotaDospem1 = $this->gapToWeight($gapKuotaDospem1);
        $bobotGapKuotaDospem2 = $this->gapToWeight($gapKuotaDospem2);

        // === Tahap 4: Core Factor = rata-rata bobot GAP komponen CF ===
        $cf = $bobotGapTopik;

        // === Tahap 5: Secondary Factor = rata-rata bobot GAP Pendidikan, Jabatan, Kuota ===
        // Dihitung terpisah per peran karena kuota berbeda per peran.
        $sfDospem1 = ($bobotGapPendidikan + $bobotGapJabatan + $bobotGapKuotaDospem1) / 3;
        $sfDospem2 = ($bobotGapPendidikan + $bobotGapJabatan + $bobotGapKuotaDospem2) / 3;

        // === Tahap 6: Nilai Total = (CF × 60%) + (SF × 40%) ===
        $finalScoreDospem1 = ($cf * self::CORE_FACTOR_WEIGHT) + ($sfDospem1 * self::SECONDARY_FACTOR_WEIGHT);
        $finalScoreDospem2 = ($cf * self::CORE_FACTOR_WEIGHT) + ($sfDospem2 * self::SECONDARY_FACTOR_WEIGHT);

        return [
            'dosen' => $dosen,
            'dosen_id' => $dosen->id,
            'dosen_nama' => $dosen->nama,

            'topic_similarity' => $topicSimilarity,
            'topic_scale' => $topicScale,
            'pendidikan_scale' => $pendidikanScale,
            'jabatan_scale' => $jabatanScale,
            'quota_scale_dospem1' => $quotaScaleDospem1,
            'quota_scale_dospem2' => $quotaScaleDospem2,

            'gap_topik' => $gapTopik,
            'gap_pendidikan' => $gapPendidikan,
            'gap_jabatan' => $gapJabatan,
            'gap_kuota_dospem1' => $gapKuotaDospem1,
            'gap_kuota_dospem2' => $gapKuotaDospem2,

            'bobot_gap_topik' => $bobotGapTopik,
            'bobot_gap_pendidikan' => $bobotGapPendidikan,
            'bobot_gap_jabatan' => $bobotGapJabatan,
            'bobot_gap_kuota_dospem1' => $bobotGapKuotaDospem1,
            'bobot_gap_kuota_dospem2' => $bobotGapKuotaDospem2,

            'cf' => $cf,
            'sf_dospem1' => $sfDospem1,
            'sf_dospem2' => $sfDospem2,

            'final_score_dospem1' => $finalScoreDospem1,
            'final_score_dospem2' => $finalScoreDospem2,

            'remaining_kuota_dospem1' => $remainingKuotaDospem1,
            'remaining_kuota_dospem2' => $remainingKuotaDospem2,

            'eligible_dospem1' => $this->isEligibleForDospem1($dosen),

            // Dipakai oleh RecommendationAssignmentService untuk hard-rule pairing.
            'jabatan_rank' => $this->jabatanRank($dosen->jabatan_fungsional),
        ];
    }

    /**
     * Dospem1 wajib berasal dari Program Studi Teknik Informatika.
     */
    public function isEligibleForDospem1(Dosen $dosen): bool
    {
        $prodi = strtolower(trim((string) $dosen->programStudi?->program_studi));

        return $prodi === 'teknik informatika';
    }

    /**
     * Rank jabatan untuk hard-rule: jabatan Dospem1 >= jabatan Dospem2.
     * Urutan: Lektor > Asisten Ahli > Tenaga Pengajar.
     */
    public function jabatanRank(?string $jabatan): int
    {
        return self::JABATAN_RANK[strtolower(trim((string) $jabatan))] ?? 0;
    }

    private function remainingKuota(?int $kuota, ?int $terpakai): int
    {
        return max(((int) $kuota) - ((int) $terpakai), 0);
    }

    /**
     * GAP = nilai_aktual - nilai_ideal.
     * Ideal = 5, sehingga GAP selalu ≤ 0 untuk nilai aktual 1-5.
     */
    private function calculateGap(int $profileValue): int
    {
        return $profileValue - self::IDEAL_PROFILE;
    }

    private function gapToWeight(int $gap): float
    {
        return self::GAP_WEIGHT_MAP[$gap] ?? self::GAP_WEIGHT_DEFAULT;
    }

    /**
     * Konversi cosine similarity (0.0 - 1.0) → skala 1-5.
     * Threshold: 0.50 / 0.30 / 0.10 / 0.05.
     */
    private function similarityToScale(float $similarity): int
    {
        return match (true) {
            $similarity >= 0.50 => 5,
            $similarity >= 0.30 => 4,
            $similarity >= 0.10 => 3,
            $similarity >= 0.05 => 2,
            default => 1,
        };
    }

    /**
     * Konversi pendidikan terakhir → skala 1-5.
     * S3 = 5, S2 = 3, lainnya = 1.
     */
    private function pendidikanToScale(?string $pendidikan): int
    {
        return self::PENDIDIKAN_SCALE[strtolower(trim((string) $pendidikan))] ?? 1;
    }

    private function jabatanToScale(?string $jabatan): int
    {
        return self::JABATAN_SCALE[strtolower(trim((string) $jabatan))] ?? 1;
    }

    /**
     * Konversi sisa kuota → skala 1-5 berdasarkan persentase.
     *
     * Persentase = (sisa kuota / total kuota) × 100
     * 80-100% = 5, 60-79% = 4, 40-59% = 3, 20-39% = 2, 0-19% = 1
     */
    private function quotaToScale(int $remainingQuota, ?int $totalQuota): int
    {
        $total = (int) $totalQuota;

        if ($total <= 0) {
            return 1;
        }

        $percentage = ($remainingQuota / $total) * 100;

        return match (true) {
            $percentage >= 80 => 5,
            $percentage >= 60 => 4,
            $percentage >= 40 => 3,
            $percentage >= 20 => 2,
            default => 1,
        };
    }
}
