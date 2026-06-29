<?php

namespace Database\Seeders;

use App\Models\BidangKeahlian;
use App\Models\Dosen;
use Illuminate\Database\Seeder;

class DosenBidangKeahlianSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            '11010101' => [
                'Pemrograman Web',
                'Basis Data',
                'Sistem Informasi',
            ],

            '11010102' => [
                'Akuntansi Komputer',
                'Audit Sistem',
                'Sistem Informasi',
            ],

            '11010103' => [
                'Analisis Sistem',
                'Jaringan Komputer',
                'Rekayasa Perangkat Lunak',
            ],

            '11010104' => [
                'Machine Learning',
                'Data Mining',
                'Deep Learning',
                'Computer Vision',
            ],
        ];

        foreach ($data as $nidn => $keahlianList) {
            $dosen = Dosen::where('nidn', $nidn)->first();

            if (!$dosen) {
                continue;
            }

            $ids = BidangKeahlian::whereIn(
                'nama',
                $keahlianList
            )->pluck('id');

            $dosen->bidangKeahlians()->syncWithoutDetaching($ids);
        }
    }
}