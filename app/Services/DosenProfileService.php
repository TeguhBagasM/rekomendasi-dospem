<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\DosenProfile;

class DosenProfileService
{
    /**
     * Hitung ulang dokumen_tfidf berdasarkan bidang keahlian + topik_metode + judul_penelitian,
     * lalu simpan ke tabel dosen_profiles.
     *
     * Panggil method ini setiap kali:
     * - profile dosen dibuat
     * - profile dosen diupdate
     * - bidang keahlian dosen disinkronkan (sync)
     */
    public function syncDokumenTfidf(Dosen $dosen): DosenProfile
    {
        // load() (bukan loadMissing) supaya relasi selalu fresh,
        // tidak memakai cache lama dari proses sync() sebelumnya.
        $dosen->load(['bidangKeahlians', 'profile']);

        $profile = $dosen->profile ?? $dosen->profile()->create([]);

        $profile->dokumen_tfidf = $this->generateDokumenTfidf($dosen, $profile);
        $profile->save();

        return $profile;
    }

    public function generateDokumenTfidf(Dosen $dosen, ?DosenProfile $profile = null): string
    {
        $profile = $profile ?? $dosen->profile;

        $bidangKeahlian = $dosen->bidangKeahlians
            ->pluck('nama')
            ->filter()
            ->implode(' ');

        $parts = [
            $bidangKeahlian,
            $profile?->topik_metode,
            $profile?->judul_penelitian,
        ];

        $dokumen = implode(' ', array_filter($parts, fn ($p) => filled($p)));

        // Rapikan spasi berlebih
        return trim(preg_replace('/\s+/', ' ', $dokumen));
    }
}