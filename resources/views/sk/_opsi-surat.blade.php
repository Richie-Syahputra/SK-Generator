<div class="card mb-3">
    <div class="card-header fw-bold">Pengaturan Surat</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Tanggal penetapan</label>
                <input type="date" name="opsi[tanggal]" class="form-control" value="{{ $opsiTanggalDefault }}" required>
                <div class="form-text">Bulan dan tahun di nomor surat mengikuti tanggal ini.</div>
            </div>

            <div class="col-md-8">
                <label class="form-label">Kop surat</label>
                <select name="opsi[kop]" id="opsi-kop" class="form-select">
                    @foreach ($opsiKopList as $k)
                    <option value="{{ $k->id }}" data-src="{{ asset('storage/' . $k->file_path) }}" @selected($opsiKopAktifId == $k->id)>
                        Kop #{{ $k->id }}{{ $k->catatan ? ' — ' . $k->catatan : '' }}{{ $opsiKopAktifId == $k->id ? ' (aktif)' : '' }}
                    </option>
                    @endforeach
                    <option value="tanpa" @selected(!$opsiKopAktifId)>Tanpa kop</option>
                    <option value="kosong">Tanpa kop, sisakan ruang kosong (untuk kertas berkop)</option>
                </select>
                <img id="opsi-kop-preview" class="img-fluid border rounded mt-2 d-none" style="max-height:90px;" alt="Pratinjau kop">
            </div>

            <div class="col-12">
                <label class="form-label">Penandatangan</label>
                <select name="opsi[penandatangan]" id="opsi-penandatangan" class="form-select">
                    @foreach ($opsiPejabatList as $p)
                    <option value="{{ $p->id }}" @selected($opsiPejabatAktifId == $p->id)>
                        {{ $p->nama_lengkap }} — {{ $p->jabatan }}{{ $opsiPejabatAktifId == $p->id ? ' (aktif)' : '' }}
                    </option>
                    @endforeach
                    <option value="manual" @selected($opsiPejabatList->isEmpty())>Isi manual…</option>
                </select>

                <div id="opsi-manual" class="d-none mt-2 p-3 border rounded bg-light">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small mb-1">Nama lengkap (dengan gelar)</label>
                            <input type="text" name="opsi[manual][nama]" class="form-control" data-wajib>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small mb-1">Jabatan (di tanda tangan, contoh: Plt. Kepala Sekolah)</label>
                            <input type="text" name="opsi[manual][jabatan]" class="form-control" data-wajib>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small mb-1">Jabatan untuk judul SK (opsional)</label>
                            <input type="text" name="opsi[manual][jabatan_judul]" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Label ID (NUKS/NIP)</label>
                            <input type="text" name="opsi[manual][label_id]" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Nomor ID</label>
                            <input type="text" name="opsi[manual][nomor_id]" class="form-control">
                        </div>
                    </div>
                    <div class="form-text">Hanya dipakai untuk surat ini, tidak disimpan ke daftar pejabat.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const kopSelect = document.getElementById('opsi-kop');
    const kopPreview = document.getElementById('opsi-kop-preview');
    function ubahKop() {
        const opt = kopSelect.options[kopSelect.selectedIndex];
        const src = opt ? opt.dataset.src : '';
        if (src) {
            kopPreview.src = src;
            kopPreview.classList.remove('d-none');
        } else {
            kopPreview.classList.add('d-none');
        }
    }
    kopSelect.addEventListener('change', ubahKop);
    ubahKop();

    const pjSelect = document.getElementById('opsi-penandatangan');
    const panel = document.getElementById('opsi-manual');
    function ubahPenandatangan() {
        const manual = pjSelect.value === 'manual';
        panel.classList.toggle('d-none', !manual);
        panel.querySelectorAll('[data-wajib]').forEach(function (el) { el.required = manual; });
    }
    pjSelect.addEventListener('change', ubahPenandatangan);
    ubahPenandatangan();
})();
</script>
