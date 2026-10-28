<?php

namespace Tests\Feature;

use App\Models\JenisSk;
use App\Models\LetterheadAsset;
use App\Models\PejabatPenandatangan;
use App\Models\SkTerbit;
use App\Services\SkDocumentGenerator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpsiSuratTest extends TestCase
{
    use RefreshDatabase;

    private function siap(array $opsi): array
    {
        $generator = new SkDocumentGenerator;
        $metode = new \ReflectionMethod($generator, 'siapkanOpsi');
        $metode->setAccessible(true);

        return $metode->invoke($generator, $opsi);
    }

    private function pejabat(string $nama, bool $aktif = false, ?string $jabatanJudul = null): PejabatPenandatangan
    {
        return PejabatPenandatangan::create([
            'nama_lengkap' => $nama,
            'jabatan' => 'Kepala Sekolah',
            'jabatan_judul' => $jabatanJudul,
            'label_id' => 'NUKS',
            'nomor_id' => '123',
            'is_active' => $aktif,
        ]);
    }

    public function test_penandatangan_default_adalah_yang_aktif(): void
    {
        $this->pejabat('Pertama');
        $this->pejabat('Kedua', true);

        $hasil = $this->siap([]);

        $this->assertSame('Kedua', $hasil['pejabat']['nama']);
    }

    public function test_pilih_penandatangan_lain_dari_daftar(): void
    {
        $lain = $this->pejabat('Lain');
        $this->pejabat('Aktif', true);

        $hasil = $this->siap(['penandatangan' => (string) $lain->id]);

        $this->assertSame('Lain', $hasil['pejabat']['nama']);
        $this->assertSame($lain->id, $hasil['pejabat']['id']);
    }

    public function test_jabatan_judul_memakai_isian_atau_jabatan(): void
    {
        $a = $this->pejabat('A', true, 'Kepala SMK Contoh');
        $b = $this->pejabat('B', false, null);

        $this->assertSame('Kepala SMK Contoh', $this->siap(['penandatangan' => (string) $a->id])['pejabat']['jabatan_judul']);
        $this->assertSame('Kepala Sekolah', $this->siap(['penandatangan' => (string) $b->id])['pejabat']['jabatan_judul']);
    }

    public function test_penandatangan_manual(): void
    {
        $hasil = $this->siap([
            'penandatangan' => 'manual',
            'manual' => ['nama' => 'Budi, S.Pd', 'jabatan' => 'Plt. Kepala Sekolah'],
        ]);

        $this->assertNull($hasil['pejabat']['id']);
        $this->assertSame('Budi, S.Pd', $hasil['pejabat']['nama']);
        $this->assertSame('Plt. Kepala Sekolah', $hasil['pejabat']['jabatan_judul']);
        $this->assertSame('', $hasil['pejabat']['nomor_id']);
    }

    public function test_penandatangan_manual_tanpa_nama_ditolak(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('wajib');

        $this->siap(['penandatangan' => 'manual', 'manual' => ['nama' => '', 'jabatan' => 'Plt']]);
    }

    public function test_tanpa_pejabat_sama_sekali_ditolak(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('belum diaktifkan');

        $this->siap([]);
    }

    public function test_mode_kop(): void
    {
        $this->pejabat('A', true);

        $tanpa = $this->siap(['kop' => 'tanpa']);
        $this->assertSame('tanpa', $tanpa['kop_mode']);
        $this->assertNull($tanpa['kop_path']);

        $kosong = $this->siap(['kop' => 'kosong']);
        $this->assertSame('kosong', $kosong['kop_mode']);
        $this->assertNull($kosong['kop_path']);

        // kop yang filenya tidak ada diperlakukan sebagai "tanpa kop"
        $kop = LetterheadAsset::create(['file_path' => 'letterhead/tidak-ada.png', 'is_active' => true]);
        $hilang = $this->siap(['kop' => (string) $kop->id]);
        $this->assertSame('tanpa', $hilang['kop_mode']);
        $this->assertNull($hilang['kop_id']);
    }

    public function test_tanggal_penetapan(): void
    {
        $this->pejabat('A', true);

        $hasil = $this->siap(['tanggal' => '2026-03-05']);
        $this->assertSame(2026, $hasil['tanggal']->year);
        $this->assertSame(3, $hasil['tanggal']->month);

        $this->assertTrue($this->siap([])['tanggal']->isSameDay(Carbon::today()));
    }

    public function test_tanggal_tidak_valid_ditolak(): void
    {
        $this->pejabat('A', true);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Tanggal');

        $this->siap(['tanggal' => 'bukan-tanggal']);
    }

    public function test_filter_penandatangan_di_riwayat(): void
    {
        $jenis = JenisSk::create(['kode' => 'AAA', 'nama' => 'SK AAA', 'kode_klasifikasi' => '421', 'field_schema' => []]);
        foreach ([[1, 'Budi'], [2, 'Siti']] as [$urut, $nama]) {
            SkTerbit::create([
                'jenis_sk_id' => $jenis->id,
                'nomor_surat' => sprintf('421/KEP.%03d/AAA', $urut),
                'nomor_urut' => $urut,
                'tanggal_sk' => '2026-02-10',
                'data_isian' => [],
                'penandatangan_nama' => $nama,
            ]);
        }

        $this->get(route('riwayat.index', ['penandatangan' => 'Budi']))
            ->assertOk()
            ->assertSee('421/KEP.001/AAA')
            ->assertDontSee('421/KEP.002/AAA');
    }
}
