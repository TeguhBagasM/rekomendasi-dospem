<?php

namespace App\Http\Controllers;

use App\Models\PengajuanJudul;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PengajuanJudulController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_unless($user, 403);

        abort_unless($user->isMahasiswa() || $user->isAdmin() || $user->isKaprodi(), 403);

        $pengajuans = PengajuanJudul::with(['mahasiswa.user', 'validator'])
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

        return view('pengajuan.create');
    }

    public function store(Request $request)
    {
        $this->ensureMahasiswa();

        $validated = $request->validate([
            'judul_skripsi' => ['required', 'string', 'max:255'],
            'file_proposal' => ['nullable', 'file', 'mimes:pdf', 'max:2048'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        abort_unless($user, 403);

        $mahasiswa = $user->mahasiswa;
        abort_unless($mahasiswa, 403);

        $filePath = null;

        if ($request->hasFile('file_proposal')) {
            $filePath = $request->file('file_proposal')->store('proposal', 'public');
        }

        PengajuanJudul::create([
            'mahasiswa_id' => $mahasiswa->id,
            'judul_skripsi' => $validated['judul_skripsi'],
            'file_proposal' => $filePath,
            'status' => 'pending',
        ]);

        return redirect()->route('pengajuan.index')->with('success', 'Pengajuan judul berhasil dikirim.');
    }

    public function show(PengajuanJudul $pengajuan)
    {
        $this->ensureCanView($pengajuan);

        $pengajuan->load(['mahasiswa.user', 'validator']);

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

        $data = [
            'judul_skripsi' => $validated['judul_skripsi'],
        ];

        if ($request->hasFile('file_proposal')) {
            if ($pengajuan->file_proposal) {
                Storage::disk('public')->delete($pengajuan->file_proposal);
            }

            $data['file_proposal'] = $request->file('file_proposal')->store('proposal', 'public');
        }

        $pengajuan->update($data);

        return redirect()->route('pengajuan.index')->with('success', 'Pengajuan judul berhasil diperbarui.');
    }

    public function destroy(PengajuanJudul $pengajuan)
    {
        $this->ensureCanManage($pengajuan);
        $this->ensurePending($pengajuan);

        if ($pengajuan->file_proposal) {
            Storage::disk('public')->delete($pengajuan->file_proposal);
        }

        $pengajuan->delete();

        return redirect()->route('pengajuan.index')->with('success', 'Pengajuan judul berhasil dihapus.');
    }

    public function approve(PengajuanJudul $pengajuan)
    {
        $this->ensureValidator();
        $this->ensurePending($pengajuan);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        abort_unless($user, 403);

        $pengajuan->approve($user);

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

        $pengajuan->reject($user, $validated['alasan_penolakan']);

        return back()->with('success', 'Pengajuan judul berhasil ditolak.');
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

        abort_unless($user && ($user->isAdmin() || $user->isKaprodi()), 403);
    }

    private function ensureCanView(PengajuanJudul $pengajuan): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_unless($user, 403);

        abort_unless(
            $user->isAdmin() || $user->isKaprodi() || ($user->isMahasiswa() && $pengajuan->mahasiswa?->user_id === $user->id),
            403
        );
    }

    private function ensureCanManage(PengajuanJudul $pengajuan): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_unless($user, 403);

        abort_unless(
            $user->isAdmin() || $user->isKaprodi() || ($user->isMahasiswa() && $pengajuan->mahasiswa?->user_id === $user->id),
            403
        );
    }

    private function ensurePending(PengajuanJudul $pengajuan): void
    {
        abort_unless($pengajuan->isPending(), 403);
    }
}
