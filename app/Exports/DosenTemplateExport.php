<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DosenTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            [
                '1234567890',
                'Contoh Nama Dosen',
                'Teknik Informatika',
                'Lektor',
                'S2',
                '081234567890',
                8,
                8,
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'NIDN',
            'Nama Lengkap',
            'Program Studi',
            'Jabatan Fungsional',
            'Pendidikan Terakhir',
            'No HP',
            'Kuota Dospem 1',
            'Kuota Dospem 2',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}