<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BidangKeahlian extends Model
{
    protected $fillable = [
        'nama',
    ];

    public function dosens()
    {
        return $this->belongsToMany(
            Dosen::class,
            'dosen_bidang_keahlian'
        );
    }
}
