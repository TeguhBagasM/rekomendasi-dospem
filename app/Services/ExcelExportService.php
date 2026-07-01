<?php

namespace App\Services;

use App\Exports\RekomendasiDospemExport;
use App\Models\RiwayatRekomendasi;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExcelExportService
{
    public function exportLatest(): BinaryFileResponse
    {
        $riwayat = RiwayatRekomendasi::latest()->first();

        abort_unless($riwayat, 404, 'Belum ada batch rekomendasi yang bisa diekspor.');

        return $this->export($riwayat);
    }

    public function export(RiwayatRekomendasi $riwayat): BinaryFileResponse
    {
        $filename = 'rekomendasi-dospem-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(new RekomendasiDospemExport($riwayat), $filename);
    }
}
