<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Gelombang extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'is_active'       => 'boolean',
    ];

    // ==================== RELATIONS ====================

    public function pengajuanJuduls(): HasMany
    {
        return $this->hasMany(PengajuanJudul::class);
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope gelombang yang benar-benar terbuka: aktif & dalam rentang tanggal.
     */
    public function scopeCurrentlyOpen($query)
    {
        $today = Carbon::today();

        return $query->where('is_active', true)
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today);
    }

    // ==================== HELPERS ====================

    public static function activeGelombang(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Apakah gelombang ini sedang terbuka untuk pengajuan hari ini.
     */
    public function isOpenForSubmission(): bool
    {
        $today = Carbon::today();

        return $this->is_active
            && $this->tanggal_mulai?->lte($today)
            && $this->tanggal_selesai?->gte($today);
    }
}