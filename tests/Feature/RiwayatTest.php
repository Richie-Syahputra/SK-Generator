<?php

namespace Tests\Feature;

use App\Models\JenisSk;
use App\Models\SkTerbit;
use App\Services\PenomoranService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RiwayatTest extends TestCase
{
    use RefreshDatabase;

    private function jenis(string $kode): JenisSk
    {
        return JenisSk::create([
            'kode' => $kode,
            'nama' => 'SK '.$kode,
            'kode_klasifikasi' => '421',
            'field_schema' => [],
        ]);
    }

    private function surat(JenisSk $jenis, int $urut, string $tanggal, array $isi = [], ?string $docx = null, ?string $pdf = null): SkTerbit
    {
        return SkTerbit::create([
            'jenis_sk_id' => $jenis->id,
            'nomor_surat' => sprintf('421/KEP.%03d/%s', $urut, $jenis->kode),
            'nomor_urut' => $urut,
            'tanggal_sk' => $tanggal,
            'data_isian' => $isi,
            'file_docx' => $docx,
            'file_pdf' => $pdf,
        ]);
    }

    public function test_filter_jenis(): void
    {
        $a = $this->jenis('AAA');
        $b = $this->jenis('BBB');
        $this->surat($a, 1, '2026-01-10');
        $this->surat($b, 2, '2026-02-10');

        $this->get(route('riwayat.index', ['jenis_sk_id' => $a->id]))
            ->assertOk()
            ->assertSee('421/KEP.001/AAA')
            ->assertDontSee('421/KEP.002/BBB');
    }

    public function test_filter_tahun_dan_bulan(): void
    {
        $a = $this->jenis('AAA');
        $this->surat($a, 1, '2025-12-31');
        $this->surat($a, 2, '2026-02-10');

        $this->get(route('riwayat.index', ['tahun' => 2026]))
            ->assertSee('421/KEP.002/AAA')->assertDontSee('421/KEP.001/AAA');

        $this->get(route('riwayat.index', ['bulan' => 12]))
            ->assertSee('421/KEP.001/AAA')->assertDontSee('421/KEP.002/AAA');
    }

    public function test_filter_rentang_tanggal(): void
    {
        $a = $this->jenis('AAA');
        $this->surat($a, 1, '2026-01-15');
        $this->surat($a, 2, '2026-02-15');
        $this->surat($a, 3, '2026-03-15');

        $this->get(route('riwayat.index', ['dari' => '2026-02-01', 'sampai' => '2026-02-28']))
            ->assertSee('421/KEP.002/AAA')
            ->assertDontSee('421/KEP.001/AAA')
            ->assertDontSee('421/KEP.003/AAA');
    }

    public function test_kata_kunci_mencari_nomor_dan_isi(): void
    {
        $a = $this->jenis('AAA');
        $this->surat($a, 1, '2026-02-10', ['nama_pegawai' => 'Budi Santoso']);
        $this->surat($a, 2, '2026-02-11', ['nama_pegawai' => 'Siti Aminah']);

        $this->get(route('riwayat.index', ['cari' => 'Budi']))
            ->assertSee('421/KEP.001/AAA')->assertDontSee('421/KEP.002/AAA');

        $this->get(route('riwayat.index', ['cari' => 'KEP.002']))
            ->assertSee('421/KEP.002/AAA')->assertDontSee('421/KEP.001/AAA');
    }

    public function test_hapus_surat_menghapus_file_dan_nomor_tertinggi_dipakai_ulang(): void
    {
        Storage::fake('public');
        $a = $this->jenis('AAA');
        $this->surat($a, 1, '2026-02-10');
        Storage::disk('public')->put('sk-output/x.docx', 'x');
        Storage::disk('public')->put('sk-output/x.pdf', 'x');
        $tertinggi = $this->surat($a, 2, '2026-02-11', [], 'sk-output/x.docx', 'sk-output/x.pdf');

        $this->delete(route('riwayat.destroy', $tertinggi))->assertRedirect();

        $this->assertDatabaseMissing('sk_terbit', ['id' => $tertinggi->id]);
        Storage::disk('public')->assertMissing('sk-output/x.docx');
        Storage::disk('public')->assertMissing('sk-output/x.pdf');
        $this->assertSame(2, (new PenomoranService)->berikutnya(2026));
    }

    public function test_hapus_di_tengah_tidak_mengubah_nomor_berikutnya(): void
    {
        Storage::fake('public');
        $a = $this->jenis('AAA');
        $this->surat($a, 1, '2026-02-10');
        $tengah = $this->surat($a, 2, '2026-02-11');
        $this->surat($a, 3, '2026-02-12');

        $this->delete(route('riwayat.destroy', $tengah));

        $this->assertSame(4, (new PenomoranService)->berikutnya(2026));
    }

    public function test_hapus_terpilih(): void
    {
        Storage::fake('public');
        $a = $this->jenis('AAA');
        $s1 = $this->surat($a, 1, '2026-02-10');
        $s2 = $this->surat($a, 2, '2026-02-11');
        $s3 = $this->surat($a, 3, '2026-02-12');

        $this->post(route('riwayat.hapus-terpilih'), ['ids' => [$s1->id, $s2->id]])->assertRedirect();

        $this->assertDatabaseMissing('sk_terbit', ['id' => $s1->id]);
        $this->assertDatabaseMissing('sk_terbit', ['id' => $s2->id]);
        $this->assertDatabaseHas('sk_terbit', ['id' => $s3->id]);
    }
}
