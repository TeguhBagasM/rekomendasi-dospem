<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PengajuanJudulExport;
use App\Exports\PengajuanJudulTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportPengajuanJudulRequest;
use App\Models\Gelombang;
use App\Models\Mahasiswa;
use App\Models\PengajuanJudul;
use App\Services\PengajuanJudulImportService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class PengajuanJudulManajemenController extends Controller
{
    public function __construct(
        private readonly PengajuanJudulImportService $pengajuanJudulImportService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'angkatan' => ['nullable', 'digits:4'],
            'status' => ['nullable', 'in:pending,disetujui,ditolak'],
            'gelombang' => ['nullable', 'integer', 'exists:gelombangs,id'],
        ]);

        $pengajuans = PengajuanJudul::query()
            ->with(['mahasiswa.programStudi', 'validator', 'gelombang'])
            ->when(
                ($filters['search'] ?? null) || ($filters['angkatan'] ?? null),
                function ($query) use ($filters) {
                    $query->whereHas('mahasiswa', function ($mahasiswaQuery) use ($filters) {
                        $mahasiswaQuery
                            ->when($filters['search'] ?? null, function ($q, $search) {
                                $q->where(function ($q2) use ($search) {
                                    $q2->where('nama', 'like', "%{$search}%")
                                        ->orWhere('nim', 'like', "%{$search}%");
                                });
                            })
                            ->when($filters['angkatan'] ?? null, fn ($q, $angkatan) => $q->where('angkatan', $angkatan));
                    });
                }
            )
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['gelombang'] ?? null, fn ($q, $gelombangId) => $q->where('gelombang_id', $gelombangId))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $gelombangs = Gelombang::orderByDesc('tanggal_mulai')->get(['id', 'nama', 'periode_akademik']);

        $angkatans = Mahasiswa::query()
            ->select('angkatan')
            ->distinct()
            ->orderByDesc('angkatan')
            ->pluck('angkatan');

        return view('admin.pengajuan-judul.index', compact('pengajuans', 'filters', 'gelombangs', 'angkatans'));
    }

    public function downloadTemplate()
    {
        return Excel::download(new PengajuanJudulTemplateExport, 'template_pengajuan_judul.xlsx');
    }

    public function export(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'angkatan' => ['nullable', 'digits:4'],
            'status' => ['nullable', 'in:pending,disetujui,ditolak'],
            'gelombang' => ['nullable', 'integer', 'exists:gelombangs,id'],
        ]);

        return Excel::download(new PengajuanJudulExport($filters), 'data_pengajuan_judul.xlsx');
    }

    public function import(ImportPengajuanJudulRequest $request)
    {
        try {
            $import = $this->pengajuanJudulImportService->import($request->file('file'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        $summary = sprintf(
            "Import selesai.\n- %d data baru\n- %d data diperbarui\n- %d data direset menjadi Pending\n- %d data dilewati karena sudah Disetujui",
            $import->submissionsCreated,
            $import->submissionsUpdated,
            $import->submissionsResetPending,
            $import->submissionsSkippedApproved
        );

        return redirect()->route('admin.pengajuan-judul.index')
            ->with('success', $summary)
            ->with('import_summary', [
                'mahasiswa_created' => $import->mahasiswaCreated,
                'mahasiswa_updated' => $import->mahasiswaUpdated,
                'submissions_created' => $import->submissionsCreated,
                'submissions_updated' => $import->submissionsUpdated,
                'submissions_reset_pending' => $import->submissionsResetPending,
                'submissions_skipped_approved' => $import->submissionsSkippedApproved,
            ])
            ->with('import_errors', $import->errors);
    }
}
