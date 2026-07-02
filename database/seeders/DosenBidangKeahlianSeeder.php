<?php

namespace Database\Seeders;

use App\Models\BidangKeahlian;
use App\Models\Dosen;
use Illuminate\Database\Seeder;

class DosenBidangKeahlianSeeder extends Seeder
{
    /**
     * Mapping nidn => list nama bidang keahlian.
     * Nama bidang keahlian harus sesuai dengan yang ada di BidangKeahlianSeeder.
     */
    public function run(): void
    {
        $mapping = [
            '0422029205' => [ // Feri Alpiyasin
                'Machine Learning',
                'Data Mining',
                'Data Science',
                'Sistem Pendukung Keputusan',
                'Sistem Informasi',
            ],
            '0429098105' => [ // Hasanah Tisna Amijaya
                'Akuntansi Keuangan',
                'Perpajakan Komputerisasi',
                'Sistem Informasi',
            ],
            '0406087303' => [ // Jajat Sudrajat
                'Sistem Informasi',
                'Manajemen',
                'Analisis Sistem',
            ],
            '0411126805' => [ // Toni Kusnandar
                'Data Science',
                'Machine Learning',
                'Deep Learning',
                'Internet Of Things',
                'Sistem Informasi',
                'Data Mining',
                'Computer Vision',
                'Image Processing',
            ],
            '0420029404' => [ // Titi Widaretna
                'Data Science',
                'Machine Learning',
                'Deep Learning',
                'Software Development',
                'Natural Language Processing',
                'Data Mining',
                'Database',
                'Big Data',
            ],
            '0409129402' => [ // Ahfi Fauka Pranata
                'Manajemen',
                'Data Science',
                'Machine Learning',
                'Deep Learning',
                'Software Development',
                'Internet Of Things',
                'Sistem Informasi',
                'Sistem Pendukung Keputusan',
                'Data Mining',
                'Database',
                'Big Data',
                'Mobile Development',
            ],
            '0408109005' => [ // Asep Ririh Riswaya
                'Machine Learning',
                'Deep Learning',
                'Software Development',
                'Cyber Security',
            ],
        ];

        foreach ($mapping as $nidn => $bidangList) {
            $dosen = Dosen::where('nidn', $nidn)->first();

            if (! $dosen) {
                continue;
            }

            $bidangIds = BidangKeahlian::whereIn('nama', $bidangList)
                ->pluck('id')
                ->toArray();

            // sync tanpa detach agar tidak menghapus relasi lain jika seeder dijalankan ulang
            $dosen->bidangKeahlians()->syncWithoutDetaching($bidangIds);
        }
    }
}