<?php

namespace App\Exports;

use App\Models\Dosen;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DosenExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Dosen::with('programStudi')->orderBy('nama')->get();
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

    public function map($dosen): array
    {
        return [
            $dosen->nidn,
            $dosen->nama,
            $dosen->programStudi?->program_studi ?? '',
            $dosen->jabatan_fungsional ?? '',
            $dosen->pendidikan_terakhir ?? '',
            $dosen->no_hp ?? '',
            $dosen->kuota_dospem1,
            $dosen->kuota_dospem2,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}