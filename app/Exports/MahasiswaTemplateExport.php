<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MahasiswaTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            [
                '2301001',
                'Contoh Nama Mahasiswa',
                1,
                2023,
                '2301001@mahasiswa.local',
                '081234567890',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'NIM',
            'Nama Lengkap',
            'Program Studi ID',
            'Angkatan',
            'Email',
            'No HP',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
