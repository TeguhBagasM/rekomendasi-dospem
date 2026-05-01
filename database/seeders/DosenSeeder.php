<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DosenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dosenList = [
            [
                'name' => 'Feri Alpiyasin',
                'email' => 'feri.alpiyasin@dosen.local',
                'password' => '12345678',
                'nidn' => '11010101',
                'nama' => 'Feri Alpiyasin, M.Kom.',
                'jabatan' => 'Kaprodi',
                'program_studi' => 'Teknik Informatika',
                'kuota_bimbingan' => 5,
                'kuota_terisi' => 1,
                'no_hp' => '081234567801',
            ],
            [
                'name' => 'Hasanah Tisna Amijaya',
                'email' => 'hasanah.amijaya@dosen.local',
                'password' => '12345678',
                'nidn' => '11010102',
                'nama' => 'Hasanah Tisna Amijaya, S.Pd., M.Ak',
                'jabatan' => 'Dosen',
                'program_studi' => 'Komputerisasi Akuntansi',
                'kuota_bimbingan' => 4,
                'kuota_terisi' => 0,
                'no_hp' => '081234567802',
            ],
            [
                'name' => 'Jajat Sudrajat',
                'email' => 'jajat.sudrajat@dosen.local',
                'password' => '12345678',
                'nidn' => '11010103',
                'nama' => 'Jajat Sudrajat, M.Kom.',
                'jabatan' => 'Dosen',
                'program_studi' => 'Manajemen Informatika',
                'kuota_bimbingan' => 6,
                'kuota_terisi' => 2,
                'no_hp' => '081234567803',
            ],
            [
                'name' => 'Toni Kusnandar',
                'email' => 'toni.kusnandar@dosen.local',
                'password' => '12345678',
                'nidn' => '11010104',
                'nama' => 'Dr. Toni Kusnandar, M.T.',
                'jabatan' => 'Kaprodi',
                'program_studi' => 'Teknik Informatika',
                'kuota_bimbingan' => 5,
                'kuota_terisi' => 0,
                'no_hp' => '081234567804',
            ],
        ];

        foreach ($dosenList as $item) {
            $user = User::updateOrCreate(
                ['email' => $item['email']],
                [
                    'name' => $item['name'],
                    'password' => Hash::make($item['password']),
                ]
            );

            $user->assignRole(Role::firstOrCreate(['name' => 'dosen']));

            Dosen::updateOrCreate(
                ['nidn' => $item['nidn']],
                [
                    'user_id' => $user->id,
                    'nama' => $item['nama'],
                    'jabatan' => $item['jabatan'],
                    'program_studi' => $item['program_studi'],
                    'kuota_bimbingan' => $item['kuota_bimbingan'],
                    'kuota_terisi' => $item['kuota_terisi'],
                    'no_hp' => $item['no_hp'],
                ]
            );
        }
    }
}
