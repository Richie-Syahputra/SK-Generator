<?php

namespace Database\Seeders;

use App\Models\JenisSk;
use Illuminate\Database\Seeder;

class Tahap9aPatchSeeder extends Seeder
{
    public function run(): void
    {
        // Isi Memperhatikan untuk 2 jenis contoh (yang lain menyusul Tahap 9b)
        JenisSk::where('kode', 'PENGANGKATAN')->update([
            'template_memperhatikan' => "1. Hasil rapat dewan guru tentang pengangkatan pegawai;\n2. Kebutuhan organisasi sekolah saat ini.",
        ]);

        JenisSk::where('kode', 'PEMBENTUKAN_TIM')->update([
            'template_memperhatikan' => "1. Hasil rapat dewan guru tentang pembentukan tim/panitia;\n2. Kebutuhan pelaksanaan kegiatan sekolah.",
        ]);

        // Perbaiki typo "apabili" -> "apabila" di semua jenis SK yang terlanjur salah
        JenisSk::all()->each(function (JenisSk $jenis) {
            if ($jenis->template_diktum && str_contains($jenis->template_diktum, 'apabili')) {
                $jenis->template_diktum = str_replace('apabili', 'apabila', $jenis->template_diktum);
                $jenis->save();
            }
        });
    }
}
