<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\KeahlianDosen;
use Illuminate\Http\Request;

class KeahlianDosenController extends Controller
{
    public function store(Request $request, Dosen $dosen)
    {
        $validated = $request->validate([
            'bidang_keahlian' => ['required', 'string', 'max:255'],
            'deskripsi_keahlian' => ['required', 'string'],
            'tipe_faktor' => ['required', 'integer', 'in:1,2'],
        ]);

        $dosen->keahlian()->create($validated);

        return redirect()->route('dosen.edit', $dosen)->with('success', 'Keahlian dosen berhasil ditambahkan.');
    }

    public function update(Request $request, KeahlianDosen $id)
    {
        $validated = $request->validate([
            'bidang_keahlian' => ['required', 'string', 'max:255'],
            'deskripsi_keahlian' => ['required', 'string'],
            'tipe_faktor' => ['required', 'integer', 'in:1,2'],
        ]);

        $id->update($validated);

        return redirect()->route('dosen.edit', $id->dosen)->with('success', 'Keahlian dosen berhasil diperbarui.');
    }

    public function destroy(KeahlianDosen $id)
    {
        $dosen = $id->dosen;
        $id->delete();

        return redirect()->route('dosen.edit', $dosen)->with('success', 'Keahlian dosen berhasil dihapus.');
    }
}
