<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenetapanDospembing extends Model
{
    use HasFactory;

    protected $fillable = [
        'rekomendasi_dospembing_id',
        'pengajuan_id',
        'dospem1_id',
        'dospem2_id',
        'ditetapkan_oleh',
        'ditetapkan_at',
        'sumber',
        'catatan',
    ];

    protected $casts = [
        'ditetapkan_at' => 'datetime',
    ];

    public function rekomendasiDospembing(): BelongsTo
    {
        return $this->belongsTo(RekomendasiDospembing::class);
    }

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanJudul::class, 'pengajuan_id');
    }

    public function dospem1(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dospem1_id');
    }

    public function dospem2(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dospem2_id');
    }

    public function ditetapkanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditetapkan_oleh');
    }
}