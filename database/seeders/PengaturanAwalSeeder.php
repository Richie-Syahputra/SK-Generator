<?php

namespace Database\Seeders;

use App\Models\PengaturanSekolah;
use Illuminate\Database\Seeder;

class PengaturanAwalSeeder extends Seeder
{
    /**
     * Pengaturan sekolah awal untuk instalasi baru. Tidak menyentuh pengaturan
     * yang sudah ada.
     */
    public function run(): void
    {
        if (PengaturanSekolah::query()->exists()) {
            return;
        }

        PengaturanSekolah::create([
            'nama_sekolah' => 'SMK Muhammadiyah 3 Banjarmasin',
            'tembusan_default' => "1. Yang bersangkutan\n2. Arsip",
        ]);
    }
}
