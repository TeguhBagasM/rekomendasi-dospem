<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\KuotaDosenGelombang;
use Illuminate\Support\Collection;

/**
 * Bertanggung jawab MURNI atas proses assignment dospem1 & dospem2
 * berdasarkan ranking Profile Matching dan sisa kuota dosen per gelombang.
 *
 * Kuota yang dipakai di sini adalah state SEMENTARA (in-memory) yang
 * berjalan sepanjang satu batch generate, BUKAN field di database.
 * Field database baru diupdate saat admin melakukan Penetapan Dosen
 * Pembimbing (lihat PenetapanDospembingImport).
 *
 * Kuota diinisialisasi dari tabel kuota_dosen_gelombangs (per-gelombang),
 * bukan dari dosens.kuota_dospem1/2 (lifetime).
 *
 * ==================== BUSINESS RULE ====================
 * DOSPEM 1:
 *   - WAJIB dari Program Studi Teknik Informatika
 *   - Kuota dospem1 di gelombang ini masih tersedia
 *
 * DOSPEM 2:
 *   - Tidak boleh sama dengan dospem1
 *   - Kuota dospem2 di gelombang ini masih tersedia
 *   - Jabatan dospem1 >= jabatan dospem2
 *   - Diprioritaskan dari dosen NON Teknik Informatika terlebih dahulu
 *   - Jika semua dosen non-TI sudah tidak ada kuota, baru boleh
 *     memilih dosen Teknik Informatika
 *   - Di antara kandidat yang memenuhi syarat, pilih nilai akhir
 *     Profile Matching tertinggi (tidak random)
 * =========================================================
 */
class RecommendationAssignmentService
{
    /**
     * Bangun state kuota awal untuk satu batch generate dari tabel
     * kuota_dosen_gelombangs (kuota per gelombang, bukan lifetime).
     *
     * @return array{dospem1: array<int,int>, dospem2: array<int,int>}
     */
    public function initializeQuotaState(Collection $dosens, int $gelombangId): array
    {
        $remainingDospem1 = [];
        $remainingDospem2 = [];

        foreach ($dosens as $dosen) {
            /** @var Dosen $dosen */
            $kuota = KuotaDosenGelombang::where('dosen_id', $dosen->id)
                ->where('gelombang_id', $gelombangId)
                ->first();

            if (! $kuota) {
                continue;
            }

            $remaining = max($kuota->kuota_dospem1 - $kuota->kuota_terpakai_dospem1, 0);
            if ($remaining > 0) {
                $remainingDospem1[$dosen->id] = $remaining;
            }

            $remaining = max($kuota->kuota_dospem2 - $kuota->kuota_terpakai_dospem2, 0);
            if ($remaining > 0) {
                $remainingDospem2[$dosen->id] = $remaining;
            }
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
     * - WAJIB dari Program Studi Teknik Informatika (eligible_dospem1)
     * - Kuota dospem1 masih tersedia
     * - Diambil dari ranking tertinggi (sudah terurut DESC)
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
     * - Tidak boleh sama dengan dospem1
     * - Kuota dospem2 masih tersedia
     * - Jabatan dospem1 >= jabatan dospem2
     * - Diprioritaskan dari dosen NON Teknik Informatika terlebih dahulu
     * - Jika semua dosen non-TI sudah tidak ada kuota, baru boleh
     *   memilih dosen Teknik Informatika
     * - Di antara kandidat yang memenuhi syarat, pilih nilai akhir
     *   Profile Matching tertinggi (sudah terurut DESC)
     *
     * @param  array<int,int>  $remainingDospem2
     */
    private function pickDospem2(Collection $ranked, array $remainingDospem2, ?array $dospem1Row): ?array
    {
        // Tanpa dospem1 tidak ada acuan jabatan untuk pairing → tidak assign dospem2.
        if ($dospem1Row === null) {
            return null;
        }

        // Pass 1: Prioritas dosen NON Teknik Informatika
        $nonTiPick = $this->findBestCandidate($ranked, $remainingDospem2, $dospem1Row, onlyTi: false);

        if ($nonTiPick !== null) {
            return $nonTiPick;
        }

        // Pass 2: Semua dosen non-TI sudah habis kuota, fallback ke Teknik Informatika
        return $this->findBestCandidate($ranked, $remainingDospem2, $dospem1Row, onlyTi: true);
    }

    /**
     * Cari kandidat terbaik dari ranked list berdasarkan filter.
     *
     * Karena $ranked sudah terurut DESC berdasarkan final_score,
     * kandidat pertama yang memenuhi semua syarat = nilai akhir tertinggi.
     *
     * @param  array<int,int>  $remainingQuota
     * @param  bool  $onlyTi  true = hanya dosen TI, false = hanya dosen non-TI
     */
    private function findBestCandidate(
        Collection $ranked,
        array $remainingQuota,
        array $dospem1Row,
        bool $onlyTi,
    ): ?array {
        foreach ($ranked as $row) {
            // Tidak boleh sama dengan dospem1
            if ($row['dosen_id'] === $dospem1Row['dosen_id']) {
                continue;
            }

            // Kuota dospem2 masih tersedia
            if (($remainingQuota[$row['dosen_id']] ?? 0) <= 0) {
                continue;
            }

            // Jabatan dospem1 >= jabatan dospem2
            if ($dospem1Row['jabatan_rank'] < $row['jabatan_rank']) {
                continue;
            }

            // Filter berdasarkan prodi dosen
            $isTi = $row['eligible_dospem1']; // true = Teknik Informatika

            if ($onlyTi && ! $isTi) {
                continue;
            }

            if (! $onlyTi && $isTi) {
                continue;
            }

            return $row;
        }

        return null;
    }
}
