@extends('layouts.app')
@section('title', 'Riwayat SK')
@section('content')
@php
    $namaBulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
                  7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
@endphp

<h1 class="mb-4">Riwayat SK</h1>

<form method="GET" class="card card-body mb-3">
    <div class="row g-2">
        <div class="col-md-6">
            <label class="form-label small mb-1">Kata kunci (nomor atau isi surat)</label>
            <input type="text" name="cari" class="form-control" value="{{ request('cari') }}" placeholder="Contoh: KEP.024 atau nama pegawai">
        </div>
        <div class="col-md-6">
            <label class="form-label small mb-1">Jenis SK</label>
            <select name="jenis_sk_id" class="form-select">
                <option value="">Semua jenis</option>
                @foreach ($jenisSkList as $j)
                <option value="{{ $j->id }}" @selected(request('jenis_sk_id') == $j->id)>{{ $j->nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Tahun</label>
            <select name="tahun" class="form-select">
                <option value="">Semua</option>
                @foreach ($daftarTahun as $t)
                <option value="{{ $t }}" @selected(request('tahun') == $t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Bulan</label>
            <select name="bulan" class="form-select">
                <option value="">Semua</option>
                @foreach ($namaBulan as $no => $nama)
                <option value="{{ $no }}" @selected(request('bulan') == $no)>{{ $nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Dari tanggal</label>
            <input type="date" name="dari" class="form-control" value="{{ request('dari') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Sampai tanggal</label>
            <input type="date" name="sampai" class="form-control" value="{{ request('sampai') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label small mb-1">Urutkan</label>
            <select name="urut" class="form-select">
                <option value="terbaru" @selected(request('urut', 'terbaru') == 'terbaru')>Terbaru dibuat</option>
                <option value="nomor_terbesar" @selected(request('urut') == 'nomor_terbesar')>Nomor terbesar dulu</option>
                <option value="nomor_terkecil" @selected(request('urut') == 'nomor_terkecil')>Nomor terkecil dulu</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small mb-1">Penandatangan</label>
            <select name="penandatangan" class="form-select">
                <option value="">Semua</option>
                @foreach ($daftarPenandatangan as $nama)
                <option value="{{ $nama }}" @selected(request('penandatangan') == $nama)>{{ $nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-primary">Cari</button>
            <a href="{{ route('riwayat.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </div>
</form>

<div class="d-flex justify-content-between align-items-center mb-2">
    <div class="text-muted small">{{ $daftar->total() }} surat ditemukan</div>
    <form id="form-hapus-banyak" action="{{ route('riwayat.hapus-terpilih') }}" method="POST" onsubmit="return konfirmasiBanyak()">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus yang Dicentang</button>
    </form>
</div>

<div class="table-responsive">
<table class="table table-striped table-hover align-middle">
    <thead class="table-dark">
    <tr>
        <th style="width:36px;"><input type="checkbox" id="cek-semua" class="form-check-input" title="Centang semua di halaman ini"></th>
        <th>Nomor Surat</th>
        <th>Jenis</th>
        <th>Penandatangan</th>
        <th>Tanggal</th>
        <th>Detail Isian</th>
        <th>Aksi</th>
    </tr>
    </thead>
    <tbody>
    @forelse ($daftar as $item)
    <tr>
        <td><input type="checkbox" class="form-check-input cek-surat" name="ids[]" value="{{ $item->id }}" form="form-hapus-banyak"></td>
        <td>{{ $item->nomor_surat }}</td>
        <td>{{ $item->jenisSk->nama }}</td>
        <td>{{ $item->penandatangan_nama ?: '—' }}</td>
        <td>{{ $item->tanggal_sk->format('d-m-Y') }}</td>
        <td>
            <details>
                <summary>Lihat</summary>
                <ul class="mb-0">
                    @foreach ($item->data_isian as $k => $v)
                    <li>{{ $k }}: {{ is_array($v) ? json_encode($v) : $v }}</li>
                    @endforeach
                </ul>
            </details>
        </td>
        <td class="text-nowrap">
            <a href="{{ asset('storage/' . $item->file_docx) }}" class="btn btn-sm btn-outline-primary">Word</a>
            <a href="{{ asset('storage/' . $item->file_pdf) }}" class="btn btn-sm btn-outline-secondary">PDF</a>
            <form action="{{ route('riwayat.destroy', $item) }}" method="POST" class="d-inline" data-nomor="{{ $item->nomor_surat }}" onsubmit="return konfirmasiHapus(this)">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
            </form>
        </td>
    </tr>
    @empty
    <tr><td colspan="7" class="text-center text-muted">Tidak ada surat yang cocok.</td></tr>
    @endforelse
    </tbody>
</table>
</div>

<div class="mt-3">
    {{ $daftar->links() }}
</div>

<script>
function konfirmasiHapus(form) {
    return confirm('Hapus surat ' + form.dataset.nomor + '? File Word/PDF-nya ikut terhapus dan tidak bisa dikembalikan. ' +
        'Kalau ini nomor tertinggi, nomornya akan dipakai ulang oleh surat berikutnya.');
}
function konfirmasiBanyak() {
    const n = document.querySelectorAll('.cek-surat:checked').length;
    if (n === 0) {
        alert('Centang dulu surat yang ingin dihapus.');
        return false;
    }
    return confirm('Hapus ' + n + ' surat yang dicentang? File Word/PDF-nya ikut terhapus dan tidak bisa dikembalikan.');
}
document.getElementById('cek-semua').addEventListener('change', function () {
    document.querySelectorAll('.cek-surat').forEach(function (c) { c.checked = this.checked; }, this);
});
</script>
@endsection