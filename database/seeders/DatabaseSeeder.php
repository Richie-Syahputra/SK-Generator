<?php

namespace Database\Seeders;

use App\Models\JenisSk;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Mengisi data awal untuk instalasi baru.
     *
     * Template jenis SK hanya diisi bila tabelnya masih kosong, supaya redaksi
     * yang sudah diedit pengguna tidak tertimpa (JenisSkSeeder memakai
     * updateOrCreate). Seeder patch dijalankan berurutan setelahnya.
     */
    public function run(): void
    {
        if (! JenisSk::query()->exists()) {
            $this->call([
                JenisSkSeeder::class,
                Tahap9aPatchSeeder::class,
                Tahap9bPatchSeeder::class,
                Tahap15PatchSeeder::class,
            ]);
        }

        $this->call(PengaturanAwalSeeder::class);
    }
}
