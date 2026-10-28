@extends('layouts.app')
@section('title', 'SK Custom Berhasil Dibuat')
@section('content')
<div class="alert alert-success">
    <h4 class="alert-heading">SK Custom Berhasil Dibuat</h4>
    <p class="mb-1"><strong>Judul:</strong> {{ $judul }}</p>
    <p class="mb-0"><strong>Nomor:</strong> {{ $skTerbit->nomor_surat }}</p>
</div>
<div class="mb-3">
    <a href="{{ asset('storage/' . $skTerbit->file_docx) }}" class="btn btn-primary">Download Word</a>
    <a href="{{ asset('storage/' . $skTerbit->file_pdf) }}" class="btn btn-outline-primary">Download PDF</a>
</div>
<a href="{{ route('sk-custom.create') }}">&larr; Buat SK Custom lain</a>
@endsection
