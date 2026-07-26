<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KeahlianDosen extends Model
{
    use HasFactory;

    protected $fillable = [
        'dosen_id',
        'bidang_keahlian',
        'deskripsi_keahlian',
        'tipe_faktor',
    ];

    protected $casts = [
        'tipe_faktor' => 'integer',
    ];

    // ==================== RELATIONS ====================

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class);
    }

    // ==================== HELPERS ====================

    public function isCoreFactor(): bool
    {
        return $this->tipe_faktor === 1;
    }

    public function isSecondaryFactor(): bool
    {
        return $this->tipe_faktor === 2;
    }

    public function getTipeLabelAttribute(): string
    {
        return $this->tipe_faktor === 1 ? 'Core Factor' : 'Secondary Factor';
    }
}
