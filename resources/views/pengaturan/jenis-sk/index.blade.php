@extends('layouts.app')
@section('title', 'Jenis SK')
@section('content')
<h1 class="mb-4">Jenis SK</h1>
<div class="table-responsive">
<table class="table table-striped align-middle">
    <thead class="table-dark">
    <tr>
        <th>Kode</th>
        <th>Nama</th>
        <th>Kode Klasifikasi</th>
        <th>Jumlah Field</th>
        <th>Detail Field</th>
        <th>Aksi</th>
        <th>Buat SK</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($daftar as $item)
    <tr>
        <td>{{ $item->kode }}</td>
        <td>{{ $item->nama }}</td>
        <td>{{ $item->kode_klasifikasi ?? '-' }}</td>
        <td>{{ count($item->field_schema) }}</td>
        <td>
            <details>
                <summary>Lihat</summary>
                <ul class="mb-0">
                    @foreach ($item->field_schema as $field)
                    <li>{{ $field['label'] }} ({{ $field['type'] }})</li>
                    @endforeach
                </ul>
            </details>
        </td>
        <td><a href="{{ route('pengaturan.jenis-sk.edit', $item) }}" class="btn btn-sm btn-outline-secondary">Edit</a></td>
        <td><a href="{{ route('sk.create', $item->kode) }}" class="btn btn-sm btn-primary">Buat</a></td>
    </tr>
    @endforeach
    </tbody>
</table>
</div>
@endsection