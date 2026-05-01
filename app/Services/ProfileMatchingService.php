<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\PengajuanJudul;

class ProfileMatchingService
{
    private const CORE_WEIGHT      = 0.6;
    private const SECONDARY_WEIGHT = 0.4;

    public function __construct(
        private readonly TextSimilarityService $similarityService
    ) {}

    /**
     * Rekomendasikan dospem 1 & 2 untuk satu pengajuan judul.
     *
     * @return array{dospem1: Dosen|null, dospem2: Dosen|null, ranking: array}
     */
    public function rekomendasikan(PengajuanJudul $pengajuan): array
    {
        $judul = $pengajuan->judul_skripsi;

        $dosens = Dosen::with(['keahlianCore', 'keahlianSecondary'])
            ->get()
            ->filter(fn (Dosen $dosen) => $dosen->masihAdaKuota());

        $ranking = $dosens
            ->map(fn (Dosen $dosen) => [
                'dosen' => $dosen,
                'skor'  => $this->hitungSkor($judul, $dosen),
            ])
            ->sortByDesc('skor')
            ->values();

        return [
            'dospem1' => $ranking->get(0)?->get('dosen'),
            'dospem2' => $ranking->get(1)?->get('dosen'),
            'ranking' => $ranking->toArray(),
        ];
    }

    /**
     * Rekomendasikan untuk banyak pengajuan sekaligus (untuk export).
     *
     * @param  \Illuminate\Support\Collection<int, PengajuanJudul>  $pengajuans
     */
    public function rekomendasikanBulk(\Illuminate\Support\Collection $pengajuans): \Illuminate\Support\Collection
    {
        // Load semua dosen sekali saja agar tidak N+1
        $dosens = Dosen::with(['keahlianCore', 'keahlianSecondary'])
            ->get()
            ->filter(fn (Dosen $dosen) => $dosen->masihAdaKuota());

        return $pengajuans->map(function (PengajuanJudul $pengajuan) use ($dosens) {
            $judul = $pengajuan->judul_skripsi;

            $ranking = $dosens
                ->map(fn (Dosen $dosen) => [
                    'dosen' => $dosen,
                    'skor'  => $this->hitungSkor($judul, $dosen),
                ])
                ->sortByDesc('skor')
                ->values();

            return [
                'pengajuan' => $pengajuan,
                'dospem1'   => $ranking->get(0),
                'dospem2'   => $ranking->get(1),
            ];
        });
    }

    private function hitungSkor(string $judul, Dosen $dosen): float
    {
        $nilaiCore      = $this->hitungNilaiPerTipe($judul, $dosen->keahlianCore);
        $nilaiSecondary = $this->hitungNilaiPerTipe($judul, $dosen->keahlianSecondary);

        // Jika tidak punya secondary factor, skor = 100% core
        if ($dosen->keahlianSecondary->isEmpty()) {
            return $nilaiCore;
        }

        // Jika tidak punya core factor, skor = 100% secondary
        if ($dosen->keahlianCore->isEmpty()) {
            return $nilaiSecondary;
        }

        return (self::CORE_WEIGHT * $nilaiCore) + (self::SECONDARY_WEIGHT * $nilaiSecondary);
    }

    /**
     * Rata-rata cosine similarity judul terhadap semua keahlian dalam satu tipe.
     */
    private function hitungNilaiPerTipe(string $judul, \Illuminate\Database\Eloquent\Collection $keahlians): float
    {
        if ($keahlians->isEmpty()) {
            return 0.0;
        }

        $total = $keahlians->sum(function ($keahlian) use ($judul) {
            // Gabungkan bidang + deskripsi agar matching lebih kaya konteks
            $teks = $keahlian->bidang_keahlian . ' ' . $keahlian->deskripsi_keahlian;

            return $this->similarityService->calculateSimilarity($judul, $teks);
        });

        return $total / $keahlians->count();
    }
}