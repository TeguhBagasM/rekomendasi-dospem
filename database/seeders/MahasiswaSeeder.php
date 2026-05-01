<?php

namespace Database\Seeders;

use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class MahasiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mahasiswaList = [
            ['nim' => '22010001', 'nama' => 'Andi Pratama', 'program_studi_kode' => '55401', 'angkatan' => '2022', 'email' => 'andi.pratama@mahasiswa.local', 'no_hp' => '081234560001'],
            ['nim' => '22010002', 'nama' => 'Siti Aisyah', 'program_studi_kode' => '57402', 'angkatan' => '2022', 'email' => 'siti.aisyah@mahasiswa.local', 'no_hp' => '081234560002'],
            ['nim' => '23010001', 'nama' => 'Budi Santoso', 'program_studi_kode' => '57401', 'angkatan' => '2023', 'email' => 'budi.santoso@mahasiswa.local', 'no_hp' => '081234560003'],
            ['nim' => '23010002', 'nama' => 'Nadia Putri', 'program_studi_kode' => '55201', 'angkatan' => '2023', 'email' => 'nadia.putri@mahasiswa.local', 'no_hp' => '081234560004'],
        ];

        foreach ($mahasiswaList as $item) {
            $programStudi = ProgramStudi::where('kode_prodi', $item['program_studi_kode'])->first();

            if (! $programStudi) {
                continue;
            }

            $user = User::updateOrCreate(
                ['email' => $item['email']],
                [
                    'name' => $item['nama'],
                    'password' => Hash::make($item['nim']),
                ]
            );

            $user->assignRole(Role::firstOrCreate(['name' => 'mahasiswa']));

            Mahasiswa::updateOrCreate(
                ['nim' => $item['nim']],
                [
                    'user_id' => $user->id,
                    'nama' => $item['nama'],
                    'program_studi_id' => $programStudi->id,
                    'angkatan' => $item['angkatan'],
                    'no_hp' => $item['no_hp'],
                ]
            );
        }
    }
}
