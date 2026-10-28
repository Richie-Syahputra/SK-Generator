@extends('layouts.app')
@section('title', 'Penomoran Surat')
@section('content')
<h1 class="mb-4">Penomoran Surat</h1>

<div class="alert alert-info">
    <strong>Tahun {{ $tahun }}</strong><br>
    Nomor tertinggi saat ini: <strong>{{ $tertinggi ?: '—' }}</strong><br>
    Nomor surat berikutnya: <strong>{{ $berikutnya }}</strong>
    &rarr; contoh: <code>{{ $contoh }}</code><br>
    <span class="small text-muted">
        Urutan satu untuk semua jenis SK dan otomatis mulai dari 1 lagi setiap tahun baru.
        @if ($baris->reset_at)
            Terakhir diatur manual: mulai dari {{ $baris->nomor_mulai }} ({{ $baris->reset_at->format('d-m-Y H:i') }}).
        @endif
    </span>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">Atur Nomor Berikutnya (Reset Manual)</h5>
        <p class="text-muted small mb-3">
            Berguna kalau sekolah sudah menerbitkan SK di kertas sebelum memakai aplikasi, atau
            ingin mulai dari 1 lagi. Surat yang sudah ada tetap tersimpan di riwayat, tetapi tidak
            lagi dihitung untuk urutan, sehingga nomor bisa berulang dengan surat lama.
        </p>
        <form action="{{ route('pengaturan.penomoran.atur') }}" method="POST" onsubmit="return konfirmasiAtur(this)">
            @csrf
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Tahun</label>
                    <input type="number" name="tahun" class="form-control" value="{{ $tahun }}" min="2000" max="2100" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nomor berikutnya menjadi</label>
                    <input type="number" name="nomor_mulai" class="form-control" min="1" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-warning">Atur Nomor</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">Format Nomor Surat</h5>
        <form action="{{ route('pengaturan.penomoran.format') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Format</label>
                <input type="text" name="format_nomor" class="form-control" value="{{ old('format_nomor', $pengaturan->format_nomor) }}" required>
                <div class="form-text">
                    Penanda: <code>{klasifikasi}</code> (kode klasifikasi jenis SK),
                    <code>{nomor}</code> (nomor urut, wajib ada), <code>{bulan}</code> (angka Romawi),
                    <code>{tahun}</code>.
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Jumlah digit nomor urut (3 = 001, 002, ...)</label>
                <input type="number" name="digit_nomor" class="form-control" min="1" max="6" value="{{ old('digit_nomor', $pengaturan->digit_nomor ?: 3) }}" required>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Format</button>
        </form>
    </div>
</div>

<script>
function konfirmasiAtur(form) {
    const n = form.nomor_mulai.value;
    const t = form.tahun.value;
    return confirm('Nomor surat berikutnya untuk tahun ' + t + ' akan diatur menjadi ' + n +
        '. Surat yang sudah ada tetap tersimpan, tetapi tidak lagi dihitung untuk urutan, sehingga nomor bisa berulang dengan surat lama. Lanjutkan?');
}
</script>
@endsection