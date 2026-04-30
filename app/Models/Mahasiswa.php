<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\HasOne;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nim',
        'nama',
        'program_studi',
        'angkatan',
        'no_hp',
    ];

    // ==================== RELATIONS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // public function pengajuanJudul(): HasMany
    // {
    //     return $this->hasMany(PengajuanJudul::class);
    // }

    /**
     * Pengajuan judul aktif/terbaru mahasiswa ini.
     */
    // public function pengajuanAktif(): HasOne
    // {
    //     return $this->hasOne(PengajuanJudul::class)->latestOfMany();
    // }

    /**
     * Hasil rekomendasi dospem yang sudah diterima mahasiswa ini.
     */
    // public function hasilRekomendasi(): HasMany
    // {
    //     return $this->hasMany(HasilRekomendasi::class, 'pengajuan_id', 'id')
    //                 ->join('pengajuan_juduls', 'hasil_rekomendasis.pengajuan_id', '=', 'pengajuan_juduls.id')
    //                 ->where('pengajuan_juduls.mahasiswa_id', $this->id);
    // }

    // ==================== HELPERS ====================

    public function sudahMengajukan(): bool
    {
        return $this->pengajuanJudul()
                    ->whereIn('status', ['pending', 'disetujui'])
                    ->exists();
    }
}