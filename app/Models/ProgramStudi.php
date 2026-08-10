<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramStudi extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_studi',
        'kode_prodi',
        'jenjang',
        'kaprodi',
    ];

    public function dosens()
    {
        return $this->hasMany(Dosen::class);
    }
}
