<?php

namespace App\Services;

use App\Models\Dosen;
use Illuminate\Support\Collection;

/**
 * Bertanggung jawab MURNI atas proses assignment dospem1 & dospem2
 * berdasarkan ranking Profile Matching dan sisa kuota dosen.
 *
 * Kuota yang dipakai di sini adalah state SEMENTARA (in-memory) yang
 * berjalan sepanjang satu batch generate, BUKAN field
 * kuota_terpakai_dospem1 / kuota_terpakai_dospem2 di database.
 * Field database baru diupdate saat admin melakukan Penetapan Dosen
 * Pembimbing (lihat PenetapanDospembingImport).
 */
class RecommendationAssignmentService
{
    /**
     * Bangun state kuota awal untuk satu batch generate.
     *
     * @return array{dospem1: array<int,int>, dospem2: array<int,int>}
     */
    public function initializeQuotaState(Collection $dosens): array
    {
        $remainingDospem1 = [];
        $remainingDospem2 = [];

        foreach ($dosens as $dosen) {
            /** @var Dosen $dosen */
            $remainingDospem1[$dosen->id] = max(
                ((int) $dosen->kuota_dospem1) - ((int) $dosen->kuota_terpakai_dospem1),
                0
            );

            $remainingDospem2[$dosen->id] = max(
                ((int) $dosen->kuota_dospem2) - ((int) $dosen->kuota_terpakai_dospem2),
                0
            );
        }

        return [
            'dospem1' => $remainingDospem1,
            'dospem2' => $remainingDospem2,
        ];
    }

    /**
     * Lakukan assignment dospem1 & dospem2 untuk satu pengajuan,
     * lalu kurangi kuota state secara langsung (by reference) agar
     * berlaku untuk pengajuan-pengajuan berikutnya dalam batch yang sama.
     *
     * @param  Collection  $rankedDospem1  Kandidat terurut DESC berdasarkan final_score_dospem1
     * @param  Collection  $rankedDospem2  Kandidat terurut DESC berdasarkan final_score_dospem2
     * @param  array{dospem1: array<int,int>, dospem2: array<int,int>}  $quotaState
     * @return array{dospem1_row: ?array, dospem2_row: ?array}
     */
    public function assign(Collection $rankedDospem1, Collection $rankedDospem2, array &$quotaState): array
    {
        $dospem1Row = $this->pickDospem1($rankedDospem1, $quotaState['dospem1']);

        if ($dospem1Row !== null) {
            $quotaState['dospem1'][$dospem1Row['dosen_id']] -= 1;
        }

        $dospem2Row = $this->pickDospem2($rankedDospem2, $quotaState['dospem2'], $dospem1Row);

        if ($dospem2Row !== null) {
            $quotaState['dospem2'][$dospem2Row['dosen_id']] -= 1;
        }

        return [
            'dospem1_row' => $dospem1Row,
            'dospem2_row' => $dospem2Row,
        ];
    }

    /**
     * RULE DOSPEM 1
     * - homebase = Teknik Informatika (eligible_dospem1)
     * - kuota dospem1 masih tersedia
     * - belum dipilih sebagai dospem2 (untuk pengajuan yang sama —
     *   otomatis terjamin karena dospem2 baru dipilih setelah ini)
     *
     * @param  array<int,int>  $remainingDospem1
     */
    private function pickDospem1(Collection $ranked, array $remainingDospem1): ?array
    {
        foreach ($ranked as $row) {
            if (! $row['eligible_dospem1']) {
                continue;
            }

            if (($remainingDospem1[$row['dosen_id']] ?? 0) <= 0) {
                continue;
            }

            return $row;
        }

        return null;
    }

    /**
     * RULE DOSPEM 2
     * - tidak boleh sama dengan dospem1
     * - kuota dospem2 masih tersedia
     * - homebase bebas
     * - jabatan dospem1 harus lebih tinggi atau sama dengan jabatan dospem2
     *
     * @param  array<int,int>  $remainingDospem2
     */
    private function pickDospem2(Collection $ranked, array $remainingDospem2, ?array $dospem1Row): ?array
    {
        // Tanpa dospem1 tidak ada acuan jabatan untuk pairing → tidak assign dospem2.
        if ($dospem1Row === null) {
            return null;
        }

        foreach ($ranked as $row) {
            if ($row['dosen_id'] === $dospem1Row['dosen_id']) {
                continue;
            }

            if (($remainingDospem2[$row['dosen_id']] ?? 0) <= 0) {
                continue;
            }

            if ($dospem1Row['jabatan_rank'] < $row['jabatan_rank']) {
                continue;
            }

            return $row;
        }

        return null;
    }
}