<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DosenSeeder extends Seeder
{
    public function run(): void
    {
        $dosenList = [
            [
                'name' => 'Feri Alpiyasin',
                'email' => 'feri.alpiyasin@stmik-mi.ac.id',
                'password' => '12345678',
                'nidn' => '0422029205',
                'nama' => 'Feri Alpiyasin, M.Kom.',
                'program_studi' => 'Teknik Informatika',
                'jabatan_fungsional' => 'Lektor',
                'pendidikan_terakhir' => 'S2',
                'kuota_dospem1' => 10,
                'kuota_dospem2' => 15,
                'no_hp' => '081234567801',
            ],
            [
                'name' => 'Hasanah Tisna Amijaya',
                'email' => 'hasanah.amijaya@stmik-mi.ac.id',
                'password' => '12345678',
                'nidn' => '0429098105',
                'nama' => 'Hasanah Tisna Amijaya, S.Pd., M.Ak.',
                'program_studi' => 'Komputerisasi Akuntansi',
                'jabatan_fungsional' => 'Asisten Ahli',
                'pendidikan_terakhir' => 'S2',
                'kuota_dospem1' => 10,
                'kuota_dospem2' => 15,
                'no_hp' => '081234567802',
            ],
            [
                'name' => 'Jajat Sudrajat',
                'email' => 'jajat.sudrajat@stmik-mi.ac.id',
                'password' => '12345678',
                'nidn' => '0406087303',
                'nama' => 'Jajat Sudrajat, M.Kom.',
                'program_studi' => 'Manajemen Informatika',
                'jabatan_fungsional' => 'Lektor',
                'pendidikan_terakhir' => 'S2',
                'kuota_dospem1' => 10,
                'kuota_dospem2' => 15,
                'no_hp' => '081234567803',
            ],
            [
                'name' => 'Toni Kusnandar',
                'email' => 'toni.kusnandar@stmik-mi.ac.id',
                'password' => '12345678',
                'nidn' => '0411126805',
                'nama' => 'Dr. Toni Kusnandar, M.T.',
                'program_studi' => 'Teknik Informatika',
                'jabatan_fungsional' => 'Lektor',
                'pendidikan_terakhir' => 'S3',
                'kuota_dospem1' => 10,
                'kuota_dospem2' => 15,
                'no_hp' => '081234567804',
            ],
        ];

        foreach ($dosenList as $item) {
            $prodi = ProgramStudi::where(
                'program_studi',
                $item['program_studi']
            )->first();

            $user = User::updateOrCreate(
                ['email' => $item['email']],
                [
                    'name' => $item['name'],
                    'password' => Hash::make($item['password']),
                ]
            );

            $user->assignRole(
                Role::firstOrCreate(['name' => 'dosen'])
            );

            Dosen::updateOrCreate(
                ['nidn' => $item['nidn']],
                [
                    'user_id' => $user->id,
                    'program_studi_id' => $prodi?->id,
                    'nama' => $item['nama'],
                    'jabatan_fungsional' => $item['jabatan_fungsional'],
                    'pendidikan_terakhir' => $item['pendidikan_terakhir'],
                    'kuota_dospem1' => $item['kuota_dospem1'],
                    'kuota_dospem2' => $item['kuota_dospem2'],
                    'no_hp' => $item['no_hp'],
                ]
            );
        }
    }
}