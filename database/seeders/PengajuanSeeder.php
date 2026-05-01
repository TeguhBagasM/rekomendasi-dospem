<?php

namespace Database\Seeders;

use App\Models\Mahasiswa;
use App\Models\PengajuanJudul;
use App\Models\User;
use Illuminate\Database\Seeder;

class PengajuanSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil user admin/kaprodi untuk mengisi divalidasi_oleh
        $validator = User::role(['admin'])->first();

        $judulList = [
            // ── Teknik Informatika – Machine Learning / AI ──────────────────
            [
                'nim'    => '22010001',
                'judul'  => 'Implementasi Machine Learning untuk Klasifikasi Penyakit Diabetes Menggunakan Algoritma Random Forest',
                'status' => 'disetujui',
            ],
            [
                'nim'    => '22010001',
                'judul'  => 'Penerapan Deep Learning untuk Deteksi Objek pada Citra Medis Menggunakan Convolutional Neural Network',
                'status' => 'ditolak',
                'alasan' => 'Topik terlalu luas, harap dipersempit ke satu jenis penyakit.',
            ],

            // ── Teknik Informatika – Web & Sistem Informasi ──────────────────
            [
                'nim'    => '23010002',
                'judul'  => 'Pengembangan Sistem Informasi Manajemen Aset Berbasis Web Menggunakan Framework Laravel',
                'status' => 'disetujui',
            ],
            [
                'nim'    => '23010002',
                'judul'  => 'Rancang Bangun Aplikasi E-Learning Berbasis Web dengan Fitur Gamifikasi untuk Meningkatkan Motivasi Belajar',
                'status' => 'ditolak',
                'alasan' => 'Proposal belum menyertakan tinjauan pustaka yang memadai.',
            ],

            // ── Komputerisasi Akuntansi ──────────────────────────────────────
            [
                'nim'    => '22010002',
                'judul'  => 'Perancangan Sistem Informasi Akuntansi Penggajian Berbasis Web pada CV Maju Jaya',
                'status' => 'disetujui',
            ],
            [
                'nim'    => '22010002',
                'judul'  => 'Implementasi Sistem Laporan Keuangan Digital Berbasis Spreadsheet Terintegrasi untuk UMKM',
                'status' => 'disetujui',
            ],
            [
                'nim'    => '22010002',
                'judul'  => 'Audit Sistem Informasi Akuntansi pada Koperasi Simpan Pinjam Menggunakan Framework COBIT',
                'status' => 'pending',
            ],

            // ── Manajemen Informatika ────────────────────────────────────────
            [
                'nim'    => '23010001',
                'judul'  => 'Analisis dan Perancangan Sistem Informasi Inventaris Barang Menggunakan Metode UML',
                'status' => 'disetujui',
            ],
            [
                'nim'    => '23010001',
                'judul'  => 'Perancangan Jaringan Komputer Local Area Network pada Kantor Dinas Pendidikan Kabupaten X',
                'status' => 'disetujui',
            ],
            [
                'nim'    => '23010001',
                'judul'  => 'Konfigurasi dan Optimasi Jaringan TCP/IP untuk Meningkatkan Performa Bandwidth pada Instansi Pemerintah',
                'status' => 'pending',
            ],

            // ── Sistem Informasi – Data Mining / Prediksi ───────────────────
            [
                'nim'    => '23010002',
                'judul'  => 'Penerapan Data Mining dengan Metode K-Means Clustering untuk Segmentasi Pelanggan pada Toko Online',
                'status' => 'disetujui',
            ],
            [
                'nim'    => '23010002',
                'judul'  => 'Prediksi Nilai Akademik Mahasiswa Menggunakan Algoritma Regresi Linear Berbasis Machine Learning',
                'status' => 'disetujui',
            ],

            // ── Teknik Informatika – Computer Vision / NLP ──────────────────
            [
                'nim'    => '22010001',
                'judul'  => 'Implementasi Computer Vision untuk Sistem Absensi Mahasiswa Berbasis Pengenalan Wajah',
                'status' => 'disetujui',
            ],
            [
                'nim'    => '22010001',
                'judul'  => 'Pengembangan Chatbot Layanan Akademik Menggunakan Natural Language Processing dan Framework Rasa',
                'status' => 'pending',
            ],

            // ── Komputerisasi Akuntansi – Audit & Kontrol ───────────────────
            [
                'nim'    => '22010002',
                'judul'  => 'Evaluasi Pengendalian Internal Sistem Informasi Keuangan Menggunakan Metode COSO pada Perusahaan Manufaktur',
                'status' => 'disetujui',
            ],

            // ── Manajemen Informatika – Analisis Sistem ──────────────────────
            [
                'nim'    => '23010001',
                'judul'  => 'Analisis Kebutuhan dan Pemodelan Proses Bisnis Sistem Informasi Rumah Sakit Menggunakan BPMN',
                'status' => 'disetujui',
            ],
            [
                'nim'    => '23010001',
                'judul'  => 'Perancangan Basis Data Terdistribusi untuk Sistem Informasi Multi-Cabang pada Perusahaan Retail',
                'status' => 'disetujui',
            ],

            // ── Sistem Informasi – Neural Network ───────────────────────────
            [
                'nim'    => '23010002',
                'judul'  => 'Klasifikasi Sentimen Ulasan Produk Menggunakan Metode Long Short-Term Memory (LSTM)',
                'status' => 'disetujui',
            ],
            [
                'nim'    => '23010002',
                'judul'  => 'Deteksi Spam Email Menggunakan Naive Bayes dan Neural Network dengan Perbandingan Akurasi',
                'status' => 'pending',
            ],

            // ── Teknik Informatika – Pemrograman Web ─────────────────────────
            [
                'nim'    => '22010001',
                'judul'  => 'Rancang Bangun Sistem Informasi Perpustakaan Berbasis Web Menggunakan PHP Laravel dan MySQL',
                'status' => 'disetujui',
            ],
            [
                'nim'    => '22010001',
                'judul'  => 'Pengembangan REST API untuk Aplikasi Mobile Pemesanan Makanan Menggunakan Laravel Sanctum',
                'status' => 'disetujui',
            ],
        ];

        foreach ($judulList as $item) {
            $mahasiswa = Mahasiswa::where('nim', $item['nim'])->first();

            if (! $mahasiswa) {
                continue;
            }

            // Cegah duplikat judul yang sama persis
            $exists = PengajuanJudul::where('mahasiswa_id', $mahasiswa->id)
                ->where('judul_skripsi', $item['judul'])
                ->exists();

            if ($exists) {
                continue;
            }

            $data = [
                'mahasiswa_id'  => $mahasiswa->id,
                'judul_skripsi' => $item['judul'],
                'status'        => $item['status'],
                'alasan_penolakan' => $item['alasan'] ?? null,
            ];

            if (in_array($item['status'], ['disetujui', 'ditolak']) && $validator) {
                $data['divalidasi_oleh'] = $validator->id;
                $data['validated_at']    = now();
            }

            PengajuanJudul::create($data);
        }
    }
}