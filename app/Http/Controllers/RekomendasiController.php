<?php

namespace App\Http\Controllers;

use App\Exports\RekomendasiDospemExport;
use App\Models\PengajuanJudul;
use App\Services\ProfileMatchingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
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

        abort_unless($user && ($user->isAdmin() || $user->isKaprodi()), 403);

        $pengajuans = PengajuanJudul::with(['mahasiswa.programStudi', 'dospem1', 'dospem2'])
            ->where('status', 'disetujui')
            ->latest()
            ->get();

        $recommendations = $pengajuans->mapWithKeys(function (PengajuanJudul $pengajuan) {
            return [$pengajuan->id => $this->profileMatchingService->recommend($pengajuan)];
        });

        return view('rekomendasi.index', compact('pengajuans', 'recommendations'));
    }

    public function generate(PengajuanJudul $pengajuan): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_unless($user && ($user->isAdmin() || $user->isKaprodi()), 403);
        abort_unless($pengajuan->status === 'disetujui', 403);

        $recommendation = $this->profileMatchingService->recommend($pengajuan);

        $pengajuan->update([
            'dospem1_id' => $recommendation['dospem1']?->id,
            'dospem2_id' => $recommendation['dospem2']?->id,
        ]);

        return back()->with('success', 'Rekomendasi dospem berhasil dibuat.');
    }

    public function exportExcel(): BinaryFileResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_unless($user && ($user->isAdmin() || $user->isKaprodi()), 403);

        $approvedPengajuans = PengajuanJudul::where('status', 'disetujui')
            ->where(function ($query) {
                $query->whereNull('dospem1_id')
                    ->orWhereNull('dospem2_id');
            })
            ->latest()
            ->get();

        foreach ($approvedPengajuans as $pengajuan) {
            $recommendation = $this->profileMatchingService->recommend($pengajuan);

            $pengajuan->update([
                'dospem1_id' => $recommendation['dospem1']?->id,
                'dospem2_id' => $recommendation['dospem2']?->id,
            ]);
        }

        $filename = 'rekomendasi-dospem-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(
            new RekomendasiDospemExport($this->profileMatchingService),
            $filename
        );
    }

    public function export(): BinaryFileResponse
    {
        return $this->exportExcel();
    }
}