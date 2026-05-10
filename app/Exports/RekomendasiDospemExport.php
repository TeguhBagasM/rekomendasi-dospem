<?php

namespace App\Exports;

use App\Models\PengajuanJudul;
use App\Services\ProfileMatchingService;
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
        private readonly ProfileMatchingService $profileMatchingService
    ) {}

    public function collection(): Collection
    {
        $pengajuans = PengajuanJudul::with([
            'mahasiswa.programStudi',
            'dospem1.keahlianDosens',
            'dospem2.keahlianDosens',
        ])
            ->where('status', 'disetujui')
            ->latest()
            ->get();

        return $pengajuans->values()->map(function (PengajuanJudul $pengajuan, int $index) {
            $recommendation = $this->profileMatchingService->recommend($pengajuan);

            $scoreDospem1 = $this->scoreForDosen($recommendation['scores'], $pengajuan->dospem1_id, 'nilai_akhir_dospem1');
            $scoreDospem2 = $this->scoreForDosen($recommendation['scores'], $pengajuan->dospem2_id, 'nilai_akhir_dospem2');

            return [
                'no' => $index + 1,
                'nim' => $pengajuan->mahasiswa?->nim ?? '-',
                'nama_mahasiswa' => $pengajuan->mahasiswa?->nama ?? '-',
                'program_studi' => $pengajuan->mahasiswa?->programStudi?->program_studi ?? '-',
                'judul_skripsi' => $pengajuan->judul_skripsi,
                'dosen_pembimbing_1' => $pengajuan->dospem1?->nama ?? '-',
                'bidang_keahlian_dospem_1' => $this->getCoreSkillLabel($pengajuan->dospem1),
                'skor_kesesuaian_dospem_1' => $this->formatPercent($scoreDospem1),
                'dosen_pembimbing_2' => $pengajuan->dospem2?->nama ?? '-',
                'bidang_keahlian_dospem_2' => $this->getCoreSkillLabel($pengajuan->dospem2),
                'skor_kesesuaian_dospem_2' => $this->formatPercent($scoreDospem2),
                'tanggal_ditetapkan' => $pengajuan->validated_at?->format('d-m-Y H:i') ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'NIM',
            'Nama Mahasiswa',
            'Program Studi',
            'Judul Skripsi',
            'Dosen Pembimbing 1',
            'Bidang Keahlian Dospem 1',
            'Skor Kesesuaian Dospem 1',
            'Dosen Pembimbing 2',
            'Bidang Keahlian Dospem 2',
            'Skor Kesesuaian Dospem 2',
            'Tanggal Ditetapkan',
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

    private function scoreForDosen(array $scores, ?int $dosenId, string $key): ?float
    {
        if ($dosenId === null) {
            return null;
        }

        foreach ($scores as $score) {
            if (($score['dosen_id'] ?? null) === $dosenId) {
                return isset($score[$key]) ? (float) $score[$key] : null;
            }
        }

        return null;
    }

    private function formatPercent(?float $score): string
    {
        if ($score === null) {
            return '-';
        }

        return number_format(($score / 5) * 100, 2) . '%';
    }

    private function getCoreSkillLabel($dosen): string
    {
        if (! $dosen) {
            return '-';
        }

        return $dosen->keahlianDosens
            ->firstWhere('tipe_faktor', 1)?->bidang_keahlian
            ?? $dosen->keahlianDosens->first()?->bidang_keahlian
            ?? '-';
    }
}