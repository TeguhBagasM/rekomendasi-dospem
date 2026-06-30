<?php

namespace App\Http\Controllers;

use App\Models\PengajuanJudul;
use App\Services\GelombangService;
use App\Services\PengajuanJudulService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengajuanJudulController extends Controller
{
    public function __construct(
        private readonly PengajuanJudulService $pengajuanJudulService,
        private readonly GelombangService $gelombangService
    ) {}

    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_unless($user, 403);
        abort_unless($user->isMahasiswa() || $user->isAdmin() || $user->isDosen(), 403);

        $pengajuans = PengajuanJudul::with(['mahasiswa.user', 'validator', 'gelombang'])
            ->when($user->isMahasiswa(), function ($query) use ($user) {
                $query->whereHas('mahasiswa', function ($mahasiswaQuery) use ($user) {
                    $mahasiswaQuery->where('user_id', $user->id);
                });
            })
            ->latest()
            ->get();

        return view('pengajuan.index', compact('pengajuans'));
    }

    public function create()
    {
        $this->ensureMahasiswa();

        $mahasiswa = $this->currentMahasiswa();

        if (! $mahasiswa->bolehMengajukanBaru()) {
            return redirect()->route('pengajuan.index')
                ->with('error', 'Anda masih memiliki pengajuan yang berstatus pending. Tunggu hingga divalidasi sebelum mengajukan judul baru.');
        }

        $gelombang = $this->gelombangService->getOpenGelombang();

        // Empty state akan ditangani langsung di view jika $gelombang null.
        return view('pengajuan.create', compact('gelombang'));
    }

    public function store(Request $request)
    {
        $this->ensureMahasiswa();

        $mahasiswa = $this->currentMahasiswa();

        if (! $mahasiswa->bolehMengajukanBaru()) {
            return redirect()->route('pengajuan.index')
                ->with('error', 'Anda masih memiliki pengajuan yang berstatus pending. Tunggu hingga divalidasi sebelum mengajukan judul baru.');
        }

        $gelombang = $this->gelombangService->getOpenGelombang();

        if (! $gelombang) {
            return redirect()->route('pengajuan.index')
                ->with('error', 'Pendaftaran pengajuan judul skripsi belum dibuka. Silakan tunggu Tim Program Studi membuka gelombang pengajuan.');
        }

        $validated = $request->validate([
            'judul_skripsi' => ['required', 'string', 'max:255'],
            'file_proposal' => ['required', 'file', 'mimes:pdf', 'max:2048'],
        ]);

        try {
            $this->pengajuanJudulService->create(
                $mahasiswa,
                $gelombang,
                $validated['judul_skripsi'],
                $request->file('file_proposal')
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('pengajuan.index')->with('success', 'Pengajuan judul berhasil dikirim.');
    }

    public function show(PengajuanJudul $pengajuan)
    {
        $this->ensureCanView($pengajuan);

        $pengajuan->load(['mahasiswa.user', 'validator', 'gelombang']);

        return view('pengajuan.show', compact('pengajuan'));
    }

    public function edit(PengajuanJudul $pengajuan)
    {
        $this->ensureCanManage($pengajuan);
        $this->ensurePending($pengajuan);

        return view('pengajuan.edit', compact('pengajuan'));
    }

    public function update(Request $request, PengajuanJudul $pengajuan)
    {
        $this->ensureCanManage($pengajuan);
        $this->ensurePending($pengajuan);

        $validated = $request->validate([
            'judul_skripsi' => ['required', 'string', 'max:255'],
            'file_proposal' => ['nullable', 'file', 'mimes:pdf', 'max:2048'],
        ]);

        try {
            $this->pengajuanJudulService->update(
                $pengajuan,
                $validated['judul_skripsi'],
                $request->file('file_proposal')
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('pengajuan.index')->with('success', 'Pengajuan judul berhasil diperbarui.');
    }

    public function destroy(PengajuanJudul $pengajuan)
    {
        $this->ensureCanManage($pengajuan);
        $this->ensurePending($pengajuan);

        $this->pengajuanJudulService->delete($pengajuan);

        return redirect()->route('pengajuan.index')->with('success', 'Pengajuan judul berhasil dihapus.');
    }

    public function approve(PengajuanJudul $pengajuan)
    {
        $this->ensureValidator();
        $this->ensurePending($pengajuan);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        abort_unless($user, 403);

        $this->pengajuanJudulService->approve($pengajuan, $user);

        return back()->with('success', 'Pengajuan judul berhasil disetujui.');
    }

    public function reject(Request $request, PengajuanJudul $pengajuan)
    {
        $this->ensureValidator();
        $this->ensurePending($pengajuan);

        $validated = $request->validate([
            'alasan_penolakan' => ['required', 'string', 'max:1000'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        abort_unless($user, 403);

        $this->pengajuanJudulService->reject($pengajuan, $user, $validated['alasan_penolakan']);

        return back()->with('success', 'Pengajuan judul berhasil ditolak.');
    }

    private function currentMahasiswa(): \App\Models\Mahasiswa
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        abort_unless($user, 403);

        $mahasiswa = $user->mahasiswa;
        abort_unless($mahasiswa, 403);

        return $mahasiswa;
    }

    private function ensureMahasiswa(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_unless($user && $user->isMahasiswa(), 403);
    }

    private function ensureValidator(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_unless($user && ($user->isAdmin() || $user->isDosen()), 403);
    }

    private function ensureCanView(PengajuanJudul $pengajuan): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_unless($user, 403);

        abort_unless(
            $user->isAdmin() || $user->isDosen() || ($user->isMahasiswa() && $pengajuan->mahasiswa?->user_id === $user->id),
            403
        );
    }

    private function ensureCanManage(PengajuanJudul $pengajuan): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_unless($user, 403);

        abort_unless(
            $user->isAdmin() || $user->isDosen() || ($user->isMahasiswa() && $pengajuan->mahasiswa?->user_id === $user->id),
            403
        );
    }

    private function ensurePending(PengajuanJudul $pengajuan): void
    {
        abort_unless($pengajuan->isPending(), 403);
    }
}