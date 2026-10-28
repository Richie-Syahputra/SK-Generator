<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SkTerbit extends Model
{
    protected $table = 'sk_terbit';

    protected $fillable = [
        'jenis_sk_id', 'nomor_surat', 'nomor_urut', 'tanggal_sk',
        'data_isian', 'file_docx', 'file_pdf',
        'kop_mode', 'letterhead_asset_id',
        'penandatangan_id', 'penandatangan_nama', 'penandatangan_jabatan',
    ];

    protected $casts = [
        'data_isian' => 'array',
        'tanggal_sk' => 'date',
        'nomor_urut' => 'integer',
    ];

    public function jenisSk()
    {
        return $this->belongsTo(JenisSk::class);
    }
}
