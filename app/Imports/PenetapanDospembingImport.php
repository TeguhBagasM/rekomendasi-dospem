<?php

namespace App\Imports;

use App\Models\Dosen;
use App\Models\PenetapanDospembing;
use App\Models\PengajuanJudul;
use App\Models\RiwayatRekomendasi;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PenetapanDospembingImport implements ToCollection, WithHeadingRow
{
    /**
     * @throws ValidationException
     */
    public function collection(Collection $rows): void
    {
        // 1. Check if the file is empty
        if ($rows->isEmpty()) {
            throw ValidationException::withMessages([
                'file' => ['File Excel tidak memiliki data untuk di-import.'],
            ]);
        }

        // 2. Prevent duplicate import: Check if latest RiwayatRekomendasi is already finalized
        $latestRiwayat = RiwayatRekomendasi::latest()->first();
        if ($latestRiwayat && $latestRiwayat->status === 'finalized') {
            throw ValidationException::withMessages([
                'file' => ['Riwayat rekomendasi terakhir sudah difinalisasi. Import baru tidak diperbolehkan.'],
            ]);
        }

        $errors = [];
        $seenPengajuanIds = [];
        $validatedData = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // Row number in Excel (1-based + header)
            $data = $row->toArray();

            // Validate required columns
            $pengajuanId = $data['pengajuan_id'] ?? null;
            $nim = $data['nim'] ?? null;

            if (empty($pengajuanId) && empty($nim)) {
                $errors[] = "Baris #{$rowNum}: Kolom 'Pengajuan ID' atau 'NIM' harus diisi.";
                continue;
            }

            // Find PengajuanJudul
            $pengajuan = null;
            if (!empty($pengajuanId)) {
                $pengajuan = PengajuanJudul::find($pengajuanId);
            }
            if (!$pengajuan && !empty($nim)) {
                $pengajuan = PengajuanJudul::whereHas('mahasiswa', function ($query) use ($nim) {
                    $query->where('nim', $nim);
                })->latest()->first();
            }

            if (!$pengajuan) {
                $errors[] = "Baris #{$rowNum}: Pengajuan Judul tidak ditemukan untuk ID '{$pengajuanId}' atau NIM '{$nim}'.";
                continue;
            }

            // Check duplicate in the uploaded file
            if (in_array($pengajuan->id, $seenPengajuanIds)) {
                $errors[] = "Baris #{$rowNum}: Duplikasi data untuk Pengajuan ID '{$pengajuan->id}' di dalam file.";
                continue;
            }
            $seenPengajuanIds[] = $pengajuan->id;

            // Find lecturers by NIDN
            $nidnDospem1 = $data['final_nidn_dospem_1'] ?? $data['final_dospem1_nidn'] ?? $data['dospem1_nidn'] ?? null;
            $nidnDospem2 = $data['final_nidn_dospem_2'] ?? $data['final_dospem2_nidn'] ?? $data['dospem2_nidn'] ?? null;

            $dospem1 = null;
            if (!empty($nidnDospem1) && $nidnDospem1 !== '-') {
                $dospem1 = Dosen::where('nidn', $nidnDospem1)->first();
                if (!$dospem1) {
                    $errors[] = "Baris #{$rowNum}: Dosen Pembimbing 1 dengan NIDN '{$nidnDospem1}' tidak ditemukan.";
                    continue;
                }
            }

            $dospem2 = null;
            if (!empty($nidnDospem2) && $nidnDospem2 !== '-') {
                $dospem2 = Dosen::where('nidn', $nidnDospem2)->first();
                if (!$dospem2) {
                    $errors[] = "Baris #{$rowNum}: Dosen Pembimbing 2 dengan NIDN '{$nidnDospem2}' tidak ditemukan.";
                    continue;
                }
            }

            // Validate that dospem1 and dospem2 are not the same person
            if ($dospem1 && $dospem2 && $dospem1->id === $dospem2->id) {
                $errors[] = "Baris #{$rowNum}: Dosen Pembimbing 1 dan Dosen Pembimbing 2 tidak boleh dosen yang sama.";
                continue;
            }

            $validatedData[] = [
                'pengajuan' => $pengajuan,
                'dospem1' => $dospem1,
                'dospem2' => $dospem2,
                'catatan' => $data['catatan'] ?? null,
            ];
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages([
                'file' => $errors,
            ]);
        }

        // 3. Process the changes inside a single database transaction
        DB::transaction(function () use ($validatedData, $latestRiwayat) {
            foreach ($validatedData as $item) {
                $pengajuan = $item['pengajuan'];
                $dospem1 = $item['dospem1'];
                $dospem2 = $item['dospem2'];
                $catatan = $item['catatan'];

                // Revert previous lecturers' kuota if they were already assigned.
                // Kuota dospem1 & dospem2 terpisah sesuai skema tabel dosens.
                if ($pengajuan->dospem1_id) {
                    $oldDospem1 = Dosen::find($pengajuan->dospem1_id);
                    $oldDospem1?->decrement('kuota_terpakai_dospem1');
                }
                if ($pengajuan->dospem2_id) {
                    $oldDospem2 = Dosen::find($pengajuan->dospem2_id);
                    $oldDospem2?->decrement('kuota_terpakai_dospem2');
                }

                // Update PengajuanJudul with finalized lecturers
                $pengajuan->update([
                    'dospem1_id' => $dospem1?->id,
                    'dospem2_id' => $dospem2?->id,
                ]);

                // Create or update PenetapanDospembing
                PenetapanDospembing::updateOrCreate(
                    ['pengajuan_id' => $pengajuan->id],
                    [
                        'rekomendasi_dospembing_id' => $pengajuan->rekomendasiDospembing?->id,
                        'dospem1_id' => $dospem1?->id,
                        'dospem2_id' => $dospem2?->id,
                        'ditetapkan_oleh' => Auth::id(),
                        'ditetapkan_at' => now(),
                        'sumber' => 'import',
                        'catatan' => $catatan === '-' ? null : $catatan,
                    ]
                );

                // Increment new lecturers' kuota terpakai sesuai peran masing-masing
                if ($dospem1) {
                    $dospem1->increment('kuota_terpakai_dospem1');
                }
                if ($dospem2) {
                    $dospem2->increment('kuota_terpakai_dospem2');
                }
            }

            // Update riwayat status to finalized
            if ($latestRiwayat) {
                $latestRiwayat->update(['status' => 'finalized']);
            }
        });
    }
}
