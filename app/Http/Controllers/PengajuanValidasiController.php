<?php

namespace App\Http\Controllers;

use App\Models\PengajuanJudul;
use App\Models\User;
use App\Services\PengajuanJudulService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengajuanValidasiController extends Controller
{
    public function __construct(
        private readonly PengajuanJudulService $pengajuanJudulService
    ) {}

    public function index(Request $request)
    {
        $pengajuans = PengajuanJudul::with(['mahasiswa.user'])
            ->pending()
            ->latest()
            ->get();

        $selectedId = $request->query('id', $pengajuans->first()?->id);
        $selectedPengajuan = $pengajuans->firstWhere('id', (int) $selectedId) ?? $pengajuans->first();

        return view('admin.validasi-pengajuan.index', compact('pengajuans', 'selectedPengajuan', 'selectedId'));
    }

    public function approve(PengajuanJudul $pengajuan)
    {
        /** @var User|null $user */
        $user = Auth::user();
        abort_unless($user && $user->hasRole('admin'), 403);
        abort_unless($pengajuan->isPending(), 403);

        $this->pengajuanJudulService->approve($pengajuan, $user);

        return redirect()->route('admin.validasi-pengajuan.index')
            ->with('success', 'Pengajuan berhasil disetujui.');
    }

    /**
     * Setujui seluruh pengajuan yang berstatus pending sekaligus.
     */
    public function approveAll(): RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        abort_unless($user && $user->hasRole('admin'), 403);

        $pengajuans = PengajuanJudul::pending()->get();

        if ($pengajuans->isEmpty()) {
            return redirect()->route('admin.validasi-pengajuan.index')
                ->with('error', 'Tidak ada pengajuan pending untuk disetujui.');
        }

        $approvedCount = $this->pengajuanJudulService->approveAll($pengajuans, $user);

        return redirect()->route('admin.validasi-pengajuan.index')
            ->with('success', "{$approvedCount} pengajuan berhasil disetujui.");
    }

    public function reject(Request $request, PengajuanJudul $pengajuan)
    {
        /** @var User|null $user */
        $user = Auth::user();
        abort_unless($user && $user->hasRole('admin'), 403);
        abort_unless($pengajuan->isPending(), 403);

        $validated = $request->validate([
            'alasan_penolakan' => ['required', 'string', 'max:1000'],
        ]);

        $this->pengajuanJudulService->reject($pengajuan, $user, $validated['alasan_penolakan']);

        return redirect()->route('admin.validasi-pengajuan.index')
            ->with('success', 'Pengajuan berhasil ditolak.');
    }
}