<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HasilPerhitungan extends Model
{
    use HasFactory;

    protected $fillable = [
        'rekomendasi_dospembing_id',
        'dosen_id',
        'jenis_dospem',
        'topic_similarity',
        'topic_scale',
        'quota_scale',
        'jabatan_scale',
        'pendidikan_scale',
        'cf_score',
        'sf_score',
        'final_score',
        'ranking',
        'detail',
    ];

    protected $casts = [
        'topic_similarity' => 'decimal:4',
        'topic_scale' => 'integer',
        'quota_scale' => 'integer',
        'jabatan_scale' => 'integer',
        'pendidikan_scale' => 'integer',
        'cf_score' => 'decimal:4',
        'sf_score' => 'decimal:4',
        'final_score' => 'decimal:4',
        'ranking' => 'integer',
        'detail' => 'array',
    ];

    public function rekomendasiDospembing(): BelongsTo
    {
        return $this->belongsTo(RekomendasiDospembing::class);
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class);
    }
}
