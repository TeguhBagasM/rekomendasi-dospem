<?php

namespace App\Http\Controllers;

use App\Models\PengajuanJudul;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengajuanValidasiController extends Controller
{
    public function index(Request $request)
    {
        $pengajuans = PengajuanJudul::with(['mahasiswa.user'])
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->latest()
            ->get();

        $selectedId = $request->query('id', $pengajuans->first()?->id);
        $selectedPengajuan = $pengajuans->firstWhere('id', (int) $selectedId) ?? $pengajuans->first();

        return view('admin.pengajuan.index', compact('pengajuans', 'selectedPengajuan', 'selectedId'));
    }

    public function approve(PengajuanJudul $pengajuan)
    {
        /** @var User|null $user */
        $user = Auth::user();
        abort_unless($user && $user->hasRole('admin'), 403);

        $pengajuan->approve($user);

        return redirect()->route('admin.pengajuan.index', ['id' => $pengajuan->id])
            ->with('success', 'Pengajuan berhasil disetujui.');
    }

    public function reject(Request $request, PengajuanJudul $pengajuan)
    {
        /** @var User|null $user */
        $user = Auth::user();
        abort_unless($user && $user->hasRole('admin'), 403);

        $validated = $request->validate([
            'alasan_penolakan' => ['required', 'string', 'max:1000'],
        ]);

        $pengajuan->reject($user, $validated['alasan_penolakan']);

        return redirect()->route('admin.pengajuan.index', ['id' => $pengajuan->id])
            ->with('success', 'Pengajuan berhasil ditolak.');
    }
}