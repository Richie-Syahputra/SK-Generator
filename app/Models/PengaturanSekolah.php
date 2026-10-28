<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanSekolah extends Model
{
    protected $table = 'pengaturan_sekolah';

    protected $fillable = [
        'nama_sekolah', 'kota', 'singkatan_nomor', 'tembusan_default',
        'format_nomor', 'digit_nomor',
    ];

    public static function ambil(): self
    {
        return static::first() ?? static::create([
            'tembusan_default' => "1. Yang bersangkutan\n2. Arsip",
        ]);
    }
}
