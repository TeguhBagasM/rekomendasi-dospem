<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\PengajuanJudul;

/**
 * Kalkulator Profile Matching murni.
 *
 * Tidak melakukan query database maupun persistensi — hanya menerima
 * data yang sudah di-load (Dosen, PengajuanJudul, nilai similarity)
 * dan mengembalikan hasil perhitungan dalam bentuk array.
 *
 * ==================== BUSINESS RULE ====================
 * Core Factor    : Kesesuaian Topik (bobot 60%)
 * Secondary Factor: Homebase, Kuota, Jabatan (bobot 40%, rata-rata ketiganya)
 *
 * Kuota dihitung terpisah untuk peran Dospem1 dan Dospem2, sehingga
 * Secondary Factor & Nilai Akhir juga dihitung terpisah per peran.
 * =========================================================
 */
class ProfileMatchingService
{
    private const CORE_FACTOR_WEIGHT = 0.6;

    private const SECONDARY_FACTOR_WEIGHT = 0.4;

    private const IDEAL_PROFILE = 5;

    /**
     * Tabel konversi GAP → Bobot GAP (standar Kusrini, 2007).
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
     * Hitung seluruh skor Profile Matching untuk satu pasangan pengajuan-dosen.
     */
    public function buildScoreRow(PengajuanJudul $pengajuan, Dosen $dosen, float $topicSimilarity): array
    {
        // === Konversi atribut → skala 1-5 ===
        $topicScale = $this->similarityToScale($topicSimilarity);
        $homebaseScale = $this->homebaseToScale($pengajuan, $dosen);
        $jabatanScale = $this->jabatanToScale($dosen->jabatan_fungsional);

        $remainingKuotaDospem1 = $this->remainingKuota($dosen->kuota_dospem1, $dosen->kuota_terpakai_dospem1);
        $remainingKuotaDospem2 = $this->remainingKuota($dosen->kuota_dospem2, $dosen->kuota_terpakai_dospem2);
        $quotaScaleDospem1 = $this->quotaToScale($remainingKuotaDospem1);
        $quotaScaleDospem2 = $this->quotaToScale($remainingKuotaDospem2);

        // === GAP → Bobot GAP per kriteria ===
        $topicWeight = $this->gapToWeight($this->calculateGap($topicScale));
        $homebaseWeight = $this->gapToWeight($this->calculateGap($homebaseScale));
        $jabatanWeight = $this->gapToWeight($this->calculateGap($jabatanScale));
        $quotaWeightDospem1 = $this->gapToWeight($this->calculateGap($quotaScaleDospem1));
        $quotaWeightDospem2 = $this->gapToWeight($this->calculateGap($quotaScaleDospem2));

        // === Core Factor = Kesesuaian Topik saja ===
        $cf = $topicWeight;

        // === Secondary Factor = rata-rata Homebase, Kuota, Jabatan ===
        // Dihitung terpisah per peran karena kuota berbeda per peran.
        $sfDospem1 = ($homebaseWeight + $quotaWeightDospem1 + $jabatanWeight) / 3;
        $sfDospem2 = ($homebaseWeight + $quotaWeightDospem2 + $jabatanWeight) / 3;

        // === Nilai Akhir = (CF x 60%) + (SF x 40%) ===
        $finalScoreDospem1 = ($cf * self::CORE_FACTOR_WEIGHT) + ($sfDospem1 * self::SECONDARY_FACTOR_WEIGHT);
        $finalScoreDospem2 = ($cf * self::CORE_FACTOR_WEIGHT) + ($sfDospem2 * self::SECONDARY_FACTOR_WEIGHT);

        return [
            'dosen' => $dosen,
            'dosen_id' => $dosen->id,
            'dosen_nama' => $dosen->nama,

            'topic_similarity' => $topicSimilarity,
            'topic_scale' => $topicScale,
            'homebase_scale' => $homebaseScale,
            'jabatan_scale' => $jabatanScale,
            'quota_scale_dospem1' => $quotaScaleDospem1,
            'quota_scale_dospem2' => $quotaScaleDospem2,

            'gap_topik' => $this->calculateGap($topicScale),
            'gap_homebase' => $this->calculateGap($homebaseScale),
            'gap_jabatan' => $this->calculateGap($jabatanScale),
            'gap_kuota_dospem1' => $this->calculateGap($quotaScaleDospem1),
            'gap_kuota_dospem2' => $this->calculateGap($quotaScaleDospem2),

            'bobot_gap_topik' => $topicWeight,
            'bobot_gap_homebase' => $homebaseWeight,
            'bobot_gap_jabatan' => $jabatanWeight,
            'bobot_gap_kuota_dospem1' => $quotaWeightDospem1,
            'bobot_gap_kuota_dospem2' => $quotaWeightDospem2,

            'cf' => $cf,
            'sf_dospem1' => $sfDospem1,
            'sf_dospem2' => $sfDospem2,

            'final_score_dospem1' => $finalScoreDospem1,
            'final_score_dospem2' => $finalScoreDospem2,

            'remaining_kuota_dospem1' => $remainingKuotaDospem1,
            'remaining_kuota_dospem2' => $remainingKuotaDospem2,

            'eligible_dospem1' => $this->isEligibleForDospem1($dosen),
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
     * Threshold: 0.8 / 0.6 / 0.4 / 0.2.
     */
    private function similarityToScale(float $similarity): int
    {
        return match (true) {
            $similarity >= 0.8 => 5,
            $similarity >= 0.6 => 4,
            $similarity >= 0.4 => 3,
            $similarity >= 0.2 => 2,
            default => 1,
        };
    }

    /**
     * 5 = program studi mahasiswa & dosen sama, 1 = berbeda.
     */
    private function homebaseToScale(PengajuanJudul $pengajuan, Dosen $dosen): int
    {
        $mahasiswaProdi = strtolower(trim((string) $pengajuan->mahasiswa?->programStudi?->program_studi));
        $dosenProdi = strtolower(trim((string) $dosen->programStudi?->program_studi));

        if ($mahasiswaProdi !== '' && $dosenProdi !== '' && $mahasiswaProdi === $dosenProdi) {
            return 5;
        }

        return 1;
    }

    private function jabatanToScale(?string $jabatan): int
    {
        return self::JABATAN_SCALE[strtolower(trim((string) $jabatan))] ?? 1;
    }

    private function quotaToScale(int $remainingQuota): int
    {
        return match (true) {
            $remainingQuota >= 5 => 5,
            $remainingQuota >= 3 => 4,
            $remainingQuota >= 1 => 3,
            default => 1,
        };
    }
}
