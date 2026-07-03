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
            // Feri Alpiyasin -> klasifikasi & prediksi
            '0422029205' => [
                'topik_metode' => 'Data mining, klasifikasi, prediksi, algoritma Naive Bayes, Decision Tree, K-Nearest Neighbor (KNN), Random Forest, evaluasi model menggunakan confusion matrix dan akurasi',
                'judul_penelitian' => [
                    'Penerapan Algoritma Naive Bayes untuk Prediksi Kelulusan Mahasiswa Tepat Waktu',
                    'Klasifikasi Risiko Kredit Nasabah Menggunakan Metode Decision Tree C4.5',
                    'Implementasi Algoritma K-Nearest Neighbor untuk Prediksi Penyakit Berdasarkan Gejala Klinis',
                ],
            ],
            // Asep Ririh -> clustering & segmentasi
            '0425087101' => [
                'topik_metode' => 'Data mining, clustering, TF-IDF, Profile Matching, algoritma K-Means, Hierarchical Clustering',
                'judul_penelitian' => [
                    'Penerapan Algoritma K-Means untuk Segmentasi Pelanggan pada Perusahaan E-Commerce',
                    'Analisis Clustering untuk Identifikasi Pola Pembelian Konsumen',
                    'Implementasi Hierarchical Clustering dalam Pengelompokan Data Transaksi Retail',
                ],
            ],
            // Hasanah -> kontrol negatif, akuntansi
            '0429098105' => [
                'topik_metode' => 'Sistem informasi akuntansi, audit sistem, pengendalian internal, pelaporan keuangan berbasis komputer',
                'judul_penelitian' => [
                    'Analisis Sistem Informasi Akuntansi Penggajian Karyawan',
                    'Perancangan Sistem Audit Internal Berbasis Web',
                    'Evaluasi Pengendalian Internal Sistem Informasi Keuangan',
                ],
            ],

            // Jajat Sudrajat -> SPK, fuzzy, multi-kriteria
            '0406087303' => [
                'topik_metode' => 'Sistem pendukung keputusan, multi-criteria decision making, metode Fuzzy Logic, TOPSIS, AHP, SAW, penentuan prioritas dan perankingan alternatif',
                'judul_penelitian' => [
                    'Sistem Pendukung Keputusan Pemilihan Supplier Menggunakan Metode TOPSIS',
                    'Penerapan Metode Fuzzy Logic dalam Penentuan Kelayakan Penerima Bantuan Sosial',
                    'Implementasi Metode AHP untuk Perankingan Alternatif Lokasi Usaha',
                ],
            ],

            // Toni Kusnandar -> rekomendasi, clustering, association rule
            '0411126805' => [
                'topik_metode' => 'Pengembangan sistem berbasis web, sistem rekomendasi produk, Content-Based Filtering, Cosine Similarity, algoritma Apriori association rule, K-Means Clustering segmentasi pelanggan',
                'judul_penelitian' => [
                    'Penerapan Content-Based Filtering untuk Sistem Rekomendasi Produk pada Toko Online',
                    'Implementasi Algoritma Apriori untuk Analisis Pola Pembelian Konsumen pada E-Commerce',
                    'Penerapan K-Means Clustering untuk Segmentasi Pelanggan dalam Mendukung Strategi Pemasaran Digital',
                ],
            ],

            // Titi Widaretna -> kontrol negatif, jaringan/cyber
            '0415038501' => [
                'topik_metode' => 'Jaringan komputer, keamanan jaringan, Internet of Things, sistem terdistribusi, monitoring infrastruktur jaringan',
                'judul_penelitian' => [
                    'Analisis Keamanan Jaringan Menggunakan Metode Intrusion Detection System',
                    'Rancang Bangun Sistem Monitoring Suhu Berbasis Internet of Things',
                    'Implementasi VPN untuk Keamanan Jaringan Kantor',
                ],
            ],

            // Ahfi Fauka Pranata -> sentimen, NLP, CNN/citra
            '0408129001' => [
                'topik_metode' => 'Analisis sentimen, text mining, Support Vector Machine (SVM), Natural Language Processing, Convolutional Neural Network (CNN), klasifikasi citra, ekstraksi fitur TF-IDF',
                'judul_penelitian' => [
                    'Analisis Sentimen Ulasan Pengguna Aplikasi E-Commerce Menggunakan Algoritma Support Vector Machine',
                    'Klasifikasi Citra Digital Menggunakan Metode Convolutional Neural Network (CNN) untuk Deteksi Objek',
                    'Penerapan Natural Language Processing untuk Analisis Opini Publik di Media Sosial',
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