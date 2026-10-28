<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisSk extends Model
{
    protected $table = 'jenis_sk';

    protected $fillable = [
        'kode', 'nama', 'kode_klasifikasi', 'field_schema', 'is_custom',
        'template_menimbang', 'template_mengingat', 'template_memperhatikan',
        'template_diktum',
    ];

    protected $casts = [
        'field_schema' => 'array',
        'is_custom' => 'boolean',
    ];
}
