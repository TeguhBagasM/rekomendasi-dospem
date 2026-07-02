<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PengajuanJudul extends Model
{
    use HasFactory;

    protected $fillable = [
        'mahasiswa_id',
        'judul_skripsi',
        'file_proposal',
        'status',
        'alasan_penolakan',
        'gelombang_id',
        'divalidasi_oleh',
        'validated_at',
    ];

    protected $casts = [
        'validated_at' => 'datetime',
    ];

    // ==================== RELATIONS ====================

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    /**
     * User (admin/kaprodi) yang memvalidasi pengajuan ini.
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'divalidasi_oleh');
    }

    public function rekomendasiDospembing()
    {
        return $this->hasOne(RekomendasiDospembing::class, 'pengajuan_id');
    }

    public function penetapanDospembing(): HasOne
    {
        return $this->hasOne(PenetapanDospembing::class, 'pengajuan_id');
    }

    public function gelombang(): BelongsTo
    {
        return $this->belongsTo(Gelombang::class);
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