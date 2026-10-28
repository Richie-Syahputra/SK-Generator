@extends('layouts.app')
@section('title', 'Edit Pejabat')
@section('content')
<h1 class="mb-4">Edit Pejabat Penandatangan</h1>
<form action="{{ route('pengaturan.pejabat.update', $pejabat) }}" method="POST">
    @csrf
    @method('PUT')
    @include('pengaturan.pejabat._form', ['pejabat' => $pejabat])
    <button type="submit" class="btn btn-primary">Perbarui</button>
</form>
@endsection
