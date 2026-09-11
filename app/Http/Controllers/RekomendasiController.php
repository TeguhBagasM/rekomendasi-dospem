<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Gelombang;
use App\Models\PengajuanJudul;
use App\Models\RekomendasiDospembing;
use App\Models\RiwayatRekomendasi;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RekomendasiController extends Controller
{
    public function __construct(
        private readonly RecommendationService $recommendationService
    ) {}

    public function index(): View
    {
        $this->authorizeAccess();

        $riwayat = RiwayatRekomendasi::with([
            'rekomendasiDospembings.pengajuan.mahasiswa.programStudi',
            'rekomendasiDospembings.rekomendasiDospem1',
            'rekomendasiDospembings.rekomendasiDospem2',
            'rekomendasiDospembings.penetapanDospembing.dospem1',
            'rekomendasiDospembings.penetapanDospembing.dospem2',
        ])->latest()->first();

        $pengajuans = PengajuanJudul::with(['mahasiswa.programStudi', 'penetapanDospembing.dospem1', 'penetapanDospembing.dospem2'])
            ->where('status', 'disetujui')
            ->latest()
            ->get();

        $recommendations = $riwayat?->rekomendasiDospembings?->keyBy('pengajuan_id') ?? collect();

        return view('rekomendasi.index', compact('pengajuans', 'recommendations', 'riwayat'));
    }

    public function generate(): RedirectResponse
    {
        $this->authorizeAccess();

        $gelombang = Gelombang::where('is_active', true)->first();

        if (! $gelombang) {
            return back()->with('error', 'Tidak ada gelombang yang aktif saat ini.');
        }

        $pengajuans = PengajuanJudul::with(['mahasiswa.programStudi'])
            ->where('status', 'disetujui')
            ->where('gelombang_id', $gelombang->id)
            ->latest()
            ->get();

        if ($pengajuans->isEmpty()) {
            return back()->with('error', "Tidak ada pengajuan disetujui untuk {$gelombang->nama}.");
        }

        $this->recommendationService->generateBatch($pengajuans);

        return back()->with('success', "Rekomendasi dospem berhasil dihitung untuk {$gelombang->nama}.");
    }

    public function exportExcel(): BinaryFileResponse
    {
        $this->authorizeAccess();

        return $this->recommendationService->exportLatestToExcel();
    }

    public function importExcel(Request $request): RedirectResponse
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        try {
            $this->recommendationService->finalizeFromImport($validated['file']);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }

        return back()->with('success', 'File rekomendasi berhasil diimport, kuota dosen diperbarui, dan status telah difinalisasi.');
    }

    public function review(Request $request, int $riwayatId): View
    {
        $this->authorizeAccess();

        $riwayat = RiwayatRekomendasi::with([
            'rekomendasiDospembings.pengajuan.mahasiswa.programStudi',
            'rekomendasiDospembings.pengajuan.gelombang',
            'rekomendasiDospembings.rekomendasiDospem1.profile',
            'rekomendasiDospembings.rekomendasiDospem1.bidangKeahlians',
            'rekomendasiDospembings.rekomendasiDospem2.profile',
            'rekomendasiDospembings.rekomendasiDospem2.bidangKeahlians',
            'rekomendasiDospembings.hasilPerhitungans.dosen',
            'rekomendasiDospembings.penetapanDospembing',
        ])->findOrFail($riwayatId);

        $recommendations = $riwayat->rekomendasiDospembings()->with([
            'hasilPerhitungans.dosen',
            'rekomendasiDospem1',
            'rekomendasiDospem2',
            'pengajuan.mahasiswa.programStudi',
            'pengajuan.gelombang',
            'penetapanDospembing.dospem1',
            'penetapanDospembing.dospem2',
        ])->get();

        $gelombangId = $riwayat->rekomendasiDospembings->first()?->pengajuan?->gelombang_id;

        $dosens = Dosen::with([
            'profile',
            'programStudi',
            'bidangKeahlians',
            'kuotaGelombangs' => fn ($query) => $query->where('gelombang_id', $gelombangId),
        ])
            ->orderBy('nama')
            ->get();

        $dosensWithQuota = $dosens->map(function (Dosen $dosen) use ($gelombangId): Dosen {
            $kuotaGelombang = $dosen->kuotaForGelombang($gelombangId);

            $dosen->sisa_kuota_dospem1 = $kuotaGelombang?->remainingDospem1() ?? 0;
            $dosen->sisa_kuota_dospem2 = $kuotaGelombang?->remainingDospem2() ?? 0;

            return $dosen;
        });

        $dosensJson = $dosensWithQuota->map(fn (Dosen $dosen): array => [
            'id' => $dosen->id,
            'nama' => $dosen->nama,
            'nidn' => $dosen->nidn,
            'program_studi' => $dosen->programStudi?->program_studi,
            'jabatan_fungsional' => $dosen->jabatan_fungsional,
            'pendidikan_terakhir' => $dosen->pendidikan_terakhir,
            'bidang_keahlian' => $dosen->bidangKeahlians->pluck('nama')->join(', '),
            'dokumen_tfidf' => $dosen->profile?->dokumen_tfidf,
            'sisa_dospem1' => $dosen->sisa_kuota_dospem1,
            'sisa_dospem2' => $dosen->sisa_kuota_dospem2,
        ])->values()->toJson();

        $gelombang = $recommendations->first()?->pengajuan?->gelombang;

        return view('admin.rekomendasi.review', compact(
            'riwayat',
            'recommendations',
            'dosensWithQuota',
            'dosensJson',
            'gelombang',
            'riwayatId'
        ));
    }

    public function updateDraft(Request $request, int $rekomendasiId): RedirectResponse
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'rekomendasi_dospem1_id' => ['nullable', 'integer', 'exists:dosens,id'],
            'rekomendasi_dospem2_id' => ['nullable', 'integer', 'exists:dosens,id'],
            'catatan' => ['nullable', 'string'],
        ]);

        $rekomendasi = RekomendasiDospembing::findOrFail($rekomendasiId);

        $this->applyDraftOverride($rekomendasi, $validated);

        return back()->with('success', 'Draft rekomendasi berhasil diupdate.');
    }

    /**
     * Simpan seluruh perubahan dropdown dosen pada satu halaman tinjau sekaligus.
     */
    public function bulkUpdateDraft(Request $request, int $riwayatId): RedirectResponse
    {
        $this->authorizeAccess();

        $riwayat = RiwayatRekomendasi::with('rekomendasiDospembings')->findOrFail($riwayatId);

        if ($riwayat->status === 'finalized') {
            return back()->with('error', 'Riwayat sudah di-finalisasi, perubahan draft tidak dapat disimpan.');
        }

        $validated = $request->validate([
            'rows' => ['required', 'array'],
            'rows.*.rekomendasi_dospem1_id' => ['nullable', 'integer', 'exists:dosens,id'],
            'rows.*.rekomendasi_dospem2_id' => ['nullable', 'integer', 'exists:dosens,id'],
            'rows.*.catatan' => ['nullable', 'string'],
        ]);

        $rekomendasiIds = $riwayat->rekomendasiDospembings->pluck('id')->all();

        foreach ($validated['rows'] as $id => $row) {
            if (! in_array((int) $id, $rekomendasiIds, true)) {
                continue;
            }

            $this->applyDraftOverride(RekomendasiDospembing::find($id), $row);
        }

        return back()->with('success', 'Perubahan draft rekomendasi berhasil disimpan.');
    }

    public function finalize(int $riwayatId): RedirectResponse
    {
        $this->authorizeAccess();

        $riwayat = RiwayatRekomendasi::findOrFail($riwayatId);

        try {
            $this->recommendationService->finalizeDraft($riwayat);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }

        return back()->with('success', 'Penetapan dospem berhasil di-finalisasi. Kuota dosen resmi diperbarui.');
    }

    public function exportDraft(int $riwayatId): BinaryFileResponse
    {
        $this->authorizeAccess();

        $riwayat = RiwayatRekomendasi::findOrFail($riwayatId);

        return $this->recommendationService->exportRiwayat($riwayat);
    }

    /**
     * Terapkan override manual pilihan dospem pada satu rekomendasi draft.
     * Hanya memperbarui rekomendasi_dospembings — kuota TIDAK diubah di sini.
     * Override ditandai pada json 'ringkasan' (original vs pilihan baru).
     */
    private function applyDraftOverride(RekomendasiDospembing $rekomendasi, array $row): void
    {
        $dospem1Id = $row['rekomendasi_dospem1_id'] ?? null;
        $dospem2Id = $row['rekomendasi_dospem2_id'] ?? null;
        $catatan = $row['catatan'] ?? null;

        $ringkasan = $rekomendasi->ringkasan ?? [];

        // Rekam pilihan asli sistem hanya pada penyimpanan pertama.
        $ringkasan['original_rekomendasi_dospem1_id'] ??= $rekomendasi->rekomendasi_dospem1_id;
        $ringkasan['original_rekomendasi_dospem2_id'] ??= $rekomendasi->rekomendasi_dospem2_id;

        $dospem1 = $dospem1Id ? Dosen::with('programStudi')->find($dospem1Id) : null;
        $dospem2 = $dospem2Id ? Dosen::with('programStudi')->find($dospem2Id) : null;

        if ($dospem1) {
            $ringkasan['rekomendasi_dospem1'] = [
                'id' => $dospem1->id,
                'nama' => $dospem1->nama,
                'nidn' => $dospem1->nidn,
                'program_studi' => $dospem1->programStudi?->program_studi,
                'jabatan_fungsional' => $dospem1->jabatan_fungsional,
            ];
        }

        if ($dospem2) {
            $ringkasan['rekomendasi_dospem2'] = [
                'id' => $dospem2->id,
                'nama' => $dospem2->nama,
                'nidn' => $dospem2->nidn,
                'program_studi' => $dospem2->programStudi?->program_studi,
                'jabatan_fungsional' => $dospem2->jabatan_fungsional,
            ];
        }

        $manualOverride = (
            ($dospem1Id !== null && (int) $dospem1Id !== (int) ($ringkasan['original_rekomendasi_dospem1_id'] ?? null))
            || ($dospem2Id !== null && (int) $dospem2Id !== (int) ($ringkasan['original_rekomendasi_dospem2_id'] ?? null))
        );

        if ($manualOverride) {
            $ringkasan['manual_override'] = true;
            $ringkasan['manual_override_at'] = now()->toDateTimeString();
            $ringkasan['manual_override_by'] = Auth::id();
        }

        if ($catatan !== null) {
            $ringkasan['catatan'] = $catatan;
        }

        $rekomendasi->update([
            'rekomendasi_dospem1_id' => $dospem1Id,
            'rekomendasi_dospem2_id' => $dospem2Id,
            'ringkasan' => $ringkasan,
        ]);
    }

    private function authorizeAccess(): void
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless($user && ($user->isAdmin() || $user->isDosen()), 403);
    }
}
