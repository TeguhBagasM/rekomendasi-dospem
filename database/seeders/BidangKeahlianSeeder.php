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
            'Basis Data',
            'Analisis Sistem',
            'Rekayasa Perangkat Lunak',
            'Jaringan Komputer',
            'Cyber Security',
            'Internet of Things',
            'Sistem Informasi',
            'Akuntansi Komputer',
            'Audit Sistem',
        ];

        foreach ($bidangKeahlian as $nama) {
            BidangKeahlian::firstOrCreate([
                'nama' => $nama,
            ]);
        }
    }
}