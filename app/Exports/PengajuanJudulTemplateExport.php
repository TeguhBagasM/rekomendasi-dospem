<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PengajuanJudulTemplateExport implements FromArray, ShouldAutoSize, WithHeadings
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
