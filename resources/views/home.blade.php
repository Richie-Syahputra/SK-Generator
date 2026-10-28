@extends('layouts.app')
@section('title', 'Beranda')
@section('content')

<h2 class="mb-3">Kesiapan Sistem</h2>
<div class="row g-2 mb-4">
    <div class="col-md-4">
        <div class="alert {{ $kesiapan['kop'] ? 'alert-success' : 'alert-warning' }} mb-0">
            Kop Surat: {{ $kesiapan['kop'] ? 'Aktif' : 'Belum ada yang aktif' }}
            @unless($kesiapan['kop'])
                — <a href="{{ route('pengaturan.kop-surat.index') }}">atur di sini</a>
            @endunless
        </div>
    </div>
        <div class="col-md-4">
        <div class="alert {{ $kesiapan['pejabat'] ? 'alert-success' : 'alert-warning' }} mb-0">
            Pejabat Penandatangan: {{ $kesiapan['pejabat'] ? 'Aktif' : 'Belum ada yang aktif' }}
            @unless($kesiapan['pejabat'])
                — <a href="{{ route('pengaturan.pejabat.index') }}">atur di sini</a>
            @endunless
        </div>
    </div>
    <div class="col-md-4">
        <div class="alert {{ $kesiapan['libreoffice'] ? 'alert-success' : 'alert-danger' }} mb-0">
            LibreOffice: {{ $kesiapan['libreoffice'] ? 'Terdeteksi' : 'Tidak ditemukan di path .env' }}
        </div>
    </div>
</div>

<h2 class="mb-3">Buat Surat Keputusan</h2>
<div class="row g-3 mb-4">
    @foreach ($jenisSkList as $j)
    <div class="col-md-3 col-sm-6">
        <div class="card h-100">
            <div class="card-body d-flex flex-column">
                <h6 class="card-title">{{ $j->nama }}</h6>
                <a href="{{ route('sk.create', $j->kode) }}" class="btn btn-primary btn-sm mt-auto">Buat</a>
            </div>
        </div>
    </div>
    @endforeach
    <div class="col-md-3 col-sm-6">
        <div class="card h-100 border-primary">
            <div class="card-body d-flex flex-column">
                <h6 class="card-title">SK Custom</h6>
                <a href="{{ route('sk-custom.create') }}" class="btn btn-outline-primary btn-sm mt-auto">Buat</a>
            </div>
        </div>
    </div>
</div>

<h2 class="mb-3">SK Terbaru</h2>
@if ($skTerbaru->isEmpty())
    <p class="text-muted">Belum ada SK yang diterbitkan.</p>
@else
<table class="table table-sm table-striped">
    <thead><tr><th>Nomor</th><th>Jenis</th><th>Tanggal</th></tr></thead>
    <tbody>
    @foreach ($skTerbaru as $sk)
        <tr>
            <td>{{ $sk->nomor_surat }}</td>
            <td>{{ $sk->jenisSk->nama }}</td>
            <td>{{ $sk->tanggal_sk->format('d-m-Y') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
@endif
<a href="{{ route('riwayat.index') }}">Lihat semua riwayat &rarr;</a>

@endsection