@extends('layouts.app')
@section('title', 'Pejabat Penandatangan')
@section('content')
<h1 class="mb-4">Pejabat Penandatangan</h1>
<a href="{{ route('pengaturan.pejabat.create') }}" class="btn btn-primary mb-3">+ Tambah Pejabat</a>

<div class="table-responsive">
<table class="table table-striped align-middle">
    <thead class="table-dark">
    <tr>
        <th>Nama Lengkap</th>
        <th>Jabatan</th>
        <th>Nomor ID</th>
        <th>Status</th>
        <th>Aksi</th>
    </tr>
    </thead>
    <tbody>
    @forelse ($daftar as $item)
    <tr>
        <td>{{ $item->nama_lengkap }}</td>
        <td>{{ $item->jabatan }}</td>
        <td>{{ $item->label_id }} {{ $item->nomor_id }}</td>
        <td>
            @if ($item->is_active)
                <span class="badge bg-success">AKTIF</span>
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
        <td>
            @if (!$item->is_active)
            <form action="{{ route('pengaturan.pejabat.aktifkan', $item) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-success">Jadikan Aktif</button>
            </form>
            @endif
            <a href="{{ route('pengaturan.pejabat.edit', $item) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
            <form action="{{ route('pengaturan.pejabat.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
            </form>
        </td>
    </tr>
    @empty
    <tr><td colspan="5" class="text-center text-muted">Belum ada data pejabat.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
@endsection
