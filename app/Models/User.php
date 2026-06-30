<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function mahasiswa(): HasOne
    {
        return $this->hasOne(Mahasiswa::class);
    }

     public function dosen(): HasOne
    {
        return $this->hasOne(Dosen::class);
    }
 
    public function isMahasiswa(): bool
    {
        return $this->hasRole('mahasiswa');
    }
 
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }
 
    public function isDosen(): bool
    {
        return $this->hasRole('dosen');
    }

        /**
     * Pengajuan judul yang divalidasi oleh user ini (admin/kaprodi).
     */
    // public function pengajuanDivalidasi(): HasMany
    // {
    //     return $this->hasMany(PengajuanJudul::class, 'divalidasi_oleh');
    // }
 
    // /**
    //  * Batch rekomendasi yang dibuat oleh user ini (admin/kaprodi).
    //  */
    // public function batchRekomendasi(): HasMany
    // {
    //     return $this->hasMany(BatchRekomendasi::class, 'admin_id');
    // }
 
    // ==================== HELPERS ====================
}
