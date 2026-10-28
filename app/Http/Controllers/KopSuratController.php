<?php

namespace App\Http\Controllers;

use App\Models\LetterheadAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KopSuratController extends Controller
{
    public function index()
    {
        $riwayat = LetterheadAsset::orderByDesc('created_at')->get();

        return view('pengaturan.kop-surat.index', compact('riwayat'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'catatan' => 'nullable|string|max:255',
        ]);

        $path = $request->file('file')->store('letterhead', 'public');

        LetterheadAsset::create([
            'file_path' => $path,
            'catatan' => $request->input('catatan'),
            'is_active' => false,
        ]);

        return redirect()->route('pengaturan.kop-surat.index')
            ->with('status', 'Kop surat baru berhasil diupload. Klik "Jadikan Aktif" untuk memakainya.');
    }

    public function aktifkan(LetterheadAsset $letterhead)
    {
        LetterheadAsset::where('is_active', true)->update(['is_active' => false]);
        $letterhead->update(['is_active' => true]);

        return redirect()->route('pengaturan.kop-surat.index')
            ->with('status', 'Kop surat berhasil diaktifkan.');
    }

    public function nonaktifkanSemua()
    {
        LetterheadAsset::where('is_active', true)->update(['is_active' => false]);

        return redirect()->route('pengaturan.kop-surat.index')
            ->with('status', 'Semua kop surat dinonaktifkan. SK baru akan dibuat tanpa kop sampai salah satu diaktifkan lagi.');
    }

    public function destroy(LetterheadAsset $letterhead)
    {
        if ($letterhead->file_path && Storage::disk('public')->exists($letterhead->file_path)) {
            Storage::disk('public')->delete($letterhead->file_path);
        }
        $letterhead->delete();

        return redirect()->route('pengaturan.kop-surat.index')
            ->with('status', 'Kop surat dihapus.');
    }
}
