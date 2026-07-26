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
        'periode_akademik',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'is_active'       => 'boolean',
    ];

    // ==================== ACCESSORS ====================

    /**
     * Format label periode akademik, contoh: "2025 Ganjil" atau "2026 Genap".
     */
    public function getPeriodeAkademikLabelAttribute(): string
    {
        $code = (string) $this->periode_akademik;

        if (strlen($code) !== 5) {
            return $code;
        }

        $year = substr($code, 0, 4);
        $semester = match (substr($code, 4, 1)) {
            '1'     => 'Ganjil',
            '2'     => 'Genap',
            default => substr($code, 4, 1),
        };

        return "{$year} {$semester}";
    }

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

    /**
     * Pilihan periode akademik untuk select dropdown.
     *
     * @return array<string, string>
     */
    public static function periodeAkademikOptions(): array
    {
        $currentYear = (int) date('Y');

        $options = [];

        for ($year = $currentYear - 1; $year <= $currentYear + 1; $year++) {
            $options["{$year}1"] = "{$year} Ganjil";
            $options["{$year}2"] = "{$year} Genap";
        }

        return $options;
    }
}
