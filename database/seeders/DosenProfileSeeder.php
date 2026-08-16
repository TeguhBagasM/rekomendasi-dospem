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
                'topik_metode' => 'Color Selection, Image Processing, IoT, Unmanned Aerial Vehicle, Machine Learning ',
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
                'topik_metode' => 'sistem informasi manajemen, uml, basis data, erd, perancangan sistem',
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
            '0000000013' => [ // RR. Isni Puspowati H
                'topik_metode' => '',
                'judul_penelitian' => [],
            ],
            '0000000014' => [ // Oktavia
                'topik_metode' => '',
                'judul_penelitian' => [],
            ],
            '0000000015' => [ // H. Dadi Rosadi
                'topik_metode' => '',
                'judul_penelitian' => [],
            ],
            '0000000016' => [ // Nono Sudarsono
                'topik_metode' => '',
                'judul_penelitian' => [],
            ],
            '0000000017' => [ // Adi Raharjo
                'topik_metode' => '',
                'judul_penelitian' => [],
            ],
            '0000000018' => [ // Jajat Sudrajat
                'topik_metode' => '',
                'judul_penelitian' => [],
            ],
            '0000000019' => [ // Asep Suherman
                'topik_metode' => '',
                'judul_penelitian' => [
                    'SmartHydro AI: Pengembangan Sistem Hidroponik Cerdas Berbasis IoT dan Kecerdasan Buatan',
                ],
            ],
            '0000000020' => [ // Adi Suwarno
                'topik_metode' => '',
                'judul_penelitian' => [],
            ],
            '0000000021' => [ // Dheni Apriantsani Budiman
                'topik_metode' => '',
                'judul_penelitian' => [],
            ],
            '0000000022' => [ // Cahyo Hermanto
                'topik_metode' => '',
                'judul_penelitian' => [],
            ],
            '0000000023' => [ // Muhammad Syukri
                'topik_metode' => '',
                'judul_penelitian' => [
                    'Analisis Forensik Keamanan Data Pribadi pada Mode Privasi Browser Menggunakan Metode National Institute of Standards and Technology (NIST)',
                ],
            ],
            '0000000024' => [ // En Tay
                'topik_metode' => '',
                'judul_penelitian' => [],
            ],
            '0000000025' => [ // Solihin
                'topik_metode' => '',
                'judul_penelitian' => [],
            ],
            '0000000026' => [ // Resti Aulia Damayanti
                'topik_metode' => '',
                'judul_penelitian' => [],
            ],
            '0000000027' => [ // Dr. Rini Risanti
                'topik_metode' => '',
                'judul_penelitian' => [
                    'How Iot Through Artificial Intelegence Improving Economic Value Added On Coffee Plantation',
                ],
            ],
            '0000000028' => [ // Ikbal Jamaludin (2)
                'topik_metode' => '',
                'judul_penelitian' => [
                    'Model perspektif pembelajaran adaptif basis deep knowledge tracing untuk memetakan evolusi pemahaman kognitif siswa pada platform SPI',
                ],
            ],
            '0000000029' => [ // Dr. Dian Kencana
                'topik_metode' => '',
                'judul_penelitian' => [],
            ],
            '0000000030' => [ // Aisah Nur Endah Sari
                'topik_metode' => '',
                'judul_penelitian' => [],
            ],
            '0000000031' => [ // Rizal Parghani
                'topik_metode' => '',
                'judul_penelitian' => [],
            ],
            '0000000032' => [ // Haris Supriatna
                'topik_metode' => '',
                'judul_penelitian' => [
                    'SISTEM REKOMENDASI SENSOR UNTUK ALAT PAKAN IKAN BERBASIS IOT MENGGUNAKAN METODE FUZZY',
                ],
            ],
            '0422029205' => [ // Feri Aalpiyasin
                'topik_metode' => 'CNN, ResNet V2, EfficientNet, Naive Bayes, SVM, K-Means, Decision Tree, Fuzzy Mamdani',
                'judul_penelitian' => [],
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
