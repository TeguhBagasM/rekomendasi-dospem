<?php

namespace App\Imports;

use App\Models\Dosen;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DosenImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    use Importable;

    public int $created = 0;
    public int $updated = 0;
    public array $errors = [];

    public function collection(\Illuminate\Support\Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +1 heading row, +1 index offset

            $nidn = trim((string) ($row['nidn'] ?? ''));

            if ($nidn === '') {
                $this->errors[] = "Baris {$rowNumber}: NIDN kosong, dilewati.";
                continue;
            }

            $nama               = trim((string) ($row['nama_lengkap'] ?? ''));
            $namaProdi          = trim((string) ($row['program_studi'] ?? ''));
            $jabatanFungsional  = trim((string) ($row['jabatan_fungsional'] ?? '')) ?: null;
            $pendidikanTerakhir = trim((string) ($row['pendidikan_terakhir'] ?? '')) ?: null;
            $noHp               = trim((string) ($row['no_hp'] ?? '')) ?: null;
            $kuotaDospem1       = (int) ($row['kuota_dospem_1'] ?? 0);
            $kuotaDospem2       = (int) ($row['kuota_dospem_2'] ?? 0);

            if ($nama === '') {
                $this->errors[] = "Baris {$rowNumber}: Nama Lengkap kosong, dilewati.";
                continue;
            }

            $programStudiId = null;
            if ($namaProdi !== '') {
                $programStudiId = ProgramStudi::where('program_studi', $namaProdi)->value('id');
            }

            DB::transaction(function () use (
                $nidn,
                $nama,
                $programStudiId,
                $jabatanFungsional,
                $pendidikanTerakhir,
                $noHp,
                $kuotaDospem1,
                $kuotaDospem2,
            ) {
                $dosen = Dosen::where('nidn', $nidn)->first();

                if ($dosen) {
                    // Update data, jangan ubah kuota_terpakai
                    $dosen->update([
                        'nama'                => $nama,
                        'program_studi_id'    => $programStudiId,
                        'jabatan_fungsional'  => $jabatanFungsional,
                        'pendidikan_terakhir' => $pendidikanTerakhir,
                        'kuota_dospem1'       => $kuotaDospem1,
                        'kuota_dospem2'       => $kuotaDospem2,
                        'no_hp'               => $noHp,
                    ]);

                    if ($dosen->user) {
                        $dosen->user->update([
                            'name' => $nama,
                        ]);
                    }

                    $this->updated++;
                } else {
                    $user = User::create([
                        'name'     => $nama,
                        'email'    => $nidn . '@stmik-mi.ac.id',
                        'password' => Hash::make($nidn),
                    ]);
                    $user->assignRole('dosen');

                    Dosen::create([
                        'user_id'                => $user->id,
                        'nidn'                   => $nidn,
                        'nama'                   => $nama,
                        'program_studi_id'       => $programStudiId,
                        'jabatan_fungsional'     => $jabatanFungsional,
                        'pendidikan_terakhir'    => $pendidikanTerakhir,
                        'kuota_dospem1'          => $kuotaDospem1,
                        'kuota_dospem2'          => $kuotaDospem2,
                        'kuota_terpakai_dospem1' => 0,
                        'kuota_terpakai_dospem2' => 0,
                        'no_hp'                  => $noHp,
                    ]);

                    $this->created++;
                }
            });
        }
    }
}