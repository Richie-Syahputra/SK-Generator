<?php

namespace App\Http\Controllers;

use App\Models\JenisSk;
use App\Models\PengaturanSekolah;
use App\Services\PenomoranService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PenomoranController extends Controller
{
    public function edit(Request $request, PenomoranService $layanan)
    {
        $tahun = (int) $request->input('tahun', now()->year);
        $pengaturan = PengaturanSekolah::ambil();
        $baris = $layanan->ambilTahun($tahun);
        $tertinggi = $layanan->nomorTertinggi($tahun);
        $berikutnya = $layanan->berikutnya($tahun);

        $contoh = $layanan->format(
            (new JenisSk)->forceFill(['kode_klasifikasi' => '421']),
            $berikutnya,
            Carbon::create($tahun, now()->month, 1)
        );

        return view('pengaturan.penomoran.edit', compact(
            'tahun', 'pengaturan', 'baris', 'tertinggi', 'berikutnya', 'contoh'
        ));
    }

    public function simpanFormat(Request $request)
    {
        $data = $request->validate([
            'format_nomor' => [
                'required', 'string', 'max:255',
                function ($atribut, $nilai, $gagal) {
                    if (! str_contains($nilai, '{nomor}')) {
                        $gagal('Format harus mengandung penanda {nomor}.');
                    }
                },
            ],
            'digit_nomor' => 'required|integer|min:1|max:6',
        ]);

        PengaturanSekolah::ambil()->update($data);

        return redirect()->route('pengaturan.penomoran.edit')
            ->with('status', 'Format nomor surat disimpan.');
    }

    public function atur(Request $request, PenomoranService $layanan)
    {
        $data = $request->validate([
            'tahun' => 'required|integer|min:2000|max:2100',
            'nomor_mulai' => 'required|integer|min:1|max:999999',
        ]);

        $layanan->atur((int) $data['tahun'], (int) $data['nomor_mulai']);

        return redirect()->route('pengaturan.penomoran.edit', ['tahun' => $data['tahun']])
            ->with('status', 'Nomor berikutnya untuk tahun '.$data['tahun'].' diatur menjadi '.$data['nomor_mulai'].'.');
    }
}
