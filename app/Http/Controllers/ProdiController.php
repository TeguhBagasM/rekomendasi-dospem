<?php

namespace App\Http\Controllers;

use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProdiController extends Controller
{
    public function index()
    {
        $prodis = ProgramStudi::orderBy('program_studi')->paginate(10);

        return view('prodi.index', compact('prodis'));
    }

    public function create()
    {
        return view('prodi.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'program_studi' => ['required', 'string', 'max:255'],
            'kode_prodi' => ['required', 'string', 'max:50', 'unique:program_studis,kode_prodi'],
            'jenjang' => ['required', 'string', 'max:10', Rule::in(['D3', 'S1', 'S2', 'S3'])],
            'kaprodi' => ['required', 'string', 'max:255'],
        ]);

        ProgramStudi::create($validated);

        return redirect()->route('prodi.index')->with('success', 'Program studi berhasil ditambahkan.');
    }

    public function edit(ProgramStudi $prodi)
    {
        return view('prodi.edit', compact('prodi'));
    }

    public function update(Request $request, ProgramStudi $prodi)
    {
        $validated = $request->validate([
            'program_studi' => ['required', 'string', 'max:255'],
            'kode_prodi' => ['required', 'string', 'max:50', Rule::unique('program_studis', 'kode_prodi')->ignore($prodi->id)],
            'jenjang' => ['required', 'string', 'max:10', Rule::in(['D3', 'S1', 'S2', 'S3'])],
            'kaprodi' => ['required', 'string', 'max:255'],
        ]);

        $prodi->update($validated);

        return redirect()->route('prodi.index')->with('success', 'Program studi berhasil diperbarui.');
    }

    public function destroy(ProgramStudi $prodi)
    {
        $prodi->delete();

        return redirect()->route('prodi.index')->with('success', 'Program studi berhasil dihapus.');
    }
}
