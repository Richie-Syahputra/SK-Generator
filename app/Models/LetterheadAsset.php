<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LetterheadAsset extends Model
{
    protected $fillable = ['file_path', 'is_active', 'catatan'];

    public static function aktif()
    {
        return static::where('is_active', true)->first();
    }
}
