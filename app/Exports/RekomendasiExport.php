<?php

namespace App\Exports;

use App\Models\PengajuanJudul;
use App\Services\ProfileMatchingService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekomendasiExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    public function __construct(
        private readonly ProfileMatchingService $profileMatchingService
    ) {}

    public function collection(): \Illuminate\Support\Collection
    {
        $pengajuans = PengajuanJudul::with(['mahasiswa.user'])
            ->where('status', 'approved')
            ->latest()
            ->get();

        $hasil = $this->profileMatchingService->rekomendasikanBulk($pengajuans);

        return $hasil->map(function (array $item, int $index) {
            /** @var PengajuanJudul $pengajuan */
            $pengajuan = $item['pengajuan'];
            $mahasiswa = $pengajuan->mahasiswa;

            $dospem1     = $item['dospem1']['dosen'] ?? null;
            $skorDospem1 = isset($item['dospem1']['skor'])
                ? round($item['dospem1']['skor'] * 100, 2) . '%'
                : '-';

            $dospem2     = $item['dospem2']['dosen'] ?? null;
            $skorDospem2 = isset($item['dospem2']['skor'])
                ? round($item['dospem2']['skor'] * 100, 2) . '%'
                : '-';

            return [
                'no'           => $index + 1,
                'nim'          => $mahasiswa?->nim ?? '-',
                'nama'         => $mahasiswa?->nama ?? '-',
                'judul'        => $pengajuan->judul_skripsi,
                'dospem1'      => $dospem1?->nama ?? '-',
                'skor_dospem1' => $skorDospem1,
                'dospem2'      => $dospem2?->nama ?? '-',
                'skor_dospem2' => $skorDospem2,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'NIM',
            'Nama Mahasiswa',
            'Judul Skripsi',
            'Dosen Pembimbing 1',
            'Skor Kesesuaian 1',
            'Dosen Pembimbing 2',
            'Skor Kesesuaian 2',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Header row bold + background biru
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF2563EB'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}