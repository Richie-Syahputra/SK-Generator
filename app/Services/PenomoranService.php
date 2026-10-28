<?php

namespace App\Services;

use App\Models\JenisSk;
use App\Models\PengaturanSekolah;
use App\Models\PenomoranTahun;
use App\Models\SkTerbit;
use Carbon\Carbon;

class PenomoranService
{
    public const FORMAT_DEFAULT = '{klasifikasi}/KEP.{nomor}–SMKM.3/DISDIKBUD/{bulan}/{tahun}';

    private const ROMAWI = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];

    public function ambilTahun(int $tahun): PenomoranTahun
    {
        return PenomoranTahun::firstOrCreate(['tahun' => $tahun], ['nomor_mulai' => 1]);
    }

    /** Nomor urut tertinggi yang masih ada pada urutan tahun ini (0 bila belum ada). */
    public function nomorTertinggi(int $tahun): int
    {
        $baris = $this->ambilTahun($tahun);

        $query = SkTerbit::whereYear('tanggal_sk', $tahun)->whereNotNull('nomor_urut');
        if ($baris->reset_at) {
            $query->where('created_at', '>=', $baris->reset_at);
        }

        return (int) ($query->max('nomor_urut') ?? 0);
    }

    /** Nomor urut yang akan dipakai surat berikutnya. */
    public function berikutnya(int $tahun): int
    {
        $baris = $this->ambilTahun($tahun);

        return max($this->nomorTertinggi($tahun), $baris->nomor_mulai - 1) + 1;
    }

    public function format(JenisSk $jenisSk, int $nomor, Carbon $tanggal): string
    {
        $pengaturan = PengaturanSekolah::ambil();
        $digit = max(1, (int) ($pengaturan->digit_nomor ?: 3));
        $format = $pengaturan->format_nomor ?: self::FORMAT_DEFAULT;

        return strtr($format, [
            '{klasifikasi}' => $jenisSk->kode_klasifikasi ?: '000',
            '{nomor}' => str_pad((string) $nomor, $digit, '0', STR_PAD_LEFT),
            '{bulan}' => self::ROMAWI[$tanggal->month],
            '{tahun}' => (string) $tanggal->year,
        ]);
    }

    /** @return array{0:int,1:string} [nomor urut, nomor surat lengkap] */
    public function buat(JenisSk $jenisSk, Carbon $tanggal): array
    {
        $nomor = $this->berikutnya($tanggal->year);

        return [$nomor, $this->format($jenisSk, $nomor, $tanggal)];
    }

    /** Atur manual: nomor berikutnya untuk tahun tersebut menjadi $mulaiDari. */
    public function atur(int $tahun, int $mulaiDari): void
    {
        $this->ambilTahun($tahun)->update([
            'nomor_mulai' => $mulaiDari,
            'reset_at' => now(),
        ]);
    }
}
