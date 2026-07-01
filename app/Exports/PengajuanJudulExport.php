<?php

namespace App\Exports;

use App\Models\PengajuanJudul;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PengajuanJudulExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    /**
     * @param array{search?:string,angkatan?:string,status?:string,gelombang?:int} $filters
     */
    public function __construct(private readonly array $filters = [])
    {
    }

    public function query()
    {
        return PengajuanJudul::query()
            ->with(['mahasiswa.programStudi', 'gelombang', 'validator'])
            ->when(
                $this->filters['search'] ?? null,
                fn ($q, $search) => $q->whereHas('mahasiswa', function ($mq) use ($search) {
                    $mq->where('nama', 'like', "%{$search}%")
                        ->orWhere('nim', 'like', "%{$search}%");
                })
            )
            ->when(
                $this->filters['angkatan'] ?? null,
                fn ($q, $angkatan) => $q->whereHas('mahasiswa', fn ($mq) => $mq->where('angkatan', $angkatan))
            )
            ->when($this->filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($this->filters['gelombang'] ?? null, fn ($q, $gelombangId) => $q->where('gelombang_id', $gelombangId))
            ->latest();
    }

    public function headings(): array
    {
        return [
            'NIM', 'Nama', 'Program Studi', 'Angkatan', 'Judul Skripsi',
            'Gelombang', 'Status', 'Validator', 'Tanggal Validasi',
        ];
    }

    public function map($row): array
    {
        return [
            $row->mahasiswa?->nim,
            $row->mahasiswa?->nama,
            $row->mahasiswa?->programStudi?->program_studi,
            $row->mahasiswa?->angkatan,
            $row->judul_skripsi,
            $row->gelombang?->nama,
            ucfirst($row->status),
            $row->validator?->name,
            $row->validated_at?->format('d-m-Y'),
        ];
    }
}