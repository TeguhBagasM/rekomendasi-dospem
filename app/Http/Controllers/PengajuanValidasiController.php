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
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:pending,disetujui,ditolak,all'],
        ]);

        $search = $filters['search'] ?? null;
        $status = $filters['status'] ?? 'pending';

        $pengajuans = PengajuanJudul::query()
            ->with(['mahasiswa.user'])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('judul_skripsi', 'like', "%{$search}%")
                        ->orWhereHas('mahasiswa', function ($mahasiswaQuery) use ($search) {
                            $mahasiswaQuery->where(fn ($q2) => $q2
                                ->where('nama', 'like', "%{$search}%")
                                ->orWhere('nim', 'like', "%{$search}%"));
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $selectedId = (int) $request->query('id', $pengajuans->first()?->id ?? 0);

        $selectedPengajuan = $pengajuans->getCollection()->firstWhere('id', $selectedId)
            ?? PengajuanJudul::with(['mahasiswa.user'])->find($selectedId)
            ?? $pengajuans->first();

        $totalPending = PengajuanJudul::pending()->count();

        return view('admin.validasi-pengajuan.index', compact(
            'pengajuans',
            'selectedPengajuan',
            'selectedId',
            'search',
            'status',
            'totalPending'
        ));
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
