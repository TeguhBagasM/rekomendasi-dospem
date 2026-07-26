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
            '0420029404' => [ // Titi Widaretna
                'topik_metode' => 'SVM, Doc2Vec, Rocchio, KNN, Classification, web development codeigniter, uml, database',
                'judul_penelitian' => [
                    'Hoax Identification on Tweets in Indonesia using Doc2Vec',
                ],
            ],
            '0420078308' => [ // Heri Wahyudi
                'topik_metode' => 'Cnn, svm',
                'judul_penelitian' => [
                    'Klasifikasi multi penyakit tanaman berbasis citra daun pepaya menggunakan convolutional neural network (cnn)',
                ],
            ],
            '0408109005' => [ // Asep Ririh Riswaya
                'topik_metode' => 'Pengembangan model random forest untuk deteksi serangan networking',
                'judul_penelitian' => [
                    'Pembenagan System-Define Networking menggunakan Machine learning dan Deep Learning',
                ],
            ],
            '0411126805' => [ // Dr. Toni Kusnandar
                'topik_metode' => '',
                'judul_penelitian' => [
                    'A Novel Method for Optimizing Color Selection using the Hadamard Product Technique',
                ],
            ],
            '5257780000000000' => [ // Deni Sopiyan
                'topik_metode' => '',
                'judul_penelitian' => [
                    'Masterplan Smart System di Desa Wisata Samida Garut',
                ],
            ],
            '0413048004' => [ // Ganjar Nurul Fajar
                'topik_metode' => '',
                'judul_penelitian' => [],
            ],
            '0000000001' => [ // Ikbal Jamaludin
                'topik_metode' => 'Erp',
                'judul_penelitian' => [],
            ],
            '0426068054' => [ // Susanto
                'topik_metode' => 'sistem informasi manajemen, umi, basis data, erd, perancangan sistem',
                'judul_penelitian' => [
                    'sistem pendukung keputusan kelayakan pemberian kredit nasabah dengan metode c4.5',
                ],
            ],
            '0409129401' => [ // Ahfi Fauka Pranata
                'topik_metode' => 'Machine Learning: random forest, decision tree, naive bayes, SVM, clustering, K-Means, LSTM, CNN, klasifikasi, prediksi, deteksi, computer vision, YOLOv8',
                'judul_penelitian' => [],
            ],
            '0428057001' => [ // Rohmat Nur Ibrahim
                'topik_metode' => 'random forest, decision tree, naive bayes, SVM, clustering, K-Means, LSTM, CNN, klasifikasi, prediksi, deteksi, computer vision, YOLOv8',
                'judul_penelitian' => [
                    'Implementasi YOLOv8 untuk Deteksi Objek Real-Time',
                    'Sistem Rekomendasi Berbasis Collaborative Filtering',
                    'Analisis Sentimen Twitter Menggunakan BERT',
                ],
            ],
            '2349780000000000' => [ // Fitri Rizqiati
                'topik_metode' => '',
                'judul_penelitian' => [
                    'SISTEM REKOMENDASI SENSOR UNTUK ALAT PAKAN IKAN BERBASIS IOT MENGGUNAKAN METODE FUZZY',
                ],
            ],
            '0419067804' => [ // Lilis Emalia
                'topik_metode' => 'Sistem Informasi Manajemen, basis data, ERD, perancangan sistem, UML, TOPSIS, ELECTRE, SAW',
                'judul_penelitian' => [
                    'Sistem Informasi Monitoring Kinerja Karyawan Berbasis Mobile Dengan Menggunakan Framework Flutter Di PT Makerindo Prima Solusi',
                ],
            ],
        ];

        foreach ($profiles as $nidn => $data) {
            $dosen = Dosen::where('nidn', $nidn)->first();

            if (! $dosen) {
                continue;
            }

            $keahlianText = $dosen->bidangKeahlians()
                ->pluck('nama')
                ->implode(', ');

            $judulText = implode('. ', $data['judul_penelitian']);

            $dokumenTfidf = implode(' ', array_filter([
                $keahlianText,
                $data['topik_metode'],
                $judulText,
            ]));

            DosenProfile::updateOrCreate(
                ['dosen_id' => $dosen->id],
                [
                    'topik_metode' => $data['topik_metode'],
                    'judul_penelitian' => $judulText,
                    'dokumen_tfidf' => $dokumenTfidf,
                ]
            );
        }
    }
}