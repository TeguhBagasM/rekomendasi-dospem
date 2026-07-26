<?php

namespace App\Http\Controllers;

use App\Models\BidangKeahlian;
use Illuminate\Http\Request;

class BidangKeahlianController extends Controller
{
    public function index()
    {
        $bidangKeahlians = BidangKeahlian::withCount('dosens')->orderBy('nama')->paginate(10);

        return view('bidang-keahlian.index', compact('bidangKeahlians'));
    }

    public function create()
    {
        return view('bidang-keahlian.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:bidang_keahlians,nama',
        ]);
        BidangKeahlian::create($validated);

        return redirect()->route('bidang-keahlian.index')->with('success', 'Bidang keahlian berhasil ditambahkan.');
    }

    public function edit(BidangKeahlian $bidangKeahlian)
    {
        return view('bidang-keahlian.edit', compact('bidangKeahlian'));
    }

    public function update(Request $request, BidangKeahlian $bidangKeahlian)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:bidang_keahlians,nama,'.$bidangKeahlian->id,
        ]);
        $bidangKeahlian->update($validated);

        return redirect()->route('bidang-keahlian.index')->with('success', 'Bidang keahlian berhasil diperbarui.');
    }

    public function destroy(BidangKeahlian $bidangKeahlian)
    {
        $bidangKeahlian->delete();

        return redirect()->route('bidang-keahlian.index')->with('success', 'Bidang keahlian berhasil dihapus.');
    }
}
