<?php

namespace App\Http\Controllers;

use App\Exports\RekomendasiDospemExport;
use App\Imports\PenetapanDospembingImport;
use App\Models\PengajuanJudul;
use App\Models\RiwayatRekomendasi;
use App\Services\ProfileMatchingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RekomendasiController extends Controller
{
    public function __construct(
        private readonly ProfileMatchingService $profileMatchingService
    ) {}

    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_unless($user && ($user->isAdmin() || $user->isDosen()), 403);

        $riwayat = RiwayatRekomendasi::with([
            'rekomendasiDospembings.pengajuan.mahasiswa.programStudi',
            'rekomendasiDospembings.rekomendasiDospem1',
            'rekomendasiDospembings.rekomendasiDospem2',
            'rekomendasiDospembings.penetapanDospembing.dospem1',
            'rekomendasiDospembings.penetapanDospembing.dospem2',
        ])->latest()->first();

        $pengajuans = PengajuanJudul::with(['mahasiswa.programStudi', 'penetapanDospembing.dospem1', 'penetapanDospembing.dospem2'])
            ->where('status', 'disetujui')
            ->latest()
            ->get();

        $recommendations = $riwayat?->rekomendasiDospembings?->keyBy('pengajuan_id') ?? collect();

        return view('rekomendasi.index', compact('pengajuans', 'recommendations', 'riwayat'));
    }

    public function generate(): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_unless($user && ($user->isAdmin() || $user->isDosen()), 403);
        $pengajuans = PengajuanJudul::with(['mahasiswa.programStudi'])
            ->where('status', 'disetujui')
            ->latest()
            ->get();

        if ($pengajuans->isEmpty()) {
            return back()->with('error', 'Tidak ada pengajuan disetujui untuk dihitung.');
        }

        $this->profileMatchingService->generateRecommendationBatch($pengajuans);

        return back()->with('success', 'Rekomendasi dospem berhasil dihitung untuk seluruh pengajuan disetujui.');
    }

    public function exportExcel(): BinaryFileResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_unless($user && ($user->isAdmin() || $user->isDosen()), 403);

        $riwayat = RiwayatRekomendasi::latest()->first();

        abort_unless($riwayat, 404, 'Belum ada batch rekomendasi yang bisa diekspor.');

        $filename = 'rekomendasi-dospem-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(
            new RekomendasiDospemExport($riwayat),
            $filename
        );
    }

    public function importExcel(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_unless($user && ($user->isAdmin() || $user->isDosen()), 403);

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        try {
            Excel::import(new PenetapanDospembingImport(), $validated['file']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        return back()->with('success', 'File rekomendasi berhasil diimport, kuota dosen diperbarui, dan status telah difinalisasi.');
    }

    public function export(): BinaryFileResponse
    {
        return $this->exportExcel();
    }
}