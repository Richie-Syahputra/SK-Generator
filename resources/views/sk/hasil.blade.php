@extends('layouts.app')
@section('title', 'SK Berhasil Dibuat')
@section('content')
<div class="alert alert-success">
    <h4 class="alert-heading">SK Berhasil Dibuat</h4>
    <p class="mb-1"><strong>Jenis:</strong> {{ $jenisSk->nama }}</p>
    <p class="mb-0"><strong>Nomor:</strong> {{ $skTerbit->nomor_surat }}</p>
</div>
<div class="mb-3">
    <a href="{{ asset('storage/' . $skTerbit->file_docx) }}" class="btn btn-primary">Download Word</a>
    <a href="{{ asset('storage/' . $skTerbit->file_pdf) }}" class="btn btn-outline-primary">Download PDF</a>
</div>
<a href="{{ route('sk.create', $jenisSk->kode) }}">&larr; Buat SK lain jenis ini</a>
@endsection