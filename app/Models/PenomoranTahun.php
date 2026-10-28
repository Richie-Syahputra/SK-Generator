<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenomoranTahun extends Model
{
    protected $table = 'penomoran_tahun';

    protected $fillable = ['tahun', 'nomor_mulai', 'reset_at'];

    protected $casts = [
        'reset_at' => 'datetime',
    ];
}
