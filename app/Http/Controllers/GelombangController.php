<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGelombangRequest;
use App\Http\Requests\UpdateGelombangRequest;
use App\Models\Gelombang;
use App\Services\GelombangService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GelombangController extends Controller
{
    public function __construct(protected GelombangService $gelombangService)
    {
    }

    public function index(): View
    {
        $gelombangs = Gelombang::withCount('pengajuanJuduls')
            ->orderBy('tanggal_mulai', 'asc')
            ->get();

        return view('gelombang.index', compact('gelombangs'));
    }

    public function create(): View
    {
        return view('gelombang.create');
    }

    public function store(StoreGelombangRequest $request): RedirectResponse
    {
        $this->gelombangService->create($request->validated());

        return redirect()
            ->route('gelombang.index')
            ->with('success', 'Gelombang berhasil ditambahkan.');
    }

    public function edit(Gelombang $gelombang): View
    {
        return view('gelombang.edit', compact('gelombang'));
    }

    public function update(UpdateGelombangRequest $request, Gelombang $gelombang): RedirectResponse
    {
        $this->gelombangService->update($gelombang, $request->validated());

        return redirect()
            ->route('gelombang.index')
            ->with('success', 'Gelombang berhasil diperbarui.');
    }

    public function destroy(Gelombang $gelombang): RedirectResponse
    {
        $this->gelombangService->delete($gelombang);

        return redirect()
            ->route('gelombang.index')
            ->with('success', 'Gelombang berhasil dihapus.');
    }

    public function toggleStatus(Gelombang $gelombang): RedirectResponse
    {
        $updated = $this->gelombangService->toggleStatus($gelombang);

        return redirect()
            ->route('gelombang.index')
            ->with('success', $updated->is_active
                ? "Gelombang \"{$updated->nama}\" berhasil diaktifkan."
                : "Gelombang \"{$updated->nama}\" dinonaktifkan.");
    }
}