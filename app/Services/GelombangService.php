<?php

namespace App\Services;

use App\Models\Gelombang;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GelombangService
{
    /**
     * Simpan gelombang baru. Jika diaktifkan, nonaktifkan gelombang lain.
     */
    public function create(array $data): Gelombang
    {
        return DB::transaction(function () use ($data) {
            $isActive = (bool) ($data['is_active'] ?? false);

            if ($isActive) {
                Gelombang::query()->update(['is_active' => false]);
            }

            return Gelombang::create([
                'nama'             => $data['nama'],
                'tanggal_mulai'    => $data['tanggal_mulai'],
                'tanggal_selesai'  => $data['tanggal_selesai'],
                'is_active'        => $isActive,
            ]);
        });
    }

    /**
     * Update gelombang. Jika diaktifkan, nonaktifkan gelombang lain.
     */
    public function update(Gelombang $gelombang, array $data): Gelombang
    {
        return DB::transaction(function () use ($gelombang, $data) {
            $isActive = (bool) ($data['is_active'] ?? false);

            if ($isActive) {
                Gelombang::query()->where('id', '!=', $gelombang->id)->update(['is_active' => false]);
            }

            $gelombang->update([
                'nama'             => $data['nama'],
                'tanggal_mulai'    => $data['tanggal_mulai'],
                'tanggal_selesai'  => $data['tanggal_selesai'],
                'is_active'        => $isActive,
            ]);

            return $gelombang;
        });
    }

    /**
     * Toggle status aktif satu gelombang via switch.
     * Hanya boleh ada satu gelombang aktif dalam satu waktu.
     */
    public function toggleStatus(Gelombang $gelombang): Gelombang
    {
        return DB::transaction(function () use ($gelombang) {
            $newStatus = ! $gelombang->is_active;

            if ($newStatus) {
                Gelombang::query()->where('id', '!=', $gelombang->id)->update(['is_active' => false]);
            }

            $gelombang->update(['is_active' => $newStatus]);

            return $gelombang->fresh();
        });
    }

    public function delete(Gelombang $gelombang): void
    {
        $gelombang->delete();
    }

    /**
     * Ambil gelombang yang benar-benar terbuka untuk pengajuan saat ini.
     * Syarat: is_active = true, dan hari ini berada di antara tanggal_mulai - tanggal_selesai.
     */
    public function getOpenGelombang(): ?Gelombang
    {
        $today = Carbon::today();
 
        return Gelombang::query()
            ->where('is_active', true)
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today)
            ->first();
    }
 
    /**
     * Apakah saat ini ada gelombang yang terbuka untuk pengajuan.
     */
    public function isOpen(): bool
    {
        return $this->getOpenGelombang() !== null;
    }
}