<?php

namespace App\Http\Controllers;

use App\Models\JenisSk;
use App\Services\SkDocumentGenerator;
use Illuminate\Http\Request;

class SkCustomController extends Controller
{
    public function create()
    {
        return view('sk-custom.create');
    }

    public function store(Request $request, SkDocumentGenerator $generator)
    {
        $judul = (string) $request->input('judul_sk');
        $blocks = json_decode((string) $request->input('blocks_json'), true) ?? [];
        $opsi = (array) $request->input('opsi', []);

        $jenisSk = JenisSk::firstOrCreate(
            ['kode' => 'CUSTOM'],
            ['nama' => 'SK Custom', 'kode_klasifikasi' => '000', 'field_schema' => [], 'is_custom' => true]
        );

        try {
            $skTerbit = $generator->generateCustom($jenisSk, $judul, $blocks, $opsi);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return view('sk-custom.hasil', compact('judul', 'skTerbit'));
    }
}
