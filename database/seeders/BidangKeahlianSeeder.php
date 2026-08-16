<?php

namespace Database\Seeders;

use App\Models\BidangKeahlian;
use Illuminate\Database\Seeder;

class BidangKeahlianSeeder extends Seeder
{
    public function run(): void
    {
        $bidangKeahlian = [
            'Manajemen',
            'Data Science',
            'Machine Learning / Deep Learning',
            'Data Mining',
            'Natural Language Processing',
            'Computer Vision / Image Processing',
            'Sistem Pendukung Keputusan',
            'Software Development',
            'Database / Big Data',
            'Cyber Security',
            'Internet Of Things',
            'Sistem Informasi',
            'Akuntansi Keuangan',
            'Akuntansi',
            'Perpajakan Komputerisasi',
            'Mobile Development',
            'Matematika',
        ];

        foreach ($bidangKeahlian as $nama) {
            BidangKeahlian::firstOrCreate([
                'nama' => $nama,
            ]);
        }
    }
}
