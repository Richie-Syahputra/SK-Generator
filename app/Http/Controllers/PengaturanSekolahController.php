<?php

namespace App\Http\Controllers;

use App\Models\PengaturanSekolah;
use Illuminate\Http\Request;

class PengaturanSekolahController extends Controller
{
    public function edit()
    {
        $pengaturan = PengaturanSekolah::ambil();

        return view('pengaturan.sekolah.edit', compact('pengaturan'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'nama_sekolah' => 'required|string|max:255',
            'kota' => 'required|string|max:100',
            'tembusan_default' => 'nullable|string',
        ]);

        PengaturanSekolah::ambil()->update($data);

        return redirect()->route('pengaturan.sekolah.edit')
            ->with('status', 'Pengaturan sekolah berhasil disimpan.');
    }
}
