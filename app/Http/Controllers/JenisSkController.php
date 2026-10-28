<?php

namespace App\Http\Controllers;

use App\Models\JenisSk;
use Illuminate\Http\Request;

class JenisSkController extends Controller
{
    public function index()
    {
        $daftar = JenisSk::orderBy('nama')->get();

        return view('pengaturan.jenis-sk.index', compact('daftar'));
    }

    public function edit(JenisSk $jenisSk)
    {
        return view('pengaturan.jenis-sk.edit', compact('jenisSk'));
    }

    public function update(Request $request, JenisSk $jenisSk)
    {
        $data = $request->validate([
            'kode_klasifikasi' => 'nullable|string|max:50',
            'template_menimbang' => 'nullable|string',
            'template_mengingat' => 'nullable|string',
            'template_memperhatikan' => 'nullable|string',
            'template_diktum' => 'nullable|string',
        ]);

        $jenisSk->update($data);

        return redirect()->route('pengaturan.jenis-sk.index')
            ->with('status', 'Data jenis SK berhasil diperbarui.');
    }
}
