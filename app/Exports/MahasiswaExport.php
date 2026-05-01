<?php

namespace App\Exports;

use App\Models\Mahasiswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MahasiswaExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Mahasiswa::with(['user', 'programStudi'])
            ->orderBy('nama')
            ->get()
            ->map(function (Mahasiswa $mahasiswa) {
                return [
                    'nim' => $mahasiswa->nim,
                    'nama' => $mahasiswa->nama,
                    'program_studi_id' => $mahasiswa->program_studi_id,
                    'program_studi_nama' => $mahasiswa->programStudi?->program_studi,
                    'angkatan' => $mahasiswa->angkatan,
                    'email' => $mahasiswa->user?->email,
                    'no_hp' => $mahasiswa->no_hp,
                ];
            });
    }

    public function headings(): array
    {
        return ['nim', 'nama', 'program_studi_id', 'program_studi_nama', 'angkatan', 'email', 'no_hp'];
    }
}