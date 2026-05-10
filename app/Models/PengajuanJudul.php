<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\User;
// use Illuminate\Database\Eloquent\Relations\HasOne;

class PengajuanJudul extends Model
{
    use HasFactory;

    protected $fillable = [
        'mahasiswa_id',
        'judul_skripsi',
        'file_proposal',
        'status',
        'alasan_penolakan',
        'divalidasi_oleh',
        'validated_at',
        'dospem1_id',
        'dospem2_id',
    ];

    protected $casts = [
        'validated_at' => 'datetime',
    ];

    // ==================== RELATIONS ====================

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function dospem1(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dospem1_id');
    }

    public function dospem2(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dospem2_id');
    }

    /**
     * User (admin/kaprodi) yang memvalidasi pengajuan ini.
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'divalidasi_oleh');
    }

    /**
     * Hasil rekomendasi dospem untuk pengajuan ini.
     */
    // public function hasilRekomendasi(): HasOne
    // {
    //     return $this->hasOne(HasilRekomendasi::class, 'pengajuan_id');
    // }

    // ==================== SCOPES ====================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDisetujui($query)
    {
        return $query->where('status', 'disetujui');
    }

    public function scopeDitolak($query)
    {
        return $query->where('status', 'ditolak');
    }

    // ==================== HELPERS ====================

    public function approve(User $validator): void
    {
        $this->update([
            'status'          => 'disetujui',
            'alasan_penolakan' => null,
            'divalidasi_oleh'  => $validator->id,
            'validated_at'     => now(),
        ]);
    }

    public function reject(User $validator, string $alasan): void
    {
        $this->update([
            'status'          => 'ditolak',
            'alasan_penolakan' => $alasan,
            'divalidasi_oleh'  => $validator->id,
            'validated_at'     => now(),
        ]);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isDisetujui(): bool
    {
        return $this->status === 'disetujui';
    }

    public function isDitolak(): bool
    {
        return $this->status === 'ditolak';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'warning',
            'disetujui' => 'success',
            'ditolak'   => 'danger',
            default     => 'secondary',
        };
    }
}