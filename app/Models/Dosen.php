<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dosen extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nidn',
        'nama',
        'jabatan',
        'jabatan_fungsional',
        'pendidikan_terakhir',
        'program_studi',
        'kuota_bimbingan',
        'kuota_terisi',
        'kuota_dospem1',
        'kuota_dospem2',
        'no_hp',
    ];

    protected $casts = [
        'kuota_bimbingan' => 'integer',
        'kuota_terisi'    => 'integer',
    ];

    // ==================== RELATIONS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function keahlianDosens(): HasMany
    {
        return $this->hasMany(KeahlianDosen::class);
    }

    public function keahlian(): HasMany
    {
        return $this->keahlianDosens();
    }

    public function keahlianCore(): HasMany
    {
        return $this->keahlianDosens()->where('tipe_faktor', 1);
    }

    public function keahlianSecondary(): HasMany
    {
        return $this->keahlianDosens()->where('tipe_faktor', 2);
    }

    public function dospem1Pengajuan(): HasMany
    {
        return $this->hasMany(PengajuanJudul::class, 'dospem1_id');
    }

    public function dospem2Pengajuan(): HasMany
    {
        return $this->hasMany(PengajuanJudul::class, 'dospem2_id');
    }

    /**
     * Hasil rekomendasi di mana dosen ini menjadi Dospem 1.
     */
    // public function rekomendasiSebagaiDospem1(): HasMany
    // {
    //     return $this->hasMany(HasilRekomendasi::class, 'dospem1_id');
    // }

    /**
     * Hasil rekomendasi di mana dosen ini menjadi Dospem 2.
     */
    // public function rekomendasiSebagaiDospem2(): HasMany
    // {
    //     return $this->hasMany(HasilRekomendasi::class, 'dospem2_id');
    // }

    // ==================== HELPERS ====================

    public function masihAdaKuota(): bool
    {
        return $this->getRemainingKuotaDospem1() > 0 || $this->getRemainingKuotaDospem2() > 0;
    }

    public function sisaKuota(): int
    {
        return max($this->getRemainingKuotaDospem1(), $this->getRemainingKuotaDospem2());
    }

    public function tambahKuota(): void
    {
        $this->increment('kuota_terisi');
    }

    public function kurangiKuota(): void
    {
        if ($this->kuota_terisi > 0) {
            $this->decrement('kuota_terisi');
        }
    }

    /**
     * Gabungkan semua deskripsi keahlian menjadi satu string
     * untuk keperluan TF-IDF vectorization.
     */
    public function getDeskripsiLengkapAttribute(): string
    {
        return $this->keahlian
            ->pluck('deskripsi_keahlian')
            ->implode(' ');
    }

    public function getJabatanFungsionalAttribute(): ?string
    {
        return $this->attributes['jabatan_fungsional'] ?? $this->attributes['jabatan'] ?? null;
    }

    public function getPendidikanTerakhirAttribute(): ?string
    {
        return $this->attributes['pendidikan_terakhir'] ?? null;
    }

    public function getKuotaDospem1Attribute(): int
    {
        return (int) ($this->attributes['kuota_dospem1'] ?? $this->attributes['kuota_bimbingan'] ?? 0);
    }

    public function getKuotaDospem2Attribute(): int
    {
        return (int) ($this->attributes['kuota_dospem2'] ?? $this->attributes['kuota_bimbingan'] ?? 0);
    }

    public function getRemainingKuotaDospem1(): int
    {
        return max(0, $this->kuota_dospem1 - $this->countApprovedAssignments('dospem1Pengajuan'));
    }

    public function getRemainingKuotaDospem2(): int
    {
        return max(0, $this->kuota_dospem2 - $this->countApprovedAssignments('dospem2Pengajuan'));
    }

    private function countApprovedAssignments(string $relation): int
    {
        $query = $this->relationLoaded($relation)
            ? $this->getRelation($relation)
            : $this->{$relation}();

        if ($query instanceof HasMany) {
            return $query->where('status', 'disetujui')->count();
        }

        return $query->where('status', 'disetujui')->count();
    }
}