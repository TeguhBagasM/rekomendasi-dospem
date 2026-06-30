<?php

namespace Database\Seeders;

use App\Models\Kriteria;
use App\Models\SubKriteria;
use Illuminate\Database\Seeder;

class KriteriaSeeder extends Seeder
{
    public function run(): void
    {
        $kriterias = [
            ['kode' => 'TOPIK', 'nama' => 'Kesesuaian Topik', 'jenis' => 'core', 'bobot' => 0.35, 'deskripsi' => 'Kesesuaian antara judul mahasiswa dan keahlian dosen'],
            ['kode' => 'HOMEBASE', 'nama' => 'Homebase Program Studi', 'jenis' => 'core', 'bobot' => 0.25, 'deskripsi' => 'Kesesuaian program studi mahasiswa dan homebase dosen'],
            ['kode' => 'KUOTA', 'nama' => 'Kuota Bimbingan', 'jenis' => 'core', 'bobot' => 0.20, 'deskripsi' => 'Sisa kuota bimbingan dosen'],
            ['kode' => 'JABATAN', 'nama' => 'Jabatan Fungsional', 'jenis' => 'secondary', 'bobot' => 0.12, 'deskripsi' => 'Peringkat jabatan fungsional dosen'],
            ['kode' => 'PENDIDIKAN', 'nama' => 'Pendidikan Terakhir', 'jenis' => 'secondary', 'bobot' => 0.08, 'deskripsi' => 'Pendidikan terakhir dosen'],
        ];

        foreach ($kriterias as $item) {
            $kriteria = Kriteria::updateOrCreate(
                ['kode' => $item['kode']],
                [
                    'nama' => $item['nama'],
                    'jenis' => $item['jenis'],
                    'bobot' => $item['bobot'],
                    'deskripsi' => $item['deskripsi'],
                    'aktif' => true,
                ]
            );

            $subKriteriaList = match ($kriteria->kode) {
                'TOPIK' => [
                    ['kode' => 'TOPIK_5', 'nama' => 'Sangat sesuai', 'nilai_min' => 0.75, 'nilai_max' => 1.00, 'nilai' => 5, 'bobot' => 5],
                    ['kode' => 'TOPIK_4', 'nama' => 'Sesuai', 'nilai_min' => 0.50, 'nilai_max' => 0.7499, 'nilai' => 4, 'bobot' => 4.5],
                    ['kode' => 'TOPIK_3', 'nama' => 'Cukup sesuai', 'nilai_min' => 0.30, 'nilai_max' => 0.4999, 'nilai' => 3, 'bobot' => 4.0],
                    ['kode' => 'TOPIK_2', 'nama' => 'Kurang sesuai', 'nilai_min' => 0.15, 'nilai_max' => 0.2999, 'nilai' => 2, 'bobot' => 3.0],
                    ['kode' => 'TOPIK_1', 'nama' => 'Tidak sesuai', 'nilai_min' => 0.00, 'nilai_max' => 0.1499, 'nilai' => 1, 'bobot' => 1.0],
                ],
                'HOMEBASE' => [
                    ['kode' => 'HOME_5', 'nama' => 'Satu program studi', 'nilai_min' => 1, 'nilai_max' => 1, 'nilai' => 5, 'bobot' => 5],
                    ['kode' => 'HOME_1', 'nama' => 'Lintas program studi', 'nilai_min' => 0, 'nilai_max' => 0, 'nilai' => 1, 'bobot' => 1],
                ],
                'KUOTA' => [
                    ['kode' => 'KUOTA_5', 'nama' => 'Kuota sangat longgar', 'nilai_min' => 5, 'nilai_max' => 999, 'nilai' => 5, 'bobot' => 5],
                    ['kode' => 'KUOTA_4', 'nama' => 'Kuota longgar', 'nilai_min' => 3, 'nilai_max' => 4, 'nilai' => 4, 'bobot' => 4],
                    ['kode' => 'KUOTA_3', 'nama' => 'Kuota tersedia', 'nilai_min' => 1, 'nilai_max' => 2, 'nilai' => 3, 'bobot' => 3],
                    ['kode' => 'KUOTA_1', 'nama' => 'Kuota penuh', 'nilai_min' => 0, 'nilai_max' => 0, 'nilai' => 1, 'bobot' => 1],
                ],
                'JABATAN' => [
                    ['kode' => 'JABATAN_5', 'nama' => 'Guru Besar', 'nilai' => 5, 'bobot' => 5],
                    ['kode' => 'JABATAN_4', 'nama' => 'Lektor Kepala', 'nilai' => 4, 'bobot' => 4.5],
                    ['kode' => 'JABATAN_3', 'nama' => 'Lektor', 'nilai' => 3, 'bobot' => 4],
                    ['kode' => 'JABATAN_2', 'nama' => 'Asisten Ahli', 'nilai' => 2, 'bobot' => 3],
                    ['kode' => 'JABATAN_1', 'nama' => 'Lainnya', 'nilai' => 1, 'bobot' => 1],
                ],
                'PENDIDIKAN' => [
                    ['kode' => 'PENDIDIKAN_5', 'nama' => 'S3', 'nilai' => 5, 'bobot' => 5],
                    ['kode' => 'PENDIDIKAN_3', 'nama' => 'S2', 'nilai' => 3, 'bobot' => 3],
                    ['kode' => 'PENDIDIKAN_1', 'nama' => 'Lainnya', 'nilai' => 1, 'bobot' => 1],
                ],
                default => [],
            };

            foreach ($subKriteriaList as $index => $subItem) {
                SubKriteria::updateOrCreate(
                    ['kriteria_id' => $kriteria->id, 'kode' => $subItem['kode']],
                    [
                        'nama' => $subItem['nama'],
                        'nilai_min' => $subItem['nilai_min'] ?? null,
                        'nilai_max' => $subItem['nilai_max'] ?? null,
                        'nilai' => $subItem['nilai'],
                        'bobot' => $subItem['bobot'],
                        'urutan' => $index + 1,
                    ]
                );
            }
        }
    }
}