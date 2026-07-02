<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\PenetapanDospembing;
use App\Models\PengajuanJudul;
use App\Models\RekomendasiDospembing;
use App\Models\RiwayatRekomendasi;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class PenetapanDospembingImportService
{
    public function finalize(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            throw ValidationException::withMessages([
                'file' => ['File Excel tidak memiliki data untuk di-import.'],
            ]);
        }

        $normalizedRows = $rows->map(fn ($row) => $this->normalizeRow($row));
        $this->ensureRequiredColumns($normalizedRows->first());

        $riwayatIds = $normalizedRows
            ->pluck('riwayat_rekomendasi_id')
            ->filter(fn ($value) => $value !== null && $value !== '' && $value !== '-')
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values();

        if ($riwayatIds->isEmpty()) {
            throw ValidationException::withMessages([
                'file' => ['Kolom riwayat_rekomendasi_id wajib ada pada file Excel hasil rekomendasi.'],
            ]);
        }

        if ($riwayatIds->count() !== 1) {
            throw ValidationException::withMessages([
                'file' => ['File Excel hanya boleh berisi satu riwayat rekomendasi. Pisahkan file per batch riwayat.'],
            ]);
        }

        $riwayatId = (int) $riwayatIds->first();

        $riwayat = RiwayatRekomendasi::query()
            ->with(['rekomendasiDospembings.pengajuan.mahasiswa.programStudi'])
            ->find($riwayatId);

        if (! $riwayat) {
            throw ValidationException::withMessages([
                'file' => ["Riwayat rekomendasi dengan ID '{$riwayatId}' tidak ditemukan."],
            ]);
        }

        $pendingErrors = $this->validateRows($normalizedRows, $riwayat);

        if ($pendingErrors !== []) {
            throw ValidationException::withMessages([
                'file' => $pendingErrors,
            ]);
        }

        DB::transaction(function () use ($normalizedRows, $riwayat): void {
            $context = $this->buildContext($normalizedRows, $riwayat);
            $validatedRows = $this->prepareRows($normalizedRows, $context);

            foreach ($validatedRows as $item) {
                $pengajuan = $item['pengajuan'];
                $rekomendasi = $item['rekomendasi'];
                $dospem1 = $item['dospem1'];
                $dospem2 = $item['dospem2'];
                $catatan = $item['catatan'];

                $existingPenetapan = PenetapanDospembing::query()
                    ->where('pengajuan_id', $pengajuan->id)
                    ->lockForUpdate()
                    ->first();

                $this->releaseQuota($existingPenetapan);

                $penetapan = PenetapanDospembing::updateOrCreate(
                    ['pengajuan_id' => $pengajuan->id],
                    [
                        'rekomendasi_dospembing_id' => $rekomendasi?->id,
                        'dospem1_id' => $dospem1?->id,
                        'dospem2_id' => $dospem2?->id,
                        'ditetapkan_oleh' => Auth::id(),
                        'ditetapkan_at' => now(),
                        'catatan' => $catatan,
                        'sumber' => 'import',
                    ]
                );

                $penetapan->loadMissing(['dospem1', 'dospem2']);
                $this->consumeQuota($penetapan);
            }

            $riwayat->update([
                'status' => 'finalized',
            ]);
        });
    }

    private function validateRows(Collection $rows, RiwayatRekomendasi $riwayat): array
    {
        $errors = [];
        $seenPengajuanIds = [];
        $context = $this->buildContext($rows, $riwayat);

        foreach ($rows->values() as $index => $row) {
            $rowNumber = $index + 2;
            $pengajuan = $this->resolvePengajuan($row, $context);

            if (! $pengajuan) {
                $errors[] = "Baris {$rowNumber}: Pengajuan Judul tidak ditemukan berdasarkan identifier pada file.";

                continue;
            }

            if (in_array($pengajuan->id, $seenPengajuanIds, true)) {
                $errors[] = "Baris {$rowNumber}: Duplikasi data untuk Pengajuan ID '{$pengajuan->id}' di dalam file.";

                continue;
            }

            $seenPengajuanIds[] = $pengajuan->id;

            $rekomendasi = $this->resolveRekomendasi($row, $pengajuan, $context);

            if (! $rekomendasi) {
                $errors[] = "Baris {$rowNumber}: Record rekomendasi dengan identifier pada file tidak ditemukan di tabel rekomendasi_dospembings.";

                continue;
            }

            if ((int) $rekomendasi->pengajuan_id !== (int) $pengajuan->id) {
                $errors[] = "Baris {$rowNumber}: Record rekomendasi tidak cocok dengan Pengajuan ID '{$pengajuan->id}'.";

                continue;
            }

            $dospem1Nidn = $this->readValue($row, ['final_nidn_dospem_1', 'final_dospem1_nidn', 'dospem1_nidn', 'nidn_dospem1']);
            $dospem2Nidn = $this->readValue($row, ['final_nidn_dospem_2', 'final_dospem2_nidn', 'dospem2_nidn', 'nidn_dospem2']);

            if ($dospem1Nidn === null || $dospem1Nidn === '' || $dospem1Nidn === '-') {
                $errors[] = "Baris {$rowNumber}: NIDN dospem1 harus diisi.";

                continue;
            }

            if ($dospem2Nidn === null || $dospem2Nidn === '' || $dospem2Nidn === '-') {
                $errors[] = "Baris {$rowNumber}: NIDN dospem2 harus diisi.";

                continue;
            }

            $dospem1 = $context['dosensByNidn'][(string) $dospem1Nidn] ?? null;
            $dospem2 = $context['dosensByNidn'][(string) $dospem2Nidn] ?? null;

            if (! $dospem1) {
                $errors[] = "Baris {$rowNumber}: Dosen Pembimbing 1 dengan NIDN '{$dospem1Nidn}' tidak ditemukan.";

                continue;
            }

            if (! $dospem2) {
                $errors[] = "Baris {$rowNumber}: Dosen Pembimbing 2 dengan NIDN '{$dospem2Nidn}' tidak ditemukan.";

                continue;
            }

            if ($dospem1->id === $dospem2->id) {
                $errors[] = "Baris {$rowNumber}: Dosen Pembimbing 1 dan Dosen Pembimbing 2 tidak boleh dosen yang sama.";
            }
        }

        return $errors;
    }

    private function prepareRows(Collection $rows, array $context): array
    {
        $prepared = [];

        foreach ($rows->values() as $row) {
            $pengajuan = $this->resolvePengajuan($row, $context);
            $rekomendasi = $this->resolveRekomendasi($row, $pengajuan, $context);

            $prepared[] = [
                'pengajuan' => $pengajuan,
                'rekomendasi' => $rekomendasi,
                'dospem1' => $context['dosensByNidn'][(string) $this->readValue($row, ['final_nidn_dospem_1', 'final_dospem1_nidn', 'dospem1_nidn', 'nidn_dospem1'])],
                'dospem2' => $context['dosensByNidn'][(string) $this->readValue($row, ['final_nidn_dospem_2', 'final_dospem2_nidn', 'dospem2_nidn', 'nidn_dospem2'])],
                'catatan' => $this->normalizeCatatan($this->readValue($row, ['catatan', 'notes'])),
            ];
        }

        return $prepared;
    }

    private function buildContext(Collection $rows, RiwayatRekomendasi $riwayat): array
    {
        $normalizedRows = $rows->map(fn ($row) => $this->normalizeRow($row));

        $pengajuanIds = $normalizedRows
            ->pluck('pengajuan_id')
            ->filter(fn ($value) => $value !== null && $value !== '' && $value !== '-')
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values();

        $nims = $normalizedRows
            ->pluck('nim')
            ->filter(fn ($value) => $value !== null && $value !== '' && $value !== '-')
            ->map(fn ($value) => trim((string) $value))
            ->unique()
            ->values();

        $rekomendasiIds = $normalizedRows
            ->pluck('rekomendasi_dospembing_id')
            ->filter(fn ($value) => $value !== null && $value !== '' && $value !== '-')
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values();

        $dosenNidns = $normalizedRows
            ->flatMap(function (array $row): array {
                return array_filter([
                    $this->readValue($row, ['final_nidn_dospem_1', 'final_dospem1_nidn', 'dospem1_nidn', 'nidn_dospem1']),
                    $this->readValue($row, ['final_nidn_dospem_2', 'final_dospem2_nidn', 'dospem2_nidn', 'nidn_dospem2']),
                ], fn ($value) => $value !== null && $value !== '' && $value !== '-');
            })
            ->map(fn ($value) => trim((string) $value))
            ->unique()
            ->values();

        $pengajuansById = PengajuanJudul::query()
            ->with(['mahasiswa'])
            ->whereIn('id', $pengajuanIds)
            ->get()
            ->keyBy('id');

        $pengajuansByNim = PengajuanJudul::query()
            ->with(['mahasiswa'])
            ->whereHas('mahasiswa', function ($query) use ($nims): void {
                $query->whereIn('nim', $nims);
            })
            ->get()
            ->keyBy(fn (PengajuanJudul $pengajuan) => (string) $pengajuan->mahasiswa?->nim);

        $dosensByNidn = Dosen::query()
            ->whereIn('nidn', $dosenNidns)
            ->get()
            ->keyBy(fn (Dosen $dosen) => (string) $dosen->nidn);

        $rekomendasiById = RekomendasiDospembing::query()
            ->with(['pengajuan.mahasiswa.programStudi'])
            ->whereIn('id', $rekomendasiIds)
            ->get()
            ->keyBy('id');

        $rekomendasiByRiwayatPengajuanKey = $riwayat->rekomendasiDospembings
            ->keyBy(fn (RekomendasiDospembing $rekomendasi) => $this->buildRecommendationKey((int) $rekomendasi->riwayat_rekomendasi_id, (int) $rekomendasi->pengajuan_id));

        return [
            'pengajuansById' => $pengajuansById,
            'pengajuansByNim' => $pengajuansByNim,
            'dosensByNidn' => $dosensByNidn,
            'rekomendasiById' => $rekomendasiById,
            'rekomendasiByRiwayatPengajuanKey' => $rekomendasiByRiwayatPengajuanKey,
        ];
    }

    private function resolvePengajuan(array $row, array $context): ?PengajuanJudul
    {
        $pengajuanId = $this->readValue($row, ['pengajuan_id', 'id_pengajuan']);
        $rekomendasiId = $this->readValue($row, ['rekomendasi_dospembing_id', 'rekomendasi_id']);

        if ($rekomendasiId !== null && $rekomendasiId !== '' && $rekomendasiId !== '-') {
            $rekomendasi = $context['rekomendasiById'][(int) $rekomendasiId] ?? null;

            if ($rekomendasi) {
                return $rekomendasi->pengajuan;
            }
        }

        if ($pengajuanId !== null && $pengajuanId !== '' && $pengajuanId !== '-') {
            return $context['pengajuansById'][(int) $pengajuanId] ?? null;
        }

        $nim = $this->readValue($row, ['nim']);

        if ($nim !== null && $nim !== '' && $nim !== '-') {
            return $context['pengajuansByNim'][(string) trim((string) $nim)] ?? null;
        }

        return null;
    }

    private function resolveRekomendasi(array $row, PengajuanJudul $pengajuan, array $context): ?RekomendasiDospembing
    {
        $rekomendasiId = $this->readValue($row, ['rekomendasi_dospembing_id', 'rekomendasi_id']);
        $riwayatId = $this->readValue($row, ['riwayat_rekomendasi_id', 'riwayat_id']);

        if ($rekomendasiId !== null && $rekomendasiId !== '' && $rekomendasiId !== '-') {
            $rekomendasi = $context['rekomendasiById'][(int) $rekomendasiId] ?? null;

            if ($rekomendasi && ($riwayatId === null || $riwayatId === '' || $riwayatId === '-' || (int) $rekomendasi->riwayat_rekomendasi_id === (int) $riwayatId)) {
                return $rekomendasi;
            }
        }

        if ($riwayatId !== null && $riwayatId !== '' && $riwayatId !== '-') {
            return $context['rekomendasiByRiwayatPengajuanKey'][$this->buildRecommendationKey((int) $riwayatId, (int) $pengajuan->id)] ?? null;
        }

        return null;
    }

    private function releaseQuota(?PenetapanDospembing $penetapan): void
    {
        if (! $penetapan) {
            return;
        }

        $penetapan->loadMissing(['dospem1', 'dospem2']);

        if ($penetapan->dospem1) {
            $penetapan->dospem1->decrement('kuota_terpakai_dospem1');
        }

        if ($penetapan->dospem2) {
            $penetapan->dospem2->decrement('kuota_terpakai_dospem2');
        }
    }

    private function consumeQuota(PenetapanDospembing $penetapan): void
    {
        if ($penetapan->dospem1_id) {
            Dosen::query()->whereKey($penetapan->dospem1_id)->increment('kuota_terpakai_dospem1');
        }

        if ($penetapan->dospem2_id) {
            Dosen::query()->whereKey($penetapan->dospem2_id)->increment('kuota_terpakai_dospem2');
        }
    }

    private function normalizeRow(mixed $row): array
    {
        $normalized = collect($row instanceof Collection ? $row->toArray() : (array) $row)
            ->mapWithKeys(function ($value, $key): array {
                $canonicalKey = Str::snake(trim((string) $key));

                if ($canonicalKey === 'rekomendasi_id') {
                    $canonicalKey = 'rekomendasi_dospembing_id';
                }

                if ($canonicalKey === 'final_dospem1_nidn') {
                    $canonicalKey = 'final_nidn_dospem_1';
                }

                if ($canonicalKey === 'final_dospem2_nidn') {
                    $canonicalKey = 'final_nidn_dospem_2';
                }

                if ($canonicalKey === 'nidn_dospem1') {
                    $canonicalKey = 'final_nidn_dospem_1';
                }

                if ($canonicalKey === 'nidn_dospem2') {
                    $canonicalKey = 'final_nidn_dospem_2';
                }

                return [$canonicalKey => is_string($value) ? trim($value) : $value];
            })
            ->all();

        if (isset($normalized['rekomendasi_id']) && ! isset($normalized['rekomendasi_dospembing_id'])) {
            $normalized['rekomendasi_dospembing_id'] = $normalized['rekomendasi_id'];
        }

        return $normalized;
    }

    private function ensureRequiredColumns(?array $firstRow): void
    {
        $requiredColumns = ['riwayat_rekomendasi_id', 'rekomendasi_dospembing_id', 'final_nidn_dospem_1', 'final_nidn_dospem_2'];

        if ($firstRow === null) {
            throw ValidationException::withMessages([
                'file' => ['File Excel tidak memiliki baris data.'],
            ]);
        }

        $missing = collect($requiredColumns)
            ->filter(fn (string $column) => ! array_key_exists($column, $firstRow))
            ->values();

        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages([
                'file' => ['Format Excel tidak sesuai. Kolom wajib tidak ditemukan: ' . $missing->implode(', ') . '.'],
            ]);
        }
    }

    private function readValue(array $row, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row)) {
                return $row[$key];
            }
        }

        return null;
    }

    private function buildRecommendationKey(int $riwayatId, int $pengajuanId): string
    {
        return $riwayatId . '|' . $pengajuanId;
    }

    private function normalizeCatatan(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' || $value === '-' ? null : $value;
    }
}