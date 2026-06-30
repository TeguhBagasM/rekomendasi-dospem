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
        // buatkan gelombang 1 sampai 4
        for ($i = 1; $i <= 4; $i++) {
            \App\Models\Gelombang::create([
                'nama' => 'Gelombang ' . $i,
                'tanggal_mulai' => now()->addDays($i * 7),
                'tanggal_selesai' => now()->addDays($i * 7 + 6),
                'is_active' => $i === 1, // hanya gelombang 1 yang aktif
            ]);
        }
    }
}
