<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dosen extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'program_studi_id',
        'nidn',
        'nama',
        'pendidikan_terakhir',
        'jabatan_fungsional',
        'kuota_dospem1',
        'kuota_dospem2',
        'kuota_terpakai_dospem1',
        'kuota_terpakai_dospem2',
        'no_hp',
    ];
    
    // ==================== RELATIONS ====================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function bidangKeahlians()
    {
        return $this->belongsToMany(
            BidangKeahlian::class,
            'dosen_bidang_keahlian'
        );
    }

    public function profile()
    {
        return $this->hasOne(DosenProfile::class);
    }
}