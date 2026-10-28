@extends('layouts.app')
@section('title', 'Tambah Pejabat')
@section('content')
<h1 class="mb-4">Tambah Pejabat Penandatangan</h1>
<form action="{{ route('pengaturan.pejabat.store') }}" method="POST">
    @csrf
    @include('pengaturan.pejabat._form')
    <button type="submit" class="btn btn-primary">Simpan</button>
</form>
@endsection
