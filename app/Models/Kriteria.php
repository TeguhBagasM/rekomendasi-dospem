<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kriteria extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode',
        'nama',
        'jenis',
        'bobot',
        'deskripsi',
        'aktif',
    ];

    protected $casts = [
        'bobot' => 'decimal:2',
        'aktif' => 'boolean',
    ];

    public function subKriterias(): HasMany
    {
        return $this->hasMany(SubKriteria::class);
    }

    public function scopeCore($query)
    {
        return $query->where('jenis', 'core');
    }

    public function scopeSecondary($query)
    {
        return $query->where('jenis', 'secondary');
    }
}