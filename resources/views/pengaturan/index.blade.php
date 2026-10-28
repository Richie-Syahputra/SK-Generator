@extends('layouts.app')
@section('title', 'Menu Pengaturan')
@section('content')
<h1>Menu Pengaturan</h1>
<ul>
    <li><a href="{{ route('pengaturan.kop-surat.index') }}">Kelola Kop Surat</a></li>
    <li><a href="{{ route('pengaturan.pejabat.index') }}">Kelola Pejabat Penandatangan</a></li>
</ul>
@endsection
