<?php

namespace App\Http\Controllers;

use App\Models\PejabatPenandatangan;
use Illuminate\Http\Request;

class PejabatController extends Controller
{
    private function aturan(): array
    {
        return [
            'nama_lengkap' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'jabatan_judul' => 'nullable|string|max:255',
            'label_id' => 'nullable|string|max:50',
            'nomor_id' => 'nullable|string|max:100',
        ];
    }

    public function index()
    {
        $daftar = PejabatPenandatangan::orderByDesc('is_active')->orderBy('nama_lengkap')->get();
        return view('pengaturan.pejabat.index', compact('daftar'));
    }

    public function create()
    {
        return view('pengaturan.pejabat.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->aturan());

        PejabatPenandatangan::create($data + ['is_active' => false]);

        return redirect()->route('pengaturan.pejabat.index')
            ->with('status', 'Pejabat baru berhasil ditambahkan.');
    }

    public function edit(PejabatPenandatangan $pejabat)
    {
        return view('pengaturan.pejabat.edit', compact('pejabat'));
    }

    public function update(Request $request, PejabatPenandatangan $pejabat)
    {
        $data = $request->validate($this->aturan());

        $pejabat->update($data);

        return redirect()->route('pengaturan.pejabat.index')
            ->with('status', 'Data pejabat berhasil diperbarui.');
    }

    public function destroy(PejabatPenandatangan $pejabat)
    {
        $pejabat->delete();

        return redirect()->route('pengaturan.pejabat.index')
            ->with('status', 'Pejabat berhasil dihapus.');
    }

    public function aktifkan(PejabatPenandatangan $pejabat)
    {
        PejabatPenandatangan::where('is_active', true)->update(['is_active' => false]);
        $pejabat->update(['is_active' => true]);

        return redirect()->route('pengaturan.pejabat.index')
            ->with('status', 'Pejabat berhasil diaktifkan sebagai penandatangan.');
    }
}
