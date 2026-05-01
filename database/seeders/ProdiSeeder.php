<?php

namespace Database\Seeders;

use App\Models\ProgramStudi;
use Illuminate\Database\Seeder;

class ProdiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $prodis = [
            [
                'program_studi' => 'Teknik Informatika',
                'kode_prodi' => '55401',
                'fakultas' => 'Fakultas Teknik',
                'jenjang' => 'D3',
                'kaprodi' => 'Feri Alpiyasin, M.Kom.',
            ],
            [
                'program_studi' => 'Komputerisasi Akuntansi',
                'kode_prodi' => '57402',
                'fakultas' => 'Fakultas Teknik',
                'jenjang' => 'D3',
                'kaprodi' => 'Hasanah Tisna Amijaya, S.Pd., M.Ak',
            ],
            [
                'program_studi' => 'Manajemen Informatika',
                'kode_prodi' => '57401',
                'fakultas' => 'Fakultas Teknik',
                'jenjang' => 'D3',
                'kaprodi' => 'Jajat Sudrajat, M.Kom.',
            ],
            [
                'program_studi' => 'Teknik Informatika',
                'kode_prodi' => '55201',
                'fakultas' => 'Fakultas Teknik',
                'jenjang' => 'S1',
                'kaprodi' => 'Dr. Toni Kusnandar, M.T.',
            ],
        ];

        foreach ($prodis as $prodi) {
            ProgramStudi::updateOrCreate(
                ['kode_prodi' => $prodi['kode_prodi']],
                $prodi
            );
        }
    }
}
