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
            '0000000013' => [ // RR. Isni Puspowati H
                'Sistem Informasi',
                'Software Development',
                'Data Science',
            ],
            '0000000014' => [ // Oktavia
                'Matematika',
                'Data Science',
                'Sistem Informasi',
            ],
            '0000000015' => [ // H. Dadi Rosadi
                'Sistem Informasi',
                'Matematika',
                'Data Science',
            ],
            '0000000016' => [ // Nono Sudarsono
                'Sistem Informasi',
                'Manajemen',
                'Internet Of Things',
            ],
            '0000000017' => [ // Adi Raharjo
                'Cyber Security',
                'Internet Of Things',
                'Sistem Informasi',
            ],
            '0000000018' => [ // Jajat Sudrajat
                'Sistem Informasi',
                'Cyber Security',
                'Data Science',
            ],
            '0000000019' => [ // Asep Suherman
                'Sistem Informasi',
                'Software Development',
                'Internet Of Things',
            ],
            '0000000020' => [ // Adi Suwarno
                'Data Science',
                'Sistem Informasi',
                'Machine Learning / Deep Learning',
            ],
            '0000000021' => [ // Dheni Apriantsani Budiman
                'Machine Learning / Deep Learning',
                'Data Science',
                'Sistem Informasi',
            ],
            '0000000022' => [ // Cahyo Hermanto
                'Sistem Informasi',
                'Software Development',
                'Machine Learning / Deep Learning',
            ],
            '0000000023' => [ // Muhammad Syukri
                'Cyber Security',
                'Software Development',
                'Sistem Informasi',
            ],
            '0000000024' => [ // En Tay
                'Software Development',
                'Sistem Informasi',
                'Internet Of Things',
            ],
            '0000000025' => [ // Solihin
                'Sistem Informasi',
                'Internet Of Things',
                'Cyber Security',
            ],
            '0000000026' => [ // Resti Aulia Damayanti
                'Manajemen',
                'Sistem Informasi',
                'Akuntansi',
            ],
            '0000000027' => [ // Dr. Rini Risanti
                'Matematika',
                'Manajemen',
                'Sistem Informasi',
            ],
            '0000000028' => [ // Ikbal Jamaludin (2)
                'Sistem Informasi',
                'Software Development',
                'Data Science',
            ],
            '0000000029' => [ // Dr. Dian Kencana
                'Manajemen',
                'Akuntansi',
                'Sistem Informasi',
            ],
            '0000000030' => [ // Aisah Nur Endah Sari
                'Data Science',
                'Internet Of Things',
                'Machine Learning / Deep Learning',
            ],
            '0000000032' => [ // Haris Supriatna
                'Software Development',
                'Internet Of Things',
                'Sistem Informasi',
            ],
            '0422029205' => [ // Feri Aalpiyasin
                'Machine Learning / Deep Learning',
                'Sistem Pendukung Keputusan',
                'Data Mining',
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
