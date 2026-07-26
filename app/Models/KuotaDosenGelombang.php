<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KuotaDosenGelombang extends Model
{
    use HasFactory;

    protected $table = 'kuota_dosen_gelombangs';

    protected $fillable = [
        'dosen_id',
        'gelombang_id',
        'kuota_dospem1',
        'kuota_dospem2',
        'kuota_terpakai_dospem1',
        'kuota_terpakai_dospem2',
    ];

    protected $casts = [
        'kuota_dospem1' => 'integer',
        'kuota_dospem2' => 'integer',
        'kuota_terpakai_dospem1' => 'integer',
        'kuota_terpakai_dospem2' => 'integer',
    ];

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class);
    }

    public function gelombang(): BelongsTo
    {
        return $this->belongsTo(Gelombang::class);
    }

    public function remainingDospem1(): int
    {
        return max($this->kuota_dospem1 - $this->kuota_terpakai_dospem1, 0);
    }

    public function remainingDospem2(): int
    {
        return max($this->kuota_dospem2 - $this->kuota_terpakai_dospem2, 0);
    }
}
