<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\PengajuanJudul;
use Illuminate\Support\Collection;

class ProfileMatchingService
{
    private const CORE_WEIGHT = 0.6;

    private const SECONDARY_WEIGHT = 0.4;

    public function __construct(
        private readonly TextSimilarityService $similarityService
    ) {}

    public function recommend(PengajuanJudul $pengajuan): array
    {
        $pengajuan->loadMissing('mahasiswa.programStudi');

        $dosens = Dosen::with([
            'keahlianDosens',
            'dospem1Pengajuan' => fn ($query) => $query->where('status', 'disetujui'),
            'dospem2Pengajuan' => fn ($query) => $query->where('status', 'disetujui'),
        ])->get();

        $scores = $dosens
            ->map(fn (Dosen $dosen) => $this->buildScoreRow($pengajuan->judul_skripsi, $dosen))
            ->values();

        $rankedForDospem1 = $scores->sortByDesc('nilai_akhir_dospem1')->values();
        $rankedForDospem2 = $scores->sortByDesc('nilai_akhir_dospem2')->values();

        // Debug point: dd($rankedForDospem1->take(5)->toArray()) if the ranking needs inspection.

        $dospem1Row = $rankedForDospem1->first(function (array $row): bool {
            return $row['remaining_kuota_dospem1'] > 0;
        });

        $dospem2Row = $rankedForDospem2->first(function (array $row) use ($dospem1Row): bool {
            if (! $dospem1Row) {
                return false;
            }

            return $row['dosen']->id !== $dospem1Row['dosen']->id
                && $row['remaining_kuota_dospem2'] > 0;
        });

        return [
            'dospem1' => $dospem1Row['dosen'] ?? null,
            'dospem2' => $dospem2Row['dosen'] ?? null,
            'scores' => $scores->map(fn (array $row) => $this->formatScoreRow($row))->all(),
        ];
    }

    public function rekomendasikan(PengajuanJudul $pengajuan): array
    {
        return $this->recommend($pengajuan);
    }

    public function rekomendasikanBulk(Collection $pengajuans): Collection
    {
        return $pengajuans->map(fn (PengajuanJudul $pengajuan) => $this->recommend($pengajuan));
    }

    private function buildScoreRow(string $judul, Dosen $dosen): array
    {
        $coreKeahlian = $dosen->keahlianDosens->pluck('deskripsi_keahlian')->filter()->values()->all();
        $topicSimilarity = $this->similarityService->getSimilarityScore($judul, $coreKeahlian);
        $topicScale = $this->similarityToScale($topicSimilarity);

        $remainingKuotaDospem1 = $dosen->getRemainingKuotaDospem1();
        $remainingKuotaDospem2 = $dosen->getRemainingKuotaDospem2();

        $quotaScaleDospem1 = $this->quotaToScale($remainingKuotaDospem1);
        $quotaScaleDospem2 = $this->quotaToScale($remainingKuotaDospem2);
        $jabatanScale = $this->jabatanToScale($dosen->jabatan_fungsional);
        $pendidikanScale = $this->pendidikanToScale($dosen->pendidikan_terakhir);

        $cfDospem1 = $this->coreFactor($topicScale, $quotaScaleDospem1);
        $cfDospem2 = $this->coreFactor($topicScale, $quotaScaleDospem2);
        $sf = $this->secondaryFactor($jabatanScale, $pendidikanScale);

        return [
            'dosen' => $dosen,
            'dosen_id' => $dosen->id,
            'dosen_nama' => $dosen->nama,
            'topic_similarity' => $topicSimilarity,
            'topic_scale' => $topicScale,
            'quota_scale_dospem1' => $quotaScaleDospem1,
            'quota_scale_dospem2' => $quotaScaleDospem2,
            'jabatan_scale' => $jabatanScale,
            'pendidikan_scale' => $pendidikanScale,
            'cf_dospem1' => $cfDospem1,
            'cf_dospem2' => $cfDospem2,
            'sf' => $sf,
            'nilai_akhir_dospem1' => $this->finalScore($cfDospem1, $sf),
            'nilai_akhir_dospem2' => $this->finalScore($cfDospem2, $sf),
            'remaining_kuota_dospem1' => $remainingKuotaDospem1,
            'remaining_kuota_dospem2' => $remainingKuotaDospem2,
        ];
    }

    private function formatScoreRow(array $row): array
    {
        unset($row['dosen']);

        return $row;
    }

    private function coreFactor(int $topicScale, int $quotaScale): float
    {
        $topicBobot = $this->gapToWeight($topicScale - 5);
        $quotaBobot = $this->gapToWeight($quotaScale - 5);

        return ($topicBobot + $quotaBobot) / 2;
    }

    private function secondaryFactor(int $jabatanScale, int $pendidikanScale): float
    {
        $jabatanBobot = $this->gapToWeight($jabatanScale - 3);
        $pendidikanBobot = $this->gapToWeight($pendidikanScale - 3);

        return ($jabatanBobot + $pendidikanBobot) / 2;
    }

    private function finalScore(float $cf, float $sf): float
    {
        return ($cf * self::CORE_WEIGHT) + ($sf * self::SECONDARY_WEIGHT);
    }

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

    private function pendidikanToScale(?string $pendidikan): int
    {
        return match (trim((string) $pendidikan)) {
            'S3' => 5,
            'S2' => 3,
            default => 1,
        };
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

    private function gapToWeight(int $gap): float
    {
        return match ($gap) {
            0 => 5.0,
            1 => 4.5,
            -1 => 4.0,
            2 => 3.5,
            -2 => 3.0,
            3 => 2.5,
            -3 => 2.0,
            4 => 1.5,
            -4 => 1.0,
            default => 0.5,
        };
    }
}