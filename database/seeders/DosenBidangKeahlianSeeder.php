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
            '0420029404' => [ // Titi Widaretna
                'Data Science',
                'Machine Learning / Deep Learning',
                'Software Development',
                'Natural Language Processing',
                'Data Mining',
                'Database / Big Data',
            ],
            '0420078308' => [ // Heri Wahyudi
                'Machine Learning / Deep Learning',
                'Internet Of Things',
                'Sistem Informasi',
            ],
            '0408109005' => [ // Asep Ririh Riswaya
                'Machine Learning / Deep Learning',
                'Software Development',
                'Cyber Security',
            ],
            '0411126805' => [ // Dr. Toni Kusnandar
                'Data Science',
                'Machine Learning / Deep Learning',
                'Internet Of Things',
                'Sistem Informasi',
                'Data Mining',
                'Computer Vision / Image Processing',
            ],
            '5257780000000000' => [ // Deni Sopiyan
                'Software Development',
                'Sistem Informasi',
                'Sistem Pendukung Keputusan',
            ],
            '0413048004' => [ // Ganjar Nurul Fajar
                'Manajemen',
                'Akuntansi Keuangan',
                'Perpajakan Komputerisasi',
            ],
            '0000000001' => [ // Ikbal Jamaludin
                'Manajemen',
                'Data Science',
                'Sistem Informasi',
            ],
            '0426068054' => [ // Susanto
                'Sistem Informasi',
                'Sistem Pendukung Keputusan',
                'Data Mining',
            ],
            '0409129401' => [ // Ahfi Fauka Pranata
                'Manajemen',
                'Data Science',
                'Machine Learning / Deep Learning',
                'Software Development',
                'Internet Of Things',
                'Sistem Informasi',
                'Sistem Pendukung Keputusan',
                'Data Mining',
                'Database / Big Data',
                'Mobile Development',
            ],
            '0428057001' => [ // Rohmat Nur Ibrahim
                'Data Science',
                'Machine Learning / Deep Learning',
                'Computer Vision / Image Processing',
            ],
            '2349780000000000' => [ // Fitri Rizqiati
                'Software Development',
                'Internet Of Things',
                'Sistem Informasi',
            ],
            '0419067804' => [ // Lilis Emalia
                'Machine Learning / Deep Learning',
                'Software Development',
                'Sistem Informasi',
                'Sistem Pendukung Keputusan',
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