<?php

namespace App\Http\Controllers;

use App\Models\JenisSk;
use App\Models\SkTerbit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RiwayatController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'cari' => 'nullable|string|max:100',
            'jenis_sk_id' => 'nullable|integer',
            'tahun' => 'nullable|integer',
            'bulan' => 'nullable|integer|between:1,12',
            'dari' => 'nullable|date',
            'sampai' => 'nullable|date',
            'urut' => 'nullable|in:terbaru,nomor_terbesar,nomor_terkecil',
            'penandatangan' => 'nullable|string|max:255',
        ]);

        $query = SkTerbit::with('jenisSk');

        if ($request->filled('cari')) {
            $kata = trim($request->input('cari'));
            $query->where(function ($q) use ($kata) {
                $q->where('nomor_surat', 'like', '%'.$kata.'%')
                    ->orWhere('data_isian', 'like', '%'.$kata.'%');
            });
        }
        if ($request->filled('jenis_sk_id')) {
            $query->where('jenis_sk_id', $request->input('jenis_sk_id'));
        }
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_sk', (int) $request->input('tahun'));
        }
        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal_sk', (int) $request->input('bulan'));
        }
        if ($request->filled('dari')) {
            $query->whereDate('tanggal_sk', '>=', $request->input('dari'));
        }
        if ($request->filled('sampai')) {
            $query->whereDate('tanggal_sk', '<=', $request->input('sampai'));
        }

        if ($request->filled('penandatangan')) {
            $query->where('penandatangan_nama', $request->input('penandatangan'));
        }

        match ($request->input('urut', 'terbaru')) {
            'nomor_terbesar' => $query->orderByDesc('tanggal_sk')->orderByDesc('nomor_urut')->orderByDesc('id'),
            'nomor_terkecil' => $query->orderBy('tanggal_sk')->orderBy('nomor_urut')->orderBy('id'),
            default => $query->orderByDesc('created_at')->orderByDesc('id'),
        };

        $daftar = $query->paginate(15)->withQueryString();
        $jenisSkList = JenisSk::orderBy('nama')->get();
        $daftarTahun = SkTerbit::selectRaw("strftime('%Y', tanggal_sk) as tahun")
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun');

        $daftarPenandatangan = SkTerbit::whereNotNull('penandatangan_nama')
            ->distinct()
            ->orderBy('penandatangan_nama')
            ->pluck('penandatangan_nama');

        return view('riwayat.index', compact('daftar', 'jenisSkList', 'daftarTahun', 'daftarPenandatangan'));
    }

    public function destroy(SkTerbit $skTerbit)
    {
        $nomor = $skTerbit->nomor_surat;
        $gagal = $this->hapusSurat($skTerbit);

        return $this->balasan('Surat '.$nomor.' dihapus.', $gagal);
    }

    public function hapusTerpilih(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        $gagal = 0;
        $jumlah = 0;
        foreach (SkTerbit::whereIn('id', $data['ids'])->get() as $surat) {
            $gagal += $this->hapusSurat($surat);
            $jumlah++;
        }

        return $this->balasan($jumlah.' surat dihapus.', $gagal);
    }

    /** Hapus file Word/PDF lalu barisnya. Mengembalikan jumlah file yang GAGAL dihapus. */
    private function hapusSurat(SkTerbit $surat): int
    {
        $disk = Storage::disk('public');
        $gagal = 0;

        foreach ([$surat->file_docx, $surat->file_pdf] as $path) {
            if ($path && $disk->exists($path) && ! $disk->delete($path)) {
                $gagal++;
            }
        }
        $surat->delete();

        return $gagal;
    }

    private function balasan(string $pesan, int $gagal)
    {
        $balik = redirect()->back()->with('status', $pesan);

        if ($gagal > 0) {
            $balik->withErrors(['file' => $gagal.' file Word/PDF tidak bisa dihapus (mungkin sedang dibuka di Word). Tutup filenya lalu hapus manual dari folder storage\app\public\sk-output.']);
        }

        return $balik;
    }
}
