@extends('layouts.app')
@section('title', 'Pengaturan Sekolah')
@section('content')
<h1 class="mb-4">Pengaturan Sekolah</h1>
<form action="{{ route('pengaturan.sekolah.update') }}" method="POST">
    @csrf
    @method('PUT')
    <div class="mb-3">
        <label class="form-label">Nama Sekolah</label>
        <input type="text" name="nama_sekolah" class="form-control" value="{{ old('nama_sekolah', $pengaturan->nama_sekolah) }}" required>
        <div class="form-text">Belum dipakai otomatis di dokumen, disimpan untuk tahap berikutnya.</div>
    </div>
    <div class="mb-3">
        <label class="form-label">Kota (untuk baris "Ditetapkan di : ...")</label>
        <input type="text" name="kota" class="form-control" value="{{ old('kota', $pengaturan->kota) }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Tembusan Default (1 baris per poin)</label>
        <textarea name="tembusan_default" rows="4" class="form-control">{{ old('tembusan_default', $pengaturan->tembusan_default) }}</textarea>
    </div>
    <div class="alert alert-secondary">
        Format dan nomor urut surat diatur di menu Pengaturan &rarr; Penomoran.
    </div>
    <button type="submit" class="btn btn-primary">Simpan</button>
</form>
@endsection