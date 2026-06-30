<?php

namespace App\Services;

use App\Models\PengajuanJudul;
use App\Models\User;
use App\Models\Mahasiswa;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PengajuanJudulService
{
    public function __construct(
        private readonly TextSimilarityService $textSimilarityService
    ) {}

    /**
     * Store a new PengajuanJudul.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Mahasiswa $mahasiswa, string $judulSkripsi, UploadedFile $fileProposal): PengajuanJudul
    {
        // 1. Check title similarity
        $similarityResult = $this->textSimilarityService->checkSimilarity($judulSkripsi);

        if ($similarityResult['is_similar']) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'judul_skripsi' => 'Judul memiliki kemiripan ' . round($similarityResult['percent'], 2) . '% dengan: "' . $similarityResult['matched_title'] . '". Gunakan judul lain.',
            ]);
        }

        // 2. Store file proposal
        $filePath = $this->storeProposalFile($fileProposal, $mahasiswa->nim, $mahasiswa->nama);

        // 3. Create record
        return PengajuanJudul::create([
            'mahasiswa_id' => $mahasiswa->id,
            'judul_skripsi' => $judulSkripsi,
            'file_proposal' => $filePath,
            'status' => 'pending',
        ]);
    }

    /**
     * Update an existing PengajuanJudul.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(PengajuanJudul $pengajuan, string $judulSkripsi, ?UploadedFile $fileProposal = null): void
    {
        // 1. Check similarity if title changes
        if (trim($pengajuan->judul_skripsi) !== trim($judulSkripsi)) {
            $similarityResult = $this->textSimilarityService->checkSimilarity($judulSkripsi, $pengajuan->id);

            if ($similarityResult['is_similar']) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'judul_skripsi' => 'Judul memiliki kemiripan ' . round($similarityResult['percent'], 2) . '% dengan: "' . $similarityResult['matched_title'] . '". Gunakan judul lain.',
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
        $safeName = Str::slug(trim($nim . ' ' . $nama), '_');
        $filename = $safeName . '.' . $file->getClientOriginalExtension();

        return $file->storeAs('proposal', $filename, 'public');
    }
}
