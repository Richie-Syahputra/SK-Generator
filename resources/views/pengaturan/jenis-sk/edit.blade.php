@extends('layouts.app')
@section('title', 'Edit Jenis SK')
@section('content')
<h1 class="mb-4">Edit: {{ $jenisSk->nama }}</h1>

<div class="alert alert-info">
    <strong>Placeholder yang bisa dipakai di redaksi:</strong>
    @foreach ($jenisSk->field_schema as $f)
        <code>{{ '{' . $f['key'] . '}' }}</code>{{ !$loop->last ? ', ' : '' }}
    @endforeach
</div>

<form action="{{ route('pengaturan.jenis-sk.update', $jenisSk) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="mb-3">
        <label class="form-label">Kode Klasifikasi (contoh: 800, 421)</label>
        <input type="text" name="kode_klasifikasi" class="form-control" value="{{ old('kode_klasifikasi', $jenisSk->kode_klasifikasi) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Menimbang (1 poin per baris, boleh diawali "a." / "b.")</label>
        <textarea name="template_menimbang" rows="4" class="form-control">{{ old('template_menimbang', $jenisSk->template_menimbang) }}</textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Mengingat (1 poin per baris)</label>
        <textarea name="template_mengingat" rows="4" class="form-control">{{ old('template_mengingat', $jenisSk->template_mengingat) }}</textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Memperhatikan (1 poin per baris, boleh dikosongkan
        supaya bagian ini tidak dicetak sama sekali)</label>
        <textarea name="template_memperhatikan" rows="4" class="form-control">{{ old('template_memperhatikan', $jenisSk->template_memperhatikan) }}</textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Diktum (format "Label : isi" per baris, contoh
        "Pertama : ...")</label>
        <textarea name="template_diktum" rows="6" class="form-control">{{ old('template_diktum', $jenisSk->template_diktum) }}</textarea>
    </div>
    <button type="submit" class="btn btn-primary">Simpan</button>
</form>
@endsection