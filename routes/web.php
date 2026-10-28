<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\JenisSkController;
use App\Http\Controllers\KopSuratController;
use App\Http\Controllers\PejabatController;
use App\Http\Controllers\PengaturanSekolahController;
use App\Http\Controllers\PenomoranController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\SkController;
use App\Http\Controllers\SkCustomController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/pengaturan', function () {
    return view('pengaturan.index');
})->name('pengaturan.index');

Route::get('/pengaturan/kop-surat', [KopSuratController::class, 'index'])->name('pengaturan.kop-surat.index');
Route::post('/pengaturan/kop-surat', [KopSuratController::class, 'store'])->name('pengaturan.kop-surat.store');
Route::post('/pengaturan/kop-surat/{letterhead}/aktifkan', [KopSuratController::class, 'aktifkan'])->name('pengaturan.kop-surat.aktifkan');
Route::post('/pengaturan/kop-surat/nonaktifkan-semua', [KopSuratController::class, 'nonaktifkanSemua'])->name('pengaturan.kop-surat.nonaktifkan-semua');
Route::delete('/pengaturan/kop-surat/{letterhead}', [KopSuratController::class, 'destroy'])->name('pengaturan.kop-surat.destroy');

Route::get('/pengaturan/pejabat', [PejabatController::class, 'index'])->name('pengaturan.pejabat.index');
Route::get('/pengaturan/pejabat/create', [PejabatController::class, 'create'])->name('pengaturan.pejabat.create');
Route::post('/pengaturan/pejabat', [PejabatController::class, 'store'])->name('pengaturan.pejabat.store');
Route::get('/pengaturan/pejabat/{pejabat}/edit', [PejabatController::class, 'edit'])->name('pengaturan.pejabat.edit');
Route::put('/pengaturan/pejabat/{pejabat}', [PejabatController::class, 'update'])->name('pengaturan.pejabat.update');
Route::delete('/pengaturan/pejabat/{pejabat}', [PejabatController::class, 'destroy'])->name('pengaturan.pejabat.destroy');
Route::post('/pengaturan/pejabat/{pejabat}/aktifkan', [PejabatController::class, 'aktifkan'])->name('pengaturan.pejabat.aktifkan');

Route::get('/pengaturan/jenis-sk', [JenisSkController::class, 'index'])->name('pengaturan.jenis-sk.index');
Route::get('/pengaturan/jenis-sk/{jenisSk}/edit', [JenisSkController::class, 'edit'])->name('pengaturan.jenis-sk.edit');
Route::put('/pengaturan/jenis-sk/{jenisSk}', [JenisSkController::class, 'update'])->name('pengaturan.jenis-sk.update');

Route::get('/sk/{jenisSk:kode}/create', [SkController::class, 'create'])->name('sk.create');
Route::post('/sk/{jenisSk:kode}', [SkController::class, 'store'])->name('sk.store');

Route::get('/riwayat', [RiwayatController::class, 'index'])->name('riwayat.index');
Route::delete('/riwayat/{skTerbit}', [RiwayatController::class, 'destroy'])->name('riwayat.destroy');
Route::post('/riwayat/hapus-terpilih', [RiwayatController::class, 'hapusTerpilih'])->name('riwayat.hapus-terpilih');

Route::get('/sk-custom/create', [SkCustomController::class, 'create'])->name('sk-custom.create');
Route::post('/sk-custom', [SkCustomController::class, 'store'])->name('sk-custom.store');

Route::get('/pengaturan/sekolah', [PengaturanSekolahController::class, 'edit'])->name('pengaturan.sekolah.edit');
Route::put('/pengaturan/sekolah', [PengaturanSekolahController::class, 'update'])->name('pengaturan.sekolah.update');

Route::get('/pengaturan/penomoran', [PenomoranController::class, 'edit'])->name('pengaturan.penomoran.edit');
Route::put('/pengaturan/penomoran/format', [PenomoranController::class, 'simpanFormat'])->name('pengaturan.penomoran.format');
Route::post('/pengaturan/penomoran/atur', [PenomoranController::class, 'atur'])->name('pengaturan.penomoran.atur');
