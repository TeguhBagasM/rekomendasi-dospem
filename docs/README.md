# Dokumentasi Teknis — Sistem Rekomendasi Dosen Pembimbing (SPK)

Dokumentasi ini menjelaskan arsitektur dan alur kerja aplikasi **Sistem Pendukung Keputusan (SPK) Rekomendasi Dosen Pembimbing** berbasis **Laravel 12**.

Aplikasi ini merekomendasikan Dosen Pembimbing 1 (Dospem1) dan Dosen Pembimbing 2 (Dospem2) untuk judul skripsi yang diajukan mahasiswa, menggunakan kombinasi tiga metode:

1. **TF-IDF (Term Frequency – Inverse Document Frequency)** untuk mengubah teks menjadi vektor angka.
2. **Cosine Similarity** untuk mengukur kemiripan antara judul mahasiswa dan dokumen keahlian dosen.
3. **Profile Matching** untuk memberi skor akhir dan merangking kandidat dosen berdasarkan kriteria (topik, pendidikan, jabatan, kuota).

---

## Struktur Dokumentasi

| File | Isi |
|---|---|
| [FLOW.md](FLOW.md) | Alur lengkap dari request sampai hasil rekomendasi (text + Mermaid). |
| [SERVICES.md](SERVICES.md) | Penjelasan detail semua Service (tujuan, dipanggil oleh, dependency, method). |
| [CONTROLLERS.md](CONTROLLERS.md) | Penjelasan semua Controller (endpoint, method, service yang dipanggil). |
| [MODELS.md](MODELS.md) | Penjelasan relasi antar Model Eloquent. |
| [ALGORITHM.md](ALGORITHM.md) | Penjelasan rinci TF-IDF, Cosine Similarity, dan Profile Matching. |
| [DATABASE.md](DATABASE.md) | Alur penggunaan tabel-tabel database. |

---

## Arsitektur Umum

Aplikasi mengikuti pola standar Laravel dengan satu aturan penting: **logika bisnis diletakkan di folder `app/Services/`**, bukan di Controller. Controller hanya bertugas:

1. Menerima HTTP request.
2. Validasi input.
3. Memanggil Service.
4. Mengembalikan response (view / redirect / file).

### Lapisan arsitektur

```
Request (HTTP) → Routes (routes/web.php) → Middleware (auth + role) → Controller
    → Service (logic bisnis) → Model (Eloquent ORM) → Database (MySQL/SQLite)
    → View (Blade + Tailwind)
```

### Daftar folder penting

| Path | Isi |
|---|---|
| `app/Http/Controllers/` | Controller (tipis, memanggil Service). |
| `app/Services/` | Seluruh logika bisnis (17 service). |
| `app/Models/` | Model Eloquent. |
| `app/Imports/` | Kelas import Excel (Maatwebsite/Excel). |
| `app/Exports/` | Kelas export Excel. |
| `app/Http/Requests/` | Form Request validasi. |
| `routes/web.php` | Semua definisi route. |
| `database/migrations/` | Skema database. |
| `database/seeders/` | Data awal (seeder). |
| `resources/views/` | Template Blade + Tailwind. |
| `tests/` | Test Pest (unit + fitur). |

---

## Alur Sistem Secara Ringkas

```
1. Admin menyiapkan master data
   - Program Studi, Bidang Keahlian, Dosen (+ profile penelitian), Mahasiswa, Gelombang, Kuota
2. Mahasiswa mengajukan judul skripsi (dalam periode gelombang terbuka)
3. Admin memvalidasi pengajuan (approve / reject)  [PengajuanValidasiController]
4. Admin klik "Generate Rekomendasi"  [RekomendasiController]
   → TF-IDF → Cosine Similarity → Profile Matching → Ranking → Assignment Dospem1 & Dospem2
5. Hasil tersimpan sebagai draft  [RiwayatRekomendasi + RekomendasiDospembing + HasilPerhitungan]
6. Admin meninjau / meng-edit draft  [halaman review]
7. Admin finalisasi (manual) ATAU export-edit-import Excel
   → status draft menjadi finalized, kuota dosen terpakai di-update resmi  [PenetapanDospembing]
8. Mahasiswa & Dosen melihat hasil penetapan pada dashboard masing-masing
```

> **Catatan penting:** kuota dosen (`kuota_terpakai_dospem1/2`) **baru di-update ke database** saat admin melakukan **finalisasi/penetapan** (tahap 7). Saat generate rekomendasi (tahap 4-5), kuota hanya dihitung sebagai state sementara di dalam memori agar tidak ada dosen yang melebihi kuota dalam satu batch.

---

## Struktur Pipeline Rekomendasi (inti sistem)

Seluruh pipeline dikelola oleh `RecommendationGeneratorService` dan dijalankan ketika admin klik **Generate Rekomendasi**.

```
PengajuanJudul (judul_skripsi mahasiswa yang disetujui)
        │
        ▼
DocumentSimilarityService::compareOneToMany(judul, [dokumen_tfidf setiap dosen])
        │  → TfidfService::buildMatrix (tokenize → TF → IDF → TF-IDF)
        │  → CosineSimilarityService::calculate (vektor judul vs vektor dosen)
        ▼
sim = [skor kemiripan judul vs dosen 1, dosen 2, ...]
        │
        ▼
ProfileMatchingService::buildScoreRow(pengajuan, dosen, sim, kuotaGelombang)
        │  → konversi similarity & atribut dosen → skala 1-5
        │  → GAP → bobot GAP → Core Factor (60%) + Secondary Factor (40%)
        ▼
Nilai akhir (final_score) untuk setiap dosen, untuk peran dospem1 & dospem2
        │
        ▼
Sort DESC → Ranking
        │
        ▼
RecommendationAssignmentService::assign(...)
        │  → pickDospem1 (wajib Teknik Informatika, kuota tersedia)
        │  → pickDospem2 (bukan dosen yg sama, kuota, jabatan dospem1 ≥ dospem2,
        │                 prioritas dosen non-TI, lalu fallback dosen TI)
        ▼
RekomendasiDospembing (dospem1 + dospem2 + skor) + HasilPerhitungan (ranking semua kandidat)
        │
        ▼
RiwayatRekomendasi (status 'draft')
```

Detail lengkap setiap tahap ada di [ALGORITHM.md](ALGORITHM.md) dan [FLOW.md](FLOW.md).

---

## Role Pengguna

Tiga role dari Spatie Laravel Permission:

| Role | Kemampuan utama |
|---|---|
| `admin` | Semua menu: master data, validasi, generate rekomendasi, review/finalisasi, import/export. |
| `dosen` | Dashboard bimbingan, meng-edit profil penelitian sendiri (topik_metode, judul_penelitian, bidang keahlian). |
| `mahasiswa` | Mengajukan judul skripsi, melihat status pengajuan & dosen pembimbing yang ditetapkan. |

Login default hasil seeder: `admin@gmail.com` / `111`.

---

## Command yang Berguna

```bash
composer dev                                   # Jalankan semua: server, queue, pail, vite
php artisan migrate:fresh --seed               # Reset database + data awal
php artisan test                               # Jalankan seluruh test Pest
vendor/bin/pest --filter="nama test"           # Jalankan satu test
vendor/bin/pint                                # Perbaiki format kode (Laravel Pint)
```

---

## Kunci Memahami Kode

- **Jangan cari logika di Controller** — Controller hanya delegasi ke Service.
- **`RecommendationService`** adalah satu-satunya Service yang boleh dipanggil Controller untuk urusan rekomendasi; ia memanggil `RecommendationGeneratorService` (perhitungan) dan `PenetapanDospembingImportService` (finalisasi).
- **`ProfileMatchingService`** adalah kalkulator murni (pure): tidak menyentuh database, hanya menerima data dan mengembalikan array skor.
- **`DosenProfile.dokumen_tfidf`** adalah dokumen teks mentah per dosen (gabungan bidang keahlian + topik_metode + judul_penelitian). Korpus dan IDF **dibangun ulang dari nol setiap kali generate** — tidak ada langkah re-index manual.
- Tabel `kriterias` / `sub_kriterias` dan model `KeahlianDosen` **tidak dipakai** oleh pipeline (kode legacy). Bobot Profile Matching di-hardcode di `ProfileMatchingService`.
