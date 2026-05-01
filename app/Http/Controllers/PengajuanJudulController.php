<?php

namespace App\Http\Controllers;

use App\Models\PengajuanJudul;
use App\Services\TextSimilarityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        
        /** @var \App\Models\User $user */
        $user = Auth::user();
        abort_unless($user, 403);
        
        $mahasiswa = $user->mahasiswa;
        abort_unless($mahasiswa, 403);
        
        if (! $mahasiswa->bolehMengajukanBaru()) {
            return redirect()->route('pengajuan.index')->with('error', 'Anda sudah memiliki pengajuan yang disetujui atau masih pending.');
        }

        return view('pengajuan.create');
    }

    public function store(Request $request, TextSimilarityService $textSimilarityService)
    {
        $this->ensureMahasiswa();

        $validated = $request->validate([
            'judul_skripsi' => ['required', 'string', 'max:255'],
            'file_proposal' => ['required', 'file', 'mimes:pdf', 'max:2048'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        abort_unless($user, 403);

        $mahasiswa = $user->mahasiswa;
        abort_unless($mahasiswa, 403);
        
        if (! $mahasiswa->bolehMengajukanBaru()) {
            return redirect()->route('pengajuan.index')->with('error', 'Anda sudah memiliki pengajuan yang disetujui atau masih pending.');
        }

        $similarityResult = $textSimilarityService->checkSimilarity($validated['judul_skripsi']);

        logger([
            'judul_baru' => $validated['judul_skripsi'],
            'hasil' => $similarityResult,
        ]);

        if ($similarityResult['is_similar']) {
            return back()->withErrors([
                'judul_skripsi' => 'Judul memiliki kemiripan ' . round($similarityResult['percent'], 2) . '% dengan: "' . $similarityResult['matched_title'] . '". Gunakan judul lain.',
            ])->withInput();
        }

        $filePath = null;

        if ($request->hasFile('file_proposal')) {
            $filePath = $this->storeProposalFile($request->file('file_proposal'), $mahasiswa->nim, $mahasiswa->nama);
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

            $data['file_proposal'] = $this->storeProposalFile($request->file('file_proposal'), $pengajuan->mahasiswa?->nim ?? '', $pengajuan->mahasiswa?->nama ?? 'proposal');
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

    private function storeProposalFile(\Illuminate\Http\UploadedFile $file, string $nim, string $nama): string
    {
        $safeName = Str::slug(trim($nim . ' ' . $nama), '_');
        $filename = $safeName . '.' . $file->getClientOriginalExtension();

        return $file->storeAs('proposal', $filename, 'public');
    }
}
