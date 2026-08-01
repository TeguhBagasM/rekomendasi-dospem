<?php

namespace App\Services;

use App\Imports\PenetapanDospembingImport;
use App\Models\RiwayatRekomendasi;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Satu-satunya service yang boleh dipanggil oleh Controller.
 *
 * Business logic perhitungan & assignment dipisah ke:
 * - RecommendationGeneratorService (orkestrasi seluruh tahap 1-13)
 * - RecommendationAssignmentService (khusus tahap 9-12: seleksi + validasi kuota)
 */
class RecommendationService
{
    public function __construct(
        private readonly RecommendationGeneratorService $generatorService,
        private readonly ExcelExportService $excelExportService,
        private readonly PenetapanDospembingImportService $penetapanDospembingImportService,
    ) {}

    /**
     * Generate rekomendasi untuk sekumpulan pengajuan sekaligus (satu batch/riwayat).
     * Kuota dosen dikelola sebagai state sementara sepanjang batch ini,
     * sehingga tidak ada dosen yang melebihi kuota dalam satu kali generate.
     */
    public function generateBatch(Collection $pengajuans): Collection
    {
        $generatedBy = Auth::id();

        return DB::transaction(function () use ($pengajuans, $generatedBy): Collection {
            $riwayat = RiwayatRekomendasi::create([
                'generated_by' => $generatedBy,
                'generated_at' => now(),
                'status' => 'draft',
                'keterangan' => 'Batch rekomendasi dospem generated oleh admin.',
            ]);

            return $this->generatorService->generateForBatch($pengajuans, $riwayat);
        });
    }

    public function exportLatestToExcel(): BinaryFileResponse
    {
        return $this->excelExportService->exportLatest();
    }

    public function finalizeFromImport(UploadedFile $file): void
    {
        Excel::import(new PenetapanDospembingImport($this->penetapanDospembingImportService), $file);
    }

    /**
     * Finalisasi draft riwayat dari halaman tinjau & edit: sinkronkan seluruh
     * rekomendasi draft ke penetapan_dospembings, lalu kuota dosen dihitung
     * terpakai secara resmi dan status riwayat menjadi 'finalized'.
     */
    public function finalizeDraft(RiwayatRekomendasi $riwayat): void
    {
        $this->penetapanDospembingImportService->finalizeDraft($riwayat);
    }

    /**
     * Ekspor draft satu riwayat rekomendasi tertentu ke Excel.
     */
    public function exportRiwayat(RiwayatRekomendasi $riwayat): BinaryFileResponse
    {
        return $this->excelExportService->export($riwayat);
    }
}
