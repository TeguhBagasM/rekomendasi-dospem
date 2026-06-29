<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\DosenProfile;
use Illuminate\Database\Seeder;

class DosenProfileSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = [
            '11010101' => [
                'topik_metode' =>
                    'laravel, php, mysql, rest api, web service, sistem informasi, basis data',

                'judul_penelitian' =>
                    'Pengembangan Sistem Informasi Akademik Berbasis Web, Implementasi REST API Pada Sistem Informasi Kampus',
            ],

            '11010102' => [
                'topik_metode' =>
                    'sistem informasi akuntansi, spreadsheet, laporan keuangan, audit sistem',

                'judul_penelitian' =>
                    'Rancang Bangun Sistem Informasi Keuangan Sekolah, Analisis Sistem Informasi Akuntansi',
            ],

            '11010103' => [
                'topik_metode' =>
                    'uml, analisis kebutuhan, jaringan komputer, tcp ip, rekayasa perangkat lunak',

                'judul_penelitian' =>
                    'Analisis Kebutuhan Sistem Akademik, Implementasi Jaringan Komputer Pada Lingkungan Kampus',
            ],

            '11010104' => [
                'topik_metode' =>
                    'machine learning, data mining, random forest, naive bayes, cnn, yolo, computer vision',

                'judul_penelitian' =>
                    'Implementasi YOLO Untuk Deteksi Objek, Prediksi Menggunakan Random Forest, Analisis Sentimen Menggunakan Machine Learning',
            ],
        ];

        foreach ($profiles as $nidn => $profile) {
            $dosen = Dosen::where('nidn', $nidn)->first();

            if (!$dosen) {
                continue;
            }

            DosenProfile::updateOrCreate(
                [
                    'dosen_id' => $dosen->id,
                ],
                [
                    'topik_metode' => $profile['topik_metode'],
                    'judul_penelitian' => $profile['judul_penelitian'],
                    'dokumen_tfidf' =>
                        $profile['topik_metode'] . ' ' .
                        $profile['judul_penelitian'],
                ]
            );
        }
    }
}