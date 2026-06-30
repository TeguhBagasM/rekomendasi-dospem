<?php

namespace App\Services;

use App\Models\Dosen;
use Illuminate\Support\Facades\DB;

class ProfileDosenService
{
    public function __construct(
        private DosenProfileService $dosenProfileService
    ) {}

    /**
     * Update profil dosen yang login. Hanya menyentuh field yang memang
     * boleh diubah dosen sendiri: no_hp, bidang keahlian, dan profil
     * penelitian (topik_metode, judul_penelitian). Data administratif
     * (NIDN, nama, program studi, jabatan fungsional, pendidikan terakhir,
     * kuota) TIDAK disentuh di sini sama sekali — itu wewenang admin.
     *
     * @param  array{
     *     no_hp?: string|null,
     *     bidang_keahlian_ids?: array<int>,
     *     topik_metode?: string|null,
     *     judul_penelitian?: string|null,
     * } $data
     */
    public function update(Dosen $dosen, array $data): Dosen
    {
        DB::transaction(function () use ($dosen, $data) {
            $dosen->update([
                'no_hp' => $data['no_hp'] ?? null,
            ]);

            $dosen->bidangKeahlians()->sync($data['bidang_keahlian_ids'] ?? []);

            $dosen->profile()->updateOrCreate(
                ['dosen_id' => $dosen->id],
                [
                    'topik_metode'     => $data['topik_metode'] ?? null,
                    'judul_penelitian' => $data['judul_penelitian'] ?? null,
                ]
            );

            // Regenerate dokumen_tfidf untuk dosen ini.
            // CATATAN: ini hanya memperbarui dokumen mentah dosen ybs.
            // Re-kalkulasi skor IDF/cosine similarity terhadap seluruh
            // corpus dosen tetap perlu dijalankan ulang oleh admin sebelum
            // proses "Generate Rekomendasi", karena IDF bergantung pada
            // keseluruhan korpus dosen, bukan satu dokumen saja.
            $this->dosenProfileService->syncDokumenTfidf($dosen);
        });

        return $dosen->fresh(['profile', 'bidangKeahlians', 'programStudi']);
    }
}