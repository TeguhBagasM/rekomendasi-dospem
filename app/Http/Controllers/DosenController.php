<?php

namespace App\Http\Controllers;

use App\Exports\DosenExport;
use App\Exports\DosenTemplateExport;
use App\Http\Requests\ImportDosenRequest;
use App\Imports\DosenImport;
use App\Models\BidangKeahlian;
use App\Models\Dosen;
use App\Models\ProgramStudi;
use App\Models\User;
use App\Services\DosenProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class DosenController extends Controller
{
    public function __construct(
        private DosenProfileService $dosenProfileService
    ) {}

    public function index()
    {
        $dosens = Dosen::with([
            'programStudi',
            'bidangKeahlians',
            'profile',
        ])
        ->withCount('bidangKeahlians')
        ->orderBy('nama')
        ->paginate(10);

        return view('dosen.index', compact('dosens'));
    }

    public function create()
    {
        $programStudis    = ProgramStudi::orderBy('program_studi')->get();
        $bidangKeahlians  = BidangKeahlian::orderBy('nama')->get();

        return view('dosen.create', compact('programStudis', 'bidangKeahlians'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nidn'                  => 'required|unique:dosens,nidn',
            'nama'                  => 'required|string|max:255',
            'program_studi_id'      => 'nullable|exists:program_studis,id',
            'jabatan_fungsional'    => 'nullable|string|max:255',
            'pendidikan_terakhir'   => 'nullable|string|max:10',
            'kuota_dospem1'         => 'required|integer|min:0',
            'kuota_dospem2'         => 'required|integer|min:0',
            'no_hp'                 => 'nullable|string|max:20',
            'bidang_keahlian_ids'   => 'nullable|array',
            'bidang_keahlian_ids.*' => 'exists:bidang_keahlians,id',
            'topik_metode'          => 'nullable|string',
            'judul_penelitian'      => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name'     => $validated['nama'],
                'email'    => $validated['nidn'] . '@dosen.local',
                'password' => Hash::make($validated['nidn']),
            ]);
            $user->assignRole('dosen');

            $dosen = Dosen::create([
                'user_id'             => $user->id,
                'nidn'                => $validated['nidn'],
                'nama'                => $validated['nama'],
                'program_studi_id'    => $validated['program_studi_id'] ?? null,
                'jabatan_fungsional'  => $validated['jabatan_fungsional'] ?? null,
                'pendidikan_terakhir' => $validated['pendidikan_terakhir'] ?? null,
                'kuota_dospem1'       => $validated['kuota_dospem1'],
                'kuota_dospem2'       => $validated['kuota_dospem2'],
                'no_hp'               => $validated['no_hp'] ?? null,
            ]);

            // Sync bidang keahlian
            $dosen->bidangKeahlians()->sync($validated['bidang_keahlian_ids'] ?? []);

            // Selalu buat profile, walau topik_metode/judul_penelitian kosong,
            // agar dokumen_tfidf tetap bisa dihitung dari bidang keahlian saja.
            $dosen->profile()->updateOrCreate(
                ['dosen_id' => $dosen->id],
                [
                    'topik_metode'     => $validated['topik_metode'] ?? null,
                    'judul_penelitian' => $validated['judul_penelitian'] ?? null,
                ]
            );

            // Generate ulang dokumen_tfidf berdasarkan kondisi terbaru
            $this->dosenProfileService->syncDokumenTfidf($dosen);
        });

        return redirect()
            ->route('dosen.index')
            ->with('success', 'Dosen berhasil ditambahkan.');
    }

    public function edit(Dosen $dosen)
    {
        $dosen->load(['programStudi', 'bidangKeahlians', 'profile']);

        $programStudis   = ProgramStudi::orderBy('program_studi')->get();
        $bidangKeahlians = BidangKeahlian::orderBy('nama')->get();

        return view('dosen.edit', compact('dosen', 'programStudis', 'bidangKeahlians'));
    }

    public function update(Request $request, Dosen $dosen)
    {
        $validated = $request->validate([
            'nidn'                  => ['required', Rule::unique('dosens')->ignore($dosen->id)],
            'nama'                  => 'required|string|max:255',
            'program_studi_id'      => 'nullable|exists:program_studis,id',
            'jabatan_fungsional'    => 'nullable|string|max:255',
            'pendidikan_terakhir'   => 'nullable|string|max:10',
            'kuota_dospem1'         => 'required|integer|min:0',
            'kuota_dospem2'         => 'required|integer|min:0',
            'no_hp'                 => 'nullable|string|max:20',
            'bidang_keahlian_ids'   => 'nullable|array',
            'bidang_keahlian_ids.*' => 'exists:bidang_keahlians,id',
            'topik_metode'          => 'nullable|string',
            'judul_penelitian'      => 'nullable|string',
        ]);

        DB::transaction(function () use ($dosen, $validated) {
            $dosen->update([
                'nidn'                => $validated['nidn'],
                'nama'                => $validated['nama'],
                'program_studi_id'    => $validated['program_studi_id'] ?? null,
                'jabatan_fungsional'  => $validated['jabatan_fungsional'] ?? null,
                'pendidikan_terakhir' => $validated['pendidikan_terakhir'] ?? null,
                'kuota_dospem1'       => $validated['kuota_dospem1'],
                'kuota_dospem2'       => $validated['kuota_dospem2'],
                'no_hp'               => $validated['no_hp'] ?? null,
            ]);

            if ($dosen->user) {
                $dosen->user->update([
                    'name'  => $validated['nama'],
                    'email' => $validated['nidn'] . '@dosen.local',
                ]);
            }

            // Sync bidang keahlian
            $dosen->bidangKeahlians()->sync($validated['bidang_keahlian_ids'] ?? []);

            // Upsert profil penelitian
            $dosen->profile()->updateOrCreate(
                ['dosen_id' => $dosen->id],
                [
                    'topik_metode'     => $validated['topik_metode'] ?? null,
                    'judul_penelitian' => $validated['judul_penelitian'] ?? null,
                ]
            );

            // Generate ulang dokumen_tfidf berdasarkan kondisi terbaru
            $this->dosenProfileService->syncDokumenTfidf($dosen);
        });

        return redirect()
            ->route('dosen.edit', $dosen)
            ->with('success', 'Dosen berhasil diperbarui.');
    }

    public function destroy(Dosen $dosen)
    {
        DB::transaction(function () use ($dosen) {
            $user = $dosen->user;

            $dosen->bidangKeahlians()->detach();
            $dosen->delete();

            if ($user) {
                $user->delete();
            }
        });

        return redirect()
            ->route('dosen.index')
            ->with('success', 'Dosen berhasil dihapus.');
    }

    /**
     * Export seluruh data dosen ke file Excel.
     */
    public function export()
    {
        return Excel::download(new DosenExport, 'data-dosen-' . now()->format('Ymd_His') . '.xlsx');
    }

    /**
     * Unduh template kosong untuk import data dosen.
     */
    public function downloadTemplate()
    {
        return Excel::download(new DosenTemplateExport, 'template-import-dosen.xlsx');
    }

    /**
     * Import data dosen dari file Excel.
     * NIDN sebagai unique key: jika sudah ada -> update, jika belum -> buat user + dosen baru.
     */
    public function import(ImportDosenRequest $request)
    {
        $import = new DosenImport;

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return redirect()
                ->route('dosen.index')
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }

        $message = "Import selesai. {$import->created} dosen baru ditambahkan, {$import->updated} dosen diperbarui.";

        if (!empty($import->errors)) {
            $message .= ' Baris dilewati: ' . implode(' ', $import->errors);
        }

        return redirect()
            ->route('dosen.index')
            ->with('success', $message);
    }
}