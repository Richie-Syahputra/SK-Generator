@extends('layouts.app')
@section('title', $jenisSk->nama)
@section('content')
<h1 class="mb-4">{{ $jenisSk->nama }}</h1>
<form action="{{ route('sk.store', $jenisSk->kode) }}" method="POST">
    @csrf
    @foreach ($jenisSk->field_schema as $field)
        <div class="mb-3">
            <label class="form-label fw-bold">{{ $field['label'] }}</label>
            @switch($field['type'])
                @case('text')
                    <input type="text" name="{{ $field['key'] }}" class="form-control">
                    @break
                @case('number')
                    <input type="number" name="{{ $field['key'] }}" class="form-control">
                    @break
                @case('date')
                    <input type="date" name="{{ $field['key'] }}" class="form-control">
                    @break
                @case('textarea')
                    <textarea name="{{ $field['key'] }}" rows="4" class="form-control"></textarea>
                    @break
                @case('select')
                    <select name="{{ $field['key'] }}" class="form-select" data-manual-target="manual-{{ $field['key'] }}">
                        @foreach ($field['options'] as $opt)
                        <option value="{{ $opt }}">{{ $opt }}</option>
                        @endforeach
                        <option value="__manual__">Lainnya (isi manual)…</option>
                    </select>
                    <input type="text" id="manual-{{ $field['key'] }}" name="{{ $field['key'] }}__manual" class="form-control mt-2 d-none" placeholder="Ketik isian sendiri">
                    @break
                @case('table')
                    <div class="table-responsive">
                    <table id="tabel-{{ $field['key'] }}" class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                @foreach ($field['columns'] as $col)
                                <th>{{ $col }}</th>
                                @endforeach
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="tambahBaris('{{ $field['key'] }}', {{ count($field['columns']) }})">+ Tambah Baris</button>
                    <script>tambahBaris('{{ $field['key'] }}', {{ count($field['columns']) }});</script>
                    @break
            @endswitch
        </div>
    @endforeach
    @include('sk._opsi-surat')
    <button type="submit" class="btn btn-primary mt-3">Simpan</button>
</form>

<script>
function tambahBaris(key, colCount) {
    const tbody = document.querySelector('#tabel-' + key + ' tbody');
    const rowIndex = tbody.children.length;
    const tr = document.createElement('tr');
    for (let c = 0; c < colCount; c++) {
        const td = document.createElement('td');
        const input = document.createElement('input');
        input.type = 'text';
        input.name = key + '[' + rowIndex + '][' + c + ']';
        input.className = 'form-control form-control-sm';
        td.appendChild(input);
        tr.appendChild(td);
    }
    const tdHapus = document.createElement('td');
    const btnHapus = document.createElement('button');
    btnHapus.type = 'button';
    btnHapus.className = 'btn btn-sm btn-outline-danger';
    btnHapus.textContent = 'Hapus';
    btnHapus.onclick = function () { tr.remove(); };
    tdHapus.appendChild(btnHapus);
    tr.appendChild(tdHapus);
    tbody.appendChild(tr);
}
</script>
<script>
document.querySelectorAll('select[data-manual-target]').forEach(function (pilihan) {
    const kotak = document.getElementById(pilihan.dataset.manualTarget);
    function ubah() {
        const manual = pilihan.value === '__manual__';
        kotak.classList.toggle('d-none', !manual);
        kotak.required = manual;
        if (!manual) {
            kotak.value = '';
        }
    }
    pilihan.addEventListener('change', ubah);
    ubah();
});
</script>
@endsection