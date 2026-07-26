<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class GelombangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gelombangs = [
            [
                'nama'             => 'Gelombang 1',
                'periode_akademik' => '20251',
                'tanggal_mulai'    => now()->addDays(7),
                'tanggal_selesai'  => now()->addDays(13),
                'is_active'        => true,
            ],
            [
                'nama'             => 'Gelombang 2',
                'periode_akademik' => '20252',
                'tanggal_mulai'    => now()->addDays(14),
                'tanggal_selesai'  => now()->addDays(20),
                'is_active'        => false,
            ],
            [
                'nama'             => 'Gelombang 3',
                'periode_akademik' => '20261',
                'tanggal_mulai'    => now()->addDays(21),
                'tanggal_selesai'  => now()->addDays(27),
                'is_active'        => false,
            ],
        ];

        foreach ($gelombangs as $data) {
            \App\Models\Gelombang::create($data);
        }
    }
}
