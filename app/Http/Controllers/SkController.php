<?php

namespace App\Http\Controllers;

use App\Models\JenisSk;
use App\Services\SkDocumentGenerator;
use Illuminate\Http\Request;

class SkController extends Controller
{
    public function create(JenisSk $jenisSk)
    {
        return view('sk.create', compact('jenisSk'));
    }

    public function store(Request $request, JenisSk $jenisSk, SkDocumentGenerator $generator)
    {
        $opsi = (array) $request->input('opsi', []);
        $data = $request->except(['_token', 'opsi']);

        try {
            $skTerbit = $generator->generate($jenisSk, $data, $opsi);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return view('sk.hasil', compact('jenisSk', 'skTerbit'));
    }
}
