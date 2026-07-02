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
            'Matematika',
            'Data Science',
            'Machine Learning',
            'Data Mining',
            'Deep Learning',
            'Computer Vision',
            'Natural Language Processing',
            'Sistem Pendukung Keputusan',
            'Pemrograman Web',
            'Database',
            'Big Data',
            'Software Development',
            'Analisis Sistem',
            'Networking',
            'Cyber Security',
            'Internet of Things',
            'Sistem Informasi',
            'Akuntansi Keuangan',
            'Perpajakan Komputerisasi',
            'Mobile Development',
            'Image Processing',
        ];

        foreach ($bidangKeahlian as $nama) {
            BidangKeahlian::firstOrCreate([
                'nama' => $nama,
            ]);
        }
    }
}