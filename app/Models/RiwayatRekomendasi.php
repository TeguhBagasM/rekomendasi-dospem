<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RiwayatRekomendasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'generated_by',
        'generated_at',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function rekomendasiDospembings(): HasMany
    {
        return $this->hasMany(RekomendasiDospembing::class);
    }
}
