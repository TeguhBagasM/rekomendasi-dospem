<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DosenProfile extends Model
{
    protected $fillable = [
        'dosen_id',
        'topik_metode',
        'judul_penelitian',
        'dokumen_tfidf',
    ];

    public function dosen()
    {
        return $this->belongsTo(Dosen::class);
    }
}
