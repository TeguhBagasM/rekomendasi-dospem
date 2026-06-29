<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DosenController extends Controller
{
    public function index()
    {
        $dosens = Dosen::with('keahlian')
            ->withCount('keahlian')
            ->orderBy('nama')
            ->paginate(10);

        return view('dosen.index', compact('dosens'));
    }

    public function create()
    {
        return view('dosen.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nidn' => ['required', 'string', 'max:255', 'unique:dosens,nidn'],
            'nama' => ['required', 'string', 'max:255'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'program_studi' => ['nullable', 'string', 'max:255'],
            'kuota_bimbingan' => ['required', 'integer', 'min:0'],
            'no_hp' => ['nullable', 'string', 'max:20'],
        ]);

        DB::transaction(function () use ($validated) {
            $programStudi = null;

            if (! empty($validated['program_studi'])) {
                $programStudi = ProgramStudi::where('program_studi', $validated['program_studi'])
                    ->orWhere('kode_prodi', $validated['program_studi'])
                    ->first();
            }

            $user = User::create([
                'name' => $validated['nama'],
                'email' => $validated['nidn'].'@dosen.local',
                'password' => Hash::make($validated['nidn']),
            ]);

            Dosen::create([
                'user_id' => $user->id,
                'program_studi_id' => $programStudi?->id,
                'nidn' => $validated['nidn'],
                'nama' => $validated['nama'],
                'jabatan' => $validated['jabatan'] ?? null,
                'program_studi' => $validated['program_studi'] ?? null,
                'kuota_bimbingan' => $validated['kuota_bimbingan'],
                'kuota_terisi' => 0,
                'no_hp' => $validated['no_hp'] ?? null,
            ]);
        });

        return redirect()->route('dosen.index')->with('success', 'Dosen berhasil ditambahkan.');
    }

    public function show(Dosen $dosen)
    {
        return redirect()->route('dosen.edit', $dosen);
    }

    public function edit(Dosen $dosen)
    {
        $dosen->load('keahlian');

        return view('dosen.edit', compact('dosen'));
    }

    public function update(Request $request, Dosen $dosen)
    {
        $validated = $request->validate([
            'nidn' => ['required', 'string', 'max:255', Rule::unique('dosens', 'nidn')->ignore($dosen->id)],
            'nama' => ['required', 'string', 'max:255'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'program_studi' => ['nullable', 'string', 'max:255'],
            'kuota_bimbingan' => ['required', 'integer', 'min:0'],
            'no_hp' => ['nullable', 'string', 'max:20'],
        ]);

        DB::transaction(function () use ($dosen, $validated) {
            $programStudi = null;

            if (! empty($validated['program_studi'])) {
                $programStudi = ProgramStudi::where('program_studi', $validated['program_studi'])
                    ->orWhere('kode_prodi', $validated['program_studi'])
                    ->first();
            }

            $dosen->update([
                'nidn' => $validated['nidn'],
                'nama' => $validated['nama'],
                'jabatan' => $validated['jabatan'] ?? null,
                'program_studi_id' => $programStudi?->id,
                'program_studi' => $validated['program_studi'] ?? null,
                'kuota_bimbingan' => $validated['kuota_bimbingan'],
                'no_hp' => $validated['no_hp'] ?? null,
            ]);

            if ($dosen->user) {
                $dosen->user->update([
                    'name' => $validated['nama'],
                    'email' => $validated['nidn'].'@dosen.local',
                ]);
            }
        });

        return redirect()->route('dosen.edit', $dosen)->with('success', 'Dosen berhasil diperbarui.');
    }

    public function destroy(Dosen $dosen)
    {
        DB::transaction(function () use ($dosen) {
            if ($dosen->user) {
                $dosen->user->delete();
            } else {
                $dosen->delete();
            }
        });

        return redirect()->route('dosen.index')->with('success', 'Dosen berhasil dihapus.');
    }
}
