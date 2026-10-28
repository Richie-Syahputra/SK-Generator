<?php

namespace App\Http\Controllers;

use App\Models\JenisSk;
use App\Models\LetterheadAsset;
use App\Models\PejabatPenandatangan;
use App\Models\SkTerbit;

class HomeController extends Controller
{
    public function index()
    {
        $jenisSkList = JenisSk::where('kode', '!=', 'CUSTOM')->orderBy('nama')->get();
        $skTerbaru = SkTerbit::with('jenisSk')->orderByDesc('created_at')->limit(5)->get();

        $kesiapan = [
            'kop' => LetterheadAsset::aktif() !== null,
            'pejabat' => PejabatPenandatangan::aktif() !== null,
            'libreoffice' => file_exists(env('LIBREOFFICE_BINARY', '')),
        ];

        return view('home', compact('jenisSkList', 'skTerbaru', 'kesiapan'));
    }
}
