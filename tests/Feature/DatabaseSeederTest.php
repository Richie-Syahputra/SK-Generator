<?php

namespace Tests\Feature;

use App\Models\JenisSk;
use App\Models\PengaturanSekolah;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_instalasi_baru_terisi_lengkap(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(12, JenisSk::count());
        $this->assertSame('SMK Muhammadiyah 3 Banjarmasin', PengaturanSekolah::ambil()->nama_sekolah);

        // Seeder patch ikut berjalan: Memperhatikan terisi, typo "apabili" hilang,
        // opsi "Lainnya" pada alasan pemberhentian dihapus.
        $this->assertSame(12, JenisSk::whereNotNull('template_memperhatikan')->where('template_memperhatikan', '!=', '')->count());
        $this->assertSame(0, JenisSk::where('template_diktum', 'like', '%apabili%')->count());
        $pemberhentian = JenisSk::where('kode', 'PEMBERHENTIAN')->firstOrFail();
        $this->assertStringNotContainsString('Lainnya', json_encode($pemberhentian->field_schema));
    }

    public function test_seeder_tidak_menimpa_data_yang_sudah_diedit(): void
    {
        $this->seed(DatabaseSeeder::class);

        JenisSk::where('kode', 'PENGANGKATAN')->update(['template_menimbang' => 'EDITAN PENGGUNA']);
        PengaturanSekolah::ambil()->update(['nama_sekolah' => 'SMK Lain']);

        $this->seed(DatabaseSeeder::class);

        $this->assertSame(12, JenisSk::count());
        $this->assertSame('EDITAN PENGGUNA', JenisSk::where('kode', 'PENGANGKATAN')->value('template_menimbang'));
        $this->assertSame(1, PengaturanSekolah::count());
        $this->assertSame('SMK Lain', PengaturanSekolah::ambil()->nama_sekolah);
    }

    public function test_zona_waktu_aplikasi_wita(): void
    {
        $this->assertSame('Asia/Makassar', config('app.timezone'));
    }
}
