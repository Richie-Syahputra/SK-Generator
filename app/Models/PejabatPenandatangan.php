<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PejabatPenandatangan extends Model
{
    protected $table = 'pejabat_penandatangan';

    protected $fillable = ['nama_lengkap', 'jabatan', 'jabatan_judul', 'label_id', 'nomor_id', 'is_active'];

    public static function aktif()
    {
        return static::where('is_active', true)->first();
    }
}
