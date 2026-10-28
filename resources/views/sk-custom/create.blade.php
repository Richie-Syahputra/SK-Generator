@extends('layouts.app')
@section('title', 'SK Custom')
@section('content')
<h1 class="mb-4">SK Custom</h1>
<p class="text-muted">Susun blok isi surat di bawah, lalu klik "Simpan" untuk menghasilkan dokumen.</p>

<form method="POST" action="{{ route('sk-custom.store') }}">
    @csrf

    <div class="mb-3">
        <label class="form-label fw-bold">Judul SK (untuk bagian "Tentang")</label>
        <input type="text" name="judul_sk" class="form-control" required>
    </div>

    <h5 class="mt-4 mb-3">Blok Isi Surat</h5>
    <div id="blocks-container"></div>

    <div class="mt-3 mb-4">
        <button type="button" class="btn btn-outline-primary" onclick="tambahBlok('paragraf')">+ Tambah Paragraf</button>
        <button type="button" class="btn btn-outline-primary" onclick="tambahBlok('tabel')">+ Tambah Tabel</button>
    </div>

    <input type="hidden" name="blocks_json" id="blocks_json">

    @include('sk._opsi-surat')
    <button type="submit" class="btn btn-primary">Simpan</button>
</form>

<script>
let blocks = [];

function tambahBlok(tipe) {
    if (tipe === 'paragraf') {
        blocks.push({ tipe: 'paragraf', label: '', isi: '' });
    } else {
        blocks.push({ tipe: 'tabel', label: '', kolom: ['Kolom 1'], baris: [['']] });
    }
    render();
}

function hapusBlok(idx) {
    blocks.splice(idx, 1);
    render();
}

function pindahBlok(idx, arah) {
    const target = idx + arah;
    if (target < 0 || target >= blocks.length) return;
    [blocks[idx], blocks[target]] = [blocks[target], blocks[idx]];
    render();
}

function tambahKolom(blokIdx) {
    blocks[blokIdx].kolom.push('Kolom Baru');
    blocks[blokIdx].baris.forEach(b => b.push(''));
    render();
}

function hapusKolom(blokIdx, kolIdx) {
    blocks[blokIdx].kolom.splice(kolIdx, 1);
    blocks[blokIdx].baris.forEach(b => b.splice(kolIdx, 1));
    render();
}

function tambahBaris(blokIdx) {
    const jumlahKolom = blocks[blokIdx].kolom.length;
    blocks[blokIdx].baris.push(new Array(jumlahKolom).fill(''));
    render();
}

function hapusBaris(blokIdx, barisIdx) {
    blocks[blokIdx].baris.splice(barisIdx, 1);
    render();
}

function render() {
    const container = document.getElementById('blocks-container');
    container.innerHTML = '';
    blocks.forEach((blok, idx) => {
        const card = document.createElement('div');
        card.className = 'card mb-3';

        const body = document.createElement('div');
        body.className = 'card-body';

        const header = document.createElement('h6');
        header.className = 'card-subtitle mb-2 text-muted';
        header.textContent = 'Blok ' + (idx + 1) + ': ' + (blok.tipe === 'paragraf' ? 'Paragraf' : 'Tabel');
        body.appendChild(header);

        const labelInput = document.createElement('input');
        labelInput.type = 'text';
        labelInput.className = 'form-control form-control-sm mb-2';
        labelInput.placeholder = 'Label (opsional, misal: Menimbang)';
        labelInput.value = blok.label;
        labelInput.oninput = () => { blok.label = labelInput.value; };
        body.appendChild(labelInput);

        if (blok.tipe === 'paragraf') {
            const textarea = document.createElement('textarea');
            textarea.className = 'form-control';
            textarea.rows = 3;
            textarea.value = blok.isi;
            textarea.oninput = () => { blok.isi = textarea.value; };
            body.appendChild(textarea);
        } else {
                        const tableWrap = document.createElement('div');
            tableWrap.className = 'table-responsive';

            const table = document.createElement('table');
            table.className = 'table table-bordered table-sm align-middle mb-2';

            const thead = document.createElement('thead');
            thead.className = 'table-light';
            const theadRow = document.createElement('tr');
            blok.kolom.forEach((namaKolom, kolIdx) => {
                const th = document.createElement('th');
                const inputKolom = document.createElement('input');
                inputKolom.type = 'text';
                inputKolom.className = 'form-control form-control-sm';
                inputKolom.value = namaKolom;
                inputKolom.oninput = () => { blok.kolom[kolIdx] = inputKolom.value; };
                th.appendChild(inputKolom);
                const btnHapusKolom = document.createElement('button');
                btnHapusKolom.type = 'button';
                btnHapusKolom.className = 'btn btn-sm btn-outline-danger mt-1';
                btnHapusKolom.textContent = 'Hapus Kolom';
                btnHapusKolom.onclick = () => hapusKolom(idx, kolIdx);
                th.appendChild(btnHapusKolom);
                theadRow.appendChild(th);
            });
            theadRow.appendChild(document.createElement('th'));
            thead.appendChild(theadRow);
            table.appendChild(thead);

            const tbody = document.createElement('tbody');
            blok.baris.forEach((baris, barisIdx) => {
                const tr = document.createElement('tr');
                baris.forEach((val, kolIdx) => {
                    const td = document.createElement('td');
                    const inputSel = document.createElement('input');
                    inputSel.type = 'text';
                    inputSel.className = 'form-control form-control-sm';
                    inputSel.value = val;
                    inputSel.oninput = () => { blok.baris[barisIdx][kolIdx] = inputSel.value; };
                    td.appendChild(inputSel);
                    tr.appendChild(td);
                });
                const tdHapus = document.createElement('td');
                const btnHapusBaris = document.createElement('button');
                btnHapusBaris.type = 'button';
                btnHapusBaris.className = 'btn btn-sm btn-outline-danger';
                btnHapusBaris.textContent = 'Hapus Baris';
                btnHapusBaris.onclick = () => hapusBaris(idx, barisIdx);
                tdHapus.appendChild(btnHapusBaris);
                tr.appendChild(tdHapus);
                tbody.appendChild(tr);
            });
            table.appendChild(tbody);
            tableWrap.appendChild(table);
            body.appendChild(tableWrap);

            const btnTambahKolom = document.createElement('button');
            btnTambahKolom.type = 'button';
            btnTambahKolom.className = 'btn btn-sm btn-outline-secondary me-2';
            btnTambahKolom.textContent = '+ Kolom';
            btnTambahKolom.onclick = () => tambahKolom(idx);
            body.appendChild(btnTambahKolom);

            const btnTambahBaris = document.createElement('button');
            btnTambahBaris.type = 'button';
            btnTambahBaris.className = 'btn btn-sm btn-outline-secondary';
            btnTambahBaris.textContent = '+ Baris';
            btnTambahBaris.onclick = () => tambahBaris(idx);
            body.appendChild(btnTambahBaris);
        }

        const kontrolBlok = document.createElement('div');
        kontrolBlok.className = 'mt-3';

        const btnUp = document.createElement('button');
        btnUp.type = 'button';
        btnUp.className = 'btn btn-sm btn-outline-secondary';
        btnUp.textContent = '↑';
        btnUp.onclick = () => pindahBlok(idx, -1);
        kontrolBlok.appendChild(btnUp);

        const btnDown = document.createElement('button');
        btnDown.type = 'button';
        btnDown.className = 'btn btn-sm btn-outline-secondary ms-1';
        btnDown.textContent = '↓';
        btnDown.onclick = () => pindahBlok(idx, 1);
        kontrolBlok.appendChild(btnDown);

        const btnHapusBlok = document.createElement('button');
        btnHapusBlok.type = 'button';
        btnHapusBlok.className = 'btn btn-sm btn-danger ms-3';
        btnHapusBlok.textContent = 'Hapus Blok';
        btnHapusBlok.onclick = () => hapusBlok(idx);
        kontrolBlok.appendChild(btnHapusBlok);

        body.appendChild(kontrolBlok);
        card.appendChild(body);
        container.appendChild(card);
    });
}

document.querySelector('form').addEventListener('submit', function () {
    document.getElementById('blocks_json').value = JSON.stringify(blocks);
});

render();
</script>
@endsection
