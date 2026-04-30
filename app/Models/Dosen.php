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
        'program_studi',
        'kuota_bimbingan',
        'kuota_terisi',
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

    public function keahlian(): HasMany
    {
        return $this->hasMany(KeahlianDosen::class);
    }

    public function keahlianCore(): HasMany
    {
        return $this->hasMany(KeahlianDosen::class)->where('tipe_faktor', 1);
    }

    public function keahlianSecondary(): HasMany
    {
        return $this->hasMany(KeahlianDosen::class)->where('tipe_faktor', 2);
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
        return $this->kuota_terisi < $this->kuota_bimbingan;
    }

    public function sisaKuota(): int
    {
        return max(0, $this->kuota_bimbingan - $this->kuota_terisi);
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
}