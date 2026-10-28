<?php

namespace Tests\Feature;

use App\Models\JenisSk;
use App\Models\SkTerbit;
use App\Services\PenomoranService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenomoranTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function jenis(string $kode, string $klasifikasi): JenisSk
    {
        return JenisSk::create([
            'kode' => $kode,
            'nama' => 'SK '.$kode,
            'kode_klasifikasi' => $klasifikasi,
            'field_schema' => [],
        ]);
    }

    private function terbitkan(JenisSk $jenis, string $tanggal): SkTerbit
    {
        [$urut, $nomor] = (new PenomoranService)->buat($jenis, Carbon::parse($tanggal));

        return SkTerbit::create([
            'jenis_sk_id' => $jenis->id,
            'nomor_surat' => $nomor,
            'nomor_urut' => $urut,
            'tanggal_sk' => $tanggal,
            'data_isian' => [],
        ]);
    }

    public function test_satu_urutan_untuk_semua_jenis(): void
    {
        Carbon::setTestNow('2026-03-01 10:00:00');
        $a = $this->jenis('A', '421');
        $b = $this->jenis('B', '800');

        $this->assertSame(1, $this->terbitkan($a, '2026-03-01')->nomor_urut);
        $this->assertSame(2, $this->terbitkan($b, '2026-03-01')->nomor_urut);
        $this->assertSame(3, $this->terbitkan($a, '2026-03-01')->nomor_urut);
    }

    public function test_hapus_di_tengah_tidak_mengubah_nomor_berikutnya(): void
    {
        Carbon::setTestNow('2026-03-01 10:00:00');
        $a = $this->jenis('A', '421');
        $this->terbitkan($a, '2026-03-01');
        $tengah = $this->terbitkan($a, '2026-03-01');
        $this->terbitkan($a, '2026-03-01');

        $tengah->delete();

        $this->assertSame(4, (new PenomoranService)->berikutnya(2026));
    }

    public function test_hapus_nomor_tertinggi_dipakai_ulang(): void
    {
        Carbon::setTestNow('2026-03-01 10:00:00');
        $a = $this->jenis('A', '421');
        $this->terbitkan($a, '2026-03-01');
        $this->terbitkan($a, '2026-03-01');
        $tertinggi = $this->terbitkan($a, '2026-03-01');

        $tertinggi->delete();

        $this->assertSame(3, (new PenomoranService)->berikutnya(2026));
    }

    public function test_tahun_baru_mulai_dari_satu(): void
    {
        Carbon::setTestNow('2026-12-30 10:00:00');
        $a = $this->jenis('A', '421');
        $this->terbitkan($a, '2026-12-30');
        $this->terbitkan($a, '2026-12-30');

        $layanan = new PenomoranService;
        $this->assertSame(3, $layanan->berikutnya(2026));
        $this->assertSame(1, $layanan->berikutnya(2027));
    }

    public function test_atur_manual_ke_angka_lebih_besar(): void
    {
        Carbon::setTestNow('2026-03-01 10:00:00');
        $a = $this->jenis('A', '421');
        $layanan = new PenomoranService;

        $layanan->atur(2026, 36);
        $this->assertSame(36, $layanan->berikutnya(2026));

        Carbon::setTestNow('2026-03-01 10:05:00');
        $this->assertSame(36, $this->terbitkan($a, '2026-03-01')->nomor_urut);
        $this->assertSame(37, $layanan->berikutnya(2026));
    }

    public function test_atur_manual_ke_satu_mengabaikan_surat_lama(): void
    {
        Carbon::setTestNow('2026-03-01 10:00:00');
        $a = $this->jenis('A', '421');
        $this->terbitkan($a, '2026-03-01');
        $this->terbitkan($a, '2026-03-01');
        $this->terbitkan($a, '2026-03-01');

        Carbon::setTestNow('2026-03-01 10:30:00');
        $layanan = new PenomoranService;
        $layanan->atur(2026, 1);
        $this->assertSame(1, $layanan->berikutnya(2026));

        Carbon::setTestNow('2026-03-01 10:35:00');
        $this->assertSame(1, $this->terbitkan($a, '2026-03-01')->nomor_urut);
        $this->assertSame(2, $layanan->berikutnya(2026));
    }

    public function test_format_nomor_memakai_en_dash_dan_digit(): void
    {
        $a = $this->jenis('A', '421');

        $nomor = (new PenomoranService)->format($a, 35, Carbon::parse('2026-07-10'));

        $this->assertSame('421/KEP.035–SMKM.3/DISDIKBUD/VII/2026', $nomor);
    }
}
