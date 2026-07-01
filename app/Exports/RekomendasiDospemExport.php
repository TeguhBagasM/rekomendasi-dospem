<?php

namespace App\Exports;

use App\Models\RiwayatRekomendasi;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekomendasiDospemExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    public function __construct(
        private readonly RiwayatRekomendasi $riwayatRekomendasi
    ) {}

    public function collection(): Collection
    {
        $rekomendasi = $this->riwayatRekomendasi->loadMissing([
            'rekomendasiDospembings.pengajuan.mahasiswa.programStudi',
            'rekomendasiDospembings.rekomendasiDospem1',
            'rekomendasiDospembings.rekomendasiDospem2',
            'rekomendasiDospembings.penetapanDospembing.dospem1',
            'rekomendasiDospembings.penetapanDospembing.dospem2',
        ])->rekomendasiDospembings;

        return $rekomendasi->values()->map(function ($rekomendasi, int $index) {
            $pengajuan = $rekomendasi->pengajuan;
            $penetapan = $rekomendasi->penetapanDospembing;

            return [
                'no' => $index + 1,
                'rekomendasi_dospembing_id' => $rekomendasi->id,
                'pengajuan_id' => $pengajuan?->id,
                'nim' => $pengajuan?->mahasiswa?->nim ?? '-',
                'nama_mahasiswa' => $pengajuan?->mahasiswa?->nama ?? '-',
                'program_studi' => $pengajuan?->mahasiswa?->programStudi?->program_studi ?? '-',
                'judul_skripsi' => $pengajuan?->judul_skripsi ?? '-',
                'rekomendasi_dospem1_nama' => $rekomendasi->rekomendasiDospem1?->nama ?? '-',
                'rekomendasi_dospem1_nidn' => $rekomendasi->rekomendasiDospem1?->nidn ?? '-',
                'nilai_dospem1' => $rekomendasi->skor_dospem1 !== null
                    ? round((float) $rekomendasi->skor_dospem1, 4)
                    : '-',
                'rekomendasi_dospem2_nama' => $rekomendasi->rekomendasiDospem2?->nama ?? '-',
                'rekomendasi_dospem2_nidn' => $rekomendasi->rekomendasiDospem2?->nidn ?? '-',
                'nilai_dospem2' => $rekomendasi->skor_dospem2 !== null
                    ? round((float) $rekomendasi->skor_dospem2, 4)
                    : '-',
                'final_dospem1_nidn' => $penetapan?->dospem1?->nidn ?? $rekomendasi->rekomendasiDospem1?->nidn ?? '-',
                'final_dospem2_nidn' => $penetapan?->dospem2?->nidn ?? $rekomendasi->rekomendasiDospem2?->nidn ?? '-',
                'status_final' => $penetapan ? 'finalized' : 'draft',
                'catatan' => $penetapan?->catatan ?? '-',
                'generated_at' => $this->riwayatRekomendasi->generated_at?->format('d-m-Y H:i') ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Rekomendasi ID',
            'Pengajuan ID',
            'NIM',
            'Nama Mahasiswa',
            'Program Studi',
            'Judul Skripsi',
            'Dospem 1',
            'NIDN Dospem 1',
            'Nilai Dospem 1',
            'Dospem 2',
            'NIDN Dospem 2',
            'Nilai Dospem 2',
            'Final NIDN Dospem 1',
            'Final NIDN Dospem 2',
            'Status Final',
            'Catatan',
            'Generated At',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF2563EB'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
