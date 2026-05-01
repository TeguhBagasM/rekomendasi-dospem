<?php

namespace App\Http\Controllers;

use App\Exports\MahasiswaExport;
use App\Imports\MahasiswaImport;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

class MahasiswaController extends Controller
{
    public function index()
    {
        $mahasiswas = Mahasiswa::with(['user', 'programStudi'])->orderBy('nama')->get();

        return view('mahasiswa.index', compact('mahasiswas'));
    }

    public function create()
    {
        $programStudis = ProgramStudi::orderBy('program_studi')->get();

        return view('mahasiswa.create', compact('programStudis'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nim' => ['required', 'string', 'max:255', 'unique:mahasiswas,nim'],
            'nama' => ['required', 'string', 'max:255'],
            'program_studi_id' => ['required', 'integer', 'exists:program_studis,id'],
            'angkatan' => ['required', 'digits:4'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'no_hp' => ['nullable', 'string', 'max:20'],
        ]);

        $email = $validated['email'] ?? $validated['nim'].'@mahasiswa.local';

        $user = User::create([
            'name' => $validated['nama'],
            'email' => $email,
            'password' => Hash::make($validated['nim']),
        ]);

        $user->assignRole(Role::firstOrCreate(['name' => 'mahasiswa']));

        Mahasiswa::create([
            'user_id' => $user->id,
            'nim' => $validated['nim'],
            'nama' => $validated['nama'],
            'program_studi_id' => $validated['program_studi_id'],
            'angkatan' => $validated['angkatan'],
            'no_hp' => $validated['no_hp'] ?? null,
        ]);

        return redirect()->route('mahasiswa.index')->with('success', 'Mahasiswa berhasil ditambahkan.');
    }

    public function edit(Mahasiswa $mahasiswa)
    {
        $programStudis = ProgramStudi::orderBy('program_studi')->get();

        return view('mahasiswa.edit', compact('mahasiswa', 'programStudis'));
    }

    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        $validated = $request->validate([
            'nim' => ['required', 'string', 'max:255', Rule::unique('mahasiswas', 'nim')->ignore($mahasiswa->id)],
            'nama' => ['required', 'string', 'max:255'],
            'program_studi_id' => ['required', 'integer', 'exists:program_studis,id'],
            'angkatan' => ['required', 'digits:4'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore(optional($mahasiswa->user)->id)],
            'no_hp' => ['nullable', 'string', 'max:20'],
        ]);

        $email = $validated['email'] ?? $validated['nim'].'@mahasiswa.local';

        $mahasiswa->update([
            'nim' => $validated['nim'],
            'nama' => $validated['nama'],
            'program_studi_id' => $validated['program_studi_id'],
            'angkatan' => $validated['angkatan'],
            'no_hp' => $validated['no_hp'] ?? null,
        ]);

        if ($mahasiswa->user) {
            $mahasiswa->user->update([
                'name' => $validated['nama'],
                'email' => $email,
                'password' => Hash::make($validated['nim']),
            ]);
        }

        return redirect()->route('mahasiswa.index')->with('success', 'Mahasiswa berhasil diperbarui.');
    }

    public function destroy(Mahasiswa $mahasiswa)
    {
        if ($mahasiswa->user) {
            $mahasiswa->user->delete();
        } else {
            $mahasiswa->delete();
        }

        return redirect()->route('mahasiswa.index')->with('success', 'Mahasiswa berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        Excel::import(new MahasiswaImport(), $request->file('file'));

        return redirect()->route('mahasiswa.index')->with('success', 'Data mahasiswa berhasil diimport.');
    }

    public function export()
    {
        return Excel::download(new MahasiswaExport(), 'data-mahasiswa.xlsx');
    }
}
