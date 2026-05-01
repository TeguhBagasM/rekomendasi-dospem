<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\KeahlianDosen;
use Illuminate\Database\Seeder;

class KeahlianDosenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            '11010101' => [
                ['bidang_keahlian' => 'Pemrograman Web', 'deskripsi_keahlian' => 'Laravel, PHP, MySQL, pengembangan web modern', 'tipe_faktor' => 1],
                ['bidang_keahlian' => 'Basis Data', 'deskripsi_keahlian' => 'Perancangan database, normalisasi, SQL', 'tipe_faktor' => 2],
            ],
            '11010102' => [
                ['bidang_keahlian' => 'Akuntansi Komputer', 'deskripsi_keahlian' => 'Sistem informasi akuntansi, spreadsheet, laporan keuangan', 'tipe_faktor' => 1],
                ['bidang_keahlian' => 'Audit Sistem', 'deskripsi_keahlian' => 'Audit internal, kontrol sistem, evaluasi proses bisnis', 'tipe_faktor' => 2],
            ],
            '11010103' => [
                ['bidang_keahlian' => 'Analisis Sistem', 'deskripsi_keahlian' => 'Analisis kebutuhan, pemodelan proses, UML', 'tipe_faktor' => 1],
                ['bidang_keahlian' => 'Jaringan Komputer', 'deskripsi_keahlian' => 'TCP/IP, konfigurasi jaringan, administrasi jaringan', 'tipe_faktor' => 2],
            ],
            '11010104' => [
                ['bidang_keahlian' => 'Machine Learning', 'deskripsi_keahlian' => 'Data mining, klasifikasi, regresi, model prediktif', 'tipe_faktor' => 1],
                ['bidang_keahlian' => 'Deep Learning', 'deskripsi_keahlian' => 'Neural network, computer vision, NLP', 'tipe_faktor' => 2],
            ],
        ];

        foreach ($data as $nidn => $keahlianList) {
            $dosen = Dosen::where('nidn', $nidn)->first();

            if (! $dosen) {
                continue;
            }

            foreach ($keahlianList as $keahlian) {
                KeahlianDosen::updateOrCreate(
                    [
                        'dosen_id' => $dosen->id,
                        'bidang_keahlian' => $keahlian['bidang_keahlian'],
                    ],
                    [
                        'deskripsi_keahlian' => $keahlian['deskripsi_keahlian'],
                        'tipe_faktor' => $keahlian['tipe_faktor'],
                    ]
                );
            }
        }
    }
}
