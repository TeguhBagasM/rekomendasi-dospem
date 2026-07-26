<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RekomendasiDospembing extends Model
{
    use HasFactory;

    protected $fillable = [
        'riwayat_rekomendasi_id',
        'pengajuan_id',
        'rekomendasi_dospem1_id',
        'rekomendasi_dospem2_id',
        'skor_dospem1',
        'skor_dospem2',
        'ringkasan',
    ];

    protected $casts = [
        'skor_dospem1' => 'decimal:4',
        'skor_dospem2' => 'decimal:4',
        'ringkasan' => 'array',
    ];

    public function riwayatRekomendasi(): BelongsTo
    {
        return $this->belongsTo(RiwayatRekomendasi::class);
    }

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanJudul::class, 'pengajuan_id');
    }

    public function rekomendasiDospem1(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'rekomendasi_dospem1_id');
    }

    public function rekomendasiDospem2(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'rekomendasi_dospem2_id');
    }

    public function hasilPerhitungans(): HasMany
    {
        return $this->hasMany(HasilPerhitungan::class);
    }

    public function penetapanDospembing(): HasOne
    {
        return $this->hasOne(PenetapanDospembing::class);
    }
}
