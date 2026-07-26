<?php

namespace App\Services;

use App\Models\Gelombang;
use App\Models\Mahasiswa;
use App\Models\PengajuanJudul;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PengajuanJudulService
{
    public function __construct(
        private readonly TextSimilarityService $textSimilarityService
    ) {}

    /**
     * Store a new PengajuanJudul.
     *
     * @throws ValidationException
     */
    public function create(Mahasiswa $mahasiswa, Gelombang $gelombang, string $judulSkripsi, UploadedFile $fileProposal): PengajuanJudul
    {
        // 1. Check title similarity
        $similarityResult = $this->textSimilarityService->checkSimilarity($judulSkripsi);
        if ($similarityResult['is_similar']) {
            throw ValidationException::withMessages([
                'judul_skripsi' => 'Judul memiliki kemiripan '.round($similarityResult['percent'], 2).'% dengan: "'.$similarityResult['matched_title'].'". Gunakan judul lain.',
            ]);
        }

        // 2. Store file proposal
        $filePath = $this->storeProposalFile($fileProposal, $mahasiswa->nim, $mahasiswa->nama);

        // 3. Create record
        return PengajuanJudul::create([
            'mahasiswa_id' => $mahasiswa->id,
            'gelombang_id' => $gelombang->id,
            'judul_skripsi' => $judulSkripsi,
            'file_proposal' => $filePath,
            'status' => 'pending',
        ]);
    }

    /**
     * Update an existing PengajuanJudul.
     *
     * @throws ValidationException
     */
    public function update(PengajuanJudul $pengajuan, string $judulSkripsi, ?UploadedFile $fileProposal = null): void
    {
        // 1. Check similarity if title changes
        if (trim($pengajuan->judul_skripsi) !== trim($judulSkripsi)) {
            $similarityResult = $this->textSimilarityService->checkSimilarity($judulSkripsi, $pengajuan->id);
            if ($similarityResult['is_similar']) {
                throw ValidationException::withMessages([
                    'judul_skripsi' => 'Judul memiliki kemiripan '.round($similarityResult['percent'], 2).'% dengan: "'.$similarityResult['matched_title'].'". Gunakan judul lain.',
                ]);
            }
        }

        $data = [
            'judul_skripsi' => $judulSkripsi,
        ];

        // 2. Handle file proposal update
        if ($fileProposal) {
            if ($pengajuan->file_proposal) {
                Storage::disk('public')->delete($pengajuan->file_proposal);
            }
            $data['file_proposal'] = $this->storeProposalFile(
                $fileProposal,
                $pengajuan->mahasiswa?->nim ?? '',
                $pengajuan->mahasiswa?->nama ?? 'proposal'
            );
        }

        $pengajuan->update($data);
    }

    /**
     * Delete a PengajuanJudul and its file.
     */
    public function delete(PengajuanJudul $pengajuan): void
    {
        if ($pengajuan->file_proposal) {
            Storage::disk('public')->delete($pengajuan->file_proposal);
        }

        $pengajuan->delete();
    }

    /**
     * Approve a PengajuanJudul.
     */
    public function approve(PengajuanJudul $pengajuan, User $validator): void
    {
        $pengajuan->approve($validator);
    }

    /**
     * Approve seluruh PengajuanJudul yang masih berstatus pending sekaligus.
     * Hanya baris yang benar-benar masih pending yang diproses (menghindari
     * race condition jika status berubah di antara fetch dan eksekusi).
     *
     * Return jumlah pengajuan yang berhasil disetujui.
     */
    public function approveAll(Collection $pengajuans, User $validator): int
    {
        $approvedCount = 0;

        DB::transaction(function () use ($pengajuans, $validator, &$approvedCount): void {
            foreach ($pengajuans as $pengajuan) {
                if (! $pengajuan->isPending()) {
                    continue;
                }

                $pengajuan->approve($validator);
                $approvedCount++;
            }
        });

        return $approvedCount;
    }

    /**
     * Reject a PengajuanJudul.
     */
    public function reject(PengajuanJudul $pengajuan, User $validator, string $alasan): void
    {
        $pengajuan->reject($validator, $alasan);
    }

    /**
     * Helper to store proposal file.
     */
    private function storeProposalFile(UploadedFile $file, string $nim, string $nama): string
    {
        $safeName = Str::slug(trim($nim.' '.$nama), '_');
        $filename = $safeName.'.'.$file->getClientOriginalExtension();

        return $file->storeAs('proposal', $filename, 'public');
    }
}
