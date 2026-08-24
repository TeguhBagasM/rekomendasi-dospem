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
            ['nim' => '22110287', 'nama' => 'Alfi Yudin Raihan', 'program_studi_kode' => '55201', 'angkatan' => '2022', 'email' => 'alfi@stmik-mi.ac.id', 'no_hp' => '081234560004'],
            ['nim' => '22110382', 'nama' => 'Rinda Tiara', 'program_studi_kode' => '55201', 'angkatan' => '2022', 'email' => 'rinda@stmik-mi.ac.id', 'no_hp' => '081234560002'],
            ['nim' => '22110439', 'nama' => 'Nurulita Septiani', 'program_studi_kode' => '55201', 'angkatan' => '2022', 'email' => 'nurulita@stmik-mi.ac.id', 'no_hp' => '081234560003'],
            ['nim' => '22110351', 'nama' => 'Maya Eka Nurulita', 'program_studi_kode' => '55201', 'angkatan' => '2022', 'email' => 'maya@stmik-mi.ac.id', 'no_hp' => '081234560001'],
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
