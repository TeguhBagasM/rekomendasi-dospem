<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nim',
        'nama',
        'program_studi_id',
        'angkatan',
        'no_hp',
    ];

    // ==================== RELATIONS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function penetapanDospembing(): HasManyThrough
    {
        return $this->hasManyThrough(
            PenetapanDospembing::class,
            PengajuanJudul::class,
            'mahasiswa_id',
            'pengajuan_id',
            'id',
            'id'
        );
    }

    public function pengajuanJuduls(): HasMany
    {
        return $this->hasMany(PengajuanJudul::class);
    }

    // ==================== HELPERS ====================

    public function sudahMengajukan(): bool
    {
        return $this->pengajuanJuduls()
                    ->whereIn('status', ['pending', 'disetujui'])
                    ->exists();
    }

    public function punyaPengajuanDisetujui(): bool
    {
        return $this->pengajuanJuduls()
            ->where('status', 'disetujui')
            ->exists();
    }

    public function punyaPengajuanPending(): bool
    {
        return $this->pengajuanJuduls()
            ->where('status', 'pending')
            ->exists();
    }

    /**
     * Mahasiswa boleh mengajukan baru selama tidak ada pengajuan
     * yang masih berstatus pending. Pengajuan disetujui ataupun
     * ditolak pada gelombang sebelumnya tidak menghalangi pengajuan baru.
     */
    public function bolehMengajukanBaru(): bool
    {
        return ! $this->punyaPengajuanPending();
    }

    public function sudahMemilikiPenetapanFinal(): bool
    {
        return $this->pengajuanJuduls()->whereHas('penetapanDospembing')->exists();
    }
}