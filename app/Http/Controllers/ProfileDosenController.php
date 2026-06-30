<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileDosenRequest;
use App\Models\BidangKeahlian;
use App\Services\ProfileDosenService;
use Illuminate\Support\Facades\Auth;

class ProfileDosenController extends Controller
{
    public function __construct(
        private ProfileDosenService $profileDosenService
    ) {}

    /**
     * Tampilkan form edit profil dosen yang sedang login.
     * Data diambil murni dari Auth::user()->dosen, TIDAK menerima
     * parameter id dari route, sehingga dosen tidak mungkin mengakses
     * data dosen lain lewat manipulasi URL.
     */
    public function edit()
    {
        $dosen = Auth::user()->dosen;

        if (! $dosen) {
            abort(404, 'Data dosen untuk akun ini tidak ditemukan.');
        }

        $dosen->load(['programStudi', 'bidangKeahlians', 'profile']);

        $bidangKeahlians = BidangKeahlian::orderBy('nama')->get();

        return view('profil-dosen.edit', compact('dosen', 'bidangKeahlians'));
    }

    /**
     * Update profil dosen yang sedang login.
     */
    public function update(UpdateProfileDosenRequest $request)
    {
        $dosen = Auth::user()->dosen;

        if (! $dosen) {
            abort(404, 'Data dosen untuk akun ini tidak ditemukan.');
        }

        $this->profileDosenService->update($dosen, $request->validated());

        return redirect()
            ->route('profile-dosen.edit')
            ->with('success', 'Profil berhasil diperbarui.');
    }
}