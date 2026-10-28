<div class="mb-3">
    <label class="form-label">Nama Lengkap (dengan gelar, contoh: Dr. Mungin, S.Pd, MA)</label>
    <input type="text" name="nama_lengkap" class="form-control" value="{{ old('nama_lengkap', $pejabat->nama_lengkap ?? '') }}" required>
    @error('nama_lengkap')<div class="text-danger small">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label">Jabatan (dicetak di atas nama pada blok tanda tangan, contoh: Kepala Sekolah)</label>
    <input type="text" name="jabatan" class="form-control" value="{{ old('jabatan', $pejabat->jabatan ?? '') }}" required>
    @error('jabatan')<div class="text-danger small">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label">Jabatan untuk Judul SK (contoh: Kepala SMK Muhammadiyah 3 Banjarmasin)</label>
    <input type="text" name="jabatan_judul" class="form-control" value="{{ old('jabatan_judul', $pejabat->jabatan_judul ?? '') }}">
    <div class="form-text">Dicetak huruf besar di bawah "SURAT KEPUTUSAN". Kosongkan kalau mau memakai isian Jabatan di atas.</div>
</div>
<div class="mb-3">
    <label class="form-label">Label Nomor ID (contoh: NUKS, NIP, NIK)</label>
    <input type="text" name="label_id" class="form-control" value="{{ old('label_id', $pejabat->label_id ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label">Nomor ID</label>
    <input type="text" name="nomor_id" class="form-control" value="{{ old('nomor_id', $pejabat->nomor_id ?? '') }}">
</div>
