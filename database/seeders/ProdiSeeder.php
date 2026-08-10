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
                'jenjang' => 'D3',
                'kaprodi' => 'Deni Sopiyan, S.Kom., M.Kom.',
            ],
            [
                'program_studi' => 'Komputerisasi Akuntansi',
                'kode_prodi' => '57402',
                'jenjang' => 'D3',
                'kaprodi' => 'Ganjar Nurul Fajar, S.E., M.Ak.',
            ],
            [
                'program_studi' => 'Manajemen Informatika',
                'kode_prodi' => '57401',
                'jenjang' => 'D3',
                'kaprodi' => 'Jajat Sudrajat, M.Kom.',
            ],
            [
                'program_studi' => 'Teknik Informatika',
                'kode_prodi' => '55201',
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
