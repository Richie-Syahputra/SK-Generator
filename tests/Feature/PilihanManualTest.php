<?php

namespace Tests\Feature;

use App\Models\JenisSk;
use App\Services\SkDocumentGenerator;
use Database\Seeders\Tahap15PatchSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PilihanManualTest extends TestCase
{
    use RefreshDatabase;

    private function skema(): array
    {
        return [
            ['key' => 'nama', 'label' => 'Nama', 'type' => 'text'],
            ['key' => 'alasan', 'label' => 'Alasan', 'type' => 'select', 'options' => ['A', 'B']],
        ];
    }

    private function normalisasi(array $data): array
    {
        $generator = new SkDocumentGenerator;
        $metode = new \ReflectionMethod($generator, 'normalisasiPilihan');
        $metode->setAccessible(true);

        return $metode->invoke($generator, $this->skema(), $data);
    }

    public function test_pilihan_biasa_tidak_berubah(): void
    {
        $hasil = $this->normalisasi(['nama' => 'Budi', 'alasan' => 'B', 'alasan__manual' => '']);

        $this->assertSame(['nama' => 'Budi', 'alasan' => 'B'], $hasil);
    }

    public function test_opsi_manual_diganti_teks_yang_diketik(): void
    {
        $hasil = $this->normalisasi(['nama' => 'Budi', 'alasan' => '__manual__', 'alasan__manual' => '  Pensiun  ']);

        $this->assertSame('Pensiun', $hasil['alasan']);
        $this->assertArrayNotHasKey('alasan__manual', $hasil);
    }

    public function test_opsi_manual_kosong_ditolak(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('wajib diisi');

        $this->normalisasi(['nama' => 'Budi', 'alasan' => '__manual__', 'alasan__manual' => '   ']);
    }

    public function test_form_menampilkan_opsi_manual(): void
    {
        JenisSk::create([
            'kode' => 'UJI',
            'nama' => 'SK Uji',
            'kode_klasifikasi' => '421',
            'field_schema' => $this->skema(),
        ]);

        $this->get(route('sk.create', 'UJI'))
            ->assertOk()
            ->assertSee('value="__manual__"', false)
            ->assertSee('name="alasan__manual"', false);
    }

    public function test_seeder_menghapus_pilihan_lainnya(): void
    {
        JenisSk::create([
            'kode' => 'PEMBERHENTIAN',
            'nama' => 'SK Pemberhentian',
            'kode_klasifikasi' => '800',
            'field_schema' => [
                ['key' => 'alasan', 'label' => 'Alasan', 'type' => 'select', 'options' => ['Mutati', 'Lainnya']],
            ],
        ]);

        (new Tahap15PatchSeeder)->run();

        $opsi = JenisSk::where('kode', 'PEMBERHENTIAN')->first()->field_schema[0]['options'];
        $this->assertSame(['Mutati'], $opsi);
    }
}
