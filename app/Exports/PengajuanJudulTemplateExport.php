<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PengajuanJudulTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function array(): array
    {
        return [
            ['22110397', 'Teguh Bagas', 'Teknik Informatika', 'Contoh Judul Skripsi'],
        ];
    }

    public function headings(): array
    {
        return ['NIM', 'Nama Mahasiswa', 'Program Studi', 'Judul Skripsi'];
    }
}