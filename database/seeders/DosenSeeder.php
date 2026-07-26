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
                'name' => 'Titi Widaretna',
                'email' => 'titi.widaretna@stmik-mi.ac.id',
                'password' => '12345678',
                'nidn' => '0420029404',
                'nama' => 'Titi Widaretna, S.T., M.Kom.',
                'program_studi' => 'Teknik Informatika',
                'jabatan_fungsional' => 'Tenaga Pengajar',
                'pendidikan_terakhir' => 'S2',
                'kuota_dospem1' => 5,
                'kuota_dospem2' => 5,
                'no_hp' => '081234567801',
            ],
            [
                'name' => 'Heri Wahyudi',
                'email' => 'heri.wahyudi@stmik-mi.ac.id',
                'password' => '12345678',
                'nidn' => '0420078308',
                'nama' => 'Heri Wahyudi, S.T., M.Kom.',
                'program_studi' => 'Teknik Informatika',
                'jabatan_fungsional' => 'Lektor',
                'pendidikan_terakhir' => 'S2',
                'kuota_dospem1' => 5,
                'kuota_dospem2' => 5,
                'no_hp' => '081234567802',
            ],
            [
                'name' => 'Asep Ririh Riswaya',
                'email' => 'asep.ririh@stmik-mi.ac.id',
                'password' => '12345678',
                'nidn' => '0408109005',
                'nama' => 'Asep Ririh Riswaya, S.Kom., M.Kom.',
                'program_studi' => 'Teknik Informatika',
                'jabatan_fungsional' => 'Lektor',
                'pendidikan_terakhir' => 'S2',
                'kuota_dospem1' => 10,
                'kuota_dospem2' => 5,
                'no_hp' => '081234567803',
            ],
            [
                'name' => 'Toni Kusnandar',
                'email' => 'toni.kusnandar@stmik-mi.ac.id',
                'password' => '12345678',
                'nidn' => '0411126805',
                'nama' => 'Dr. Toni Kusnandar, S.E., M.T., M.T.',
                'program_studi' => 'Teknik Informatika',
                'jabatan_fungsional' => 'Lektor',
                'pendidikan_terakhir' => 'S3',
                'kuota_dospem1' => 10,
                'kuota_dospem2' => 5,
                'no_hp' => '081234567804',
            ],
            [
                'name' => 'Deni Sopiyan',
                'email' => 'deni.sopiyan@stmik-mi.ac.id',
                'password' => '12345678',
                'nidn' => '5257780000000000',
                'nama' => 'Deni Sopiyan, S.Kom., M.Kom.',
                'program_studi' => 'Teknik Informatika',
                'jabatan_fungsional' => 'Asisten Ahli',
                'pendidikan_terakhir' => 'S2',
                'kuota_dospem1' => 5,
                'kuota_dospem2' => 5,
                'no_hp' => '081234567805',
            ],
            [
                'name' => 'Ganjar Nurul Fajar',
                'email' => 'ganjar.fajar@stmik-mi.ac.id',
                'password' => '12345678',
                'nidn' => '0413048004',
                'nama' => 'Ganjar Nurul Fajar, S.E., M.Ak.',
                'program_studi' => 'Komputerisasi Akuntansi',
                'jabatan_fungsional' => 'Tenaga Pengajar',
                'pendidikan_terakhir' => 'S2',
                'kuota_dospem1' => 0,
                'kuota_dospem2' => 2,
                'no_hp' => '081234567806',
            ],
            [
                'name' => 'Ikbal Jamaludin',
                'email' => 'ikbal.jamaludin@stmik-mi.ac.id',
                'password' => '12345678',
                'nidn' => '0000000001',
                'nama' => 'Ikbal Jamaludin, M.Kom.',
                'program_studi' => 'Teknik Informatika',
                'jabatan_fungsional' => 'Asisten Ahli',
                'pendidikan_terakhir' => 'S2',
                'kuota_dospem1' => 5,
                'kuota_dospem2' => 5,
                'no_hp' => '081234567807',
            ],
            [
                'name' => 'Susanto',
                'email' => 'susanto@stmik-mi.ac.id',
                'password' => '12345678',
                'nidn' => '0426068054',
                'nama' => 'Susanto, S.Kom., M.Kom.',
                'program_studi' => 'Teknik Informatika',
                'jabatan_fungsional' => 'Tenaga Pengajar',
                'pendidikan_terakhir' => 'S2',
                'kuota_dospem1' => 5,
                'kuota_dospem2' => 5,
                'no_hp' => '081234567808',
            ],
            [
                'name' => 'Ahfi Fauka Pranata',
                'email' => 'ahfi.pranata@stmik-mi.ac.id',
                'password' => '12345678',
                'nidn' => '0409129401',
                'nama' => 'Ahfi Fauka Pranata, S.Kom., M.M.',
                'program_studi' => 'Teknik Informatika',
                'jabatan_fungsional' => 'Tenaga Pengajar',
                'pendidikan_terakhir' => 'S2',
                'kuota_dospem1' => 5,
                'kuota_dospem2' => 5,
                'no_hp' => '081234567809',
            ],
            [
                'name' => 'Rohmat Nur Ibrahim',
                'email' => 'rohmat.ibrahim@stmik-mi.ac.id',
                'password' => '12345678',
                'nidn' => '0428057001',
                'nama' => 'Rohmat Nur Ibrahim, S.Kom., M.T.',
                'program_studi' => 'Teknik Informatika',
                'jabatan_fungsional' => 'Lektor',
                'pendidikan_terakhir' => 'S2',
                'kuota_dospem1' => 10,
                'kuota_dospem2' => 5,
                'no_hp' => '081234567810',
            ],
            [
                'name' => 'Fitri Rizqiati',
                'email' => 'fitri.rizqiati@stmik-mi.ac.id',
                'password' => '12345678',
                'nidn' => '2349780000000000',
                'nama' => 'Fitri Rizqiati, S.Kom., M.Kom.',
                'program_studi' => 'Teknik Informatika',
                'jabatan_fungsional' => 'Tenaga Pengajar',
                'pendidikan_terakhir' => 'S2',
                'kuota_dospem1' => 5,
                'kuota_dospem2' => 5,
                'no_hp' => '081234567811',
            ],
            [
                'name' => 'Lilis Emalia',
                'email' => 'lilis.emalia@stmik-mi.ac.id',
                'password' => '12345678',
                'nidn' => '0419067804',
                'nama' => 'Lilis Emalia, A.Md., S.T., M.Kom.',
                'program_studi' => 'Teknik Informatika',
                'jabatan_fungsional' => 'Lektor',
                'pendidikan_terakhir' => 'S2',
                'kuota_dospem1' => 10,
                'kuota_dospem2' => 5,
                'no_hp' => '081234567812',
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
