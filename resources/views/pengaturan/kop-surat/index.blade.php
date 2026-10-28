@extends('layouts.app')
@section('title', 'Kop Surat')
@section('content')
<h1 class="mb-4">Kop Surat</h1>

<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">Upload Kop Surat Baru</h5>
        <form action="{{ route('pengaturan.kop-surat.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label">File gambar (JPG/PNG, maks 2MB)</label>
                <input type="file" name="file" accept="image/png, image/jpeg" class="form-control" required>
                @error('file')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Catatan (opsional)</label>
                <input type="text" name="catatan" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </div>
</div>

<form action="{{ route('pengaturan.kop-surat.nonaktifkan-semua') }}" method="POST" class="mb-4" onsubmit="return confirm('Nonaktifkan semua kop surat? SK baru akan dibuat tanpa kop sampai Anda mengaktifkan salah satu lagi.')">
    @csrf
    <button type="submit" class="btn btn-secondary">Nonaktifkan Semua Kop Surat</button>
</form>

<h5 class="mb-3">Riwayat Kop Surat</h5>
<div class="table-responsive">
<table class="table table-striped align-middle">
    <thead class="table-dark">
    <tr>
        <th>Preview</th>
        <th>Catatan</th>
        <th>Status</th>
        <th>Diupload</th>
        <th>Aksi</th>
    </tr>
    </thead>
    <tbody>
    @forelse ($riwayat as $item)
    <tr>
        <td><img src="{{ asset('storage/' . $item->file_path) }}" style="max-width:150px;" class="img-fluid border rounded"></td>
        <td>{{ $item->catatan ?? '-' }}</td>
        <td>
            @if ($item->is_active)
                <span class="badge bg-success">AKTIF</span>
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
        <td>{{ $item->created_at->format('d-m-Y H:i') }}</td>
        <td>
            @if (!$item->is_active)
            <form action="{{ route('pengaturan.kop-surat.aktifkan', $item) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-success">Jadikan Aktif</button>
            </form>
            @endif
            <form action="{{ route('pengaturan.kop-surat.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus kop surat ini? Tidak bisa dibatalkan.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
            </form>
        </td>
    </tr>
    @empty
    <tr><td colspan="5" class="text-center text-muted">Belum ada kop surat yang diupload.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
@endsection