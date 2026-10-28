<?php

namespace App\Services;

use PhpOffice\PhpWord\ComplexType\TblWidth;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Table as TableStyle;

/**
 * Menyusun dokumen SK mengikuti aturan "surat produksi" sekolah (hasil analisis
 * Surat_SK_umum.docx). Class ini TIDAK tahu apa pun soal database/Laravel — hanya
 * menerima data siap cetak ($spec) dan mengembalikan objek PhpWord.
 */
class SkLayoutBuilder
{
    // ---- Font & ukuran (pt) ----
    public const FONT = 'Calibri Light';

    public const UKURAN_JUDUL = 12;

    public const UKURAN_ISI = 10;

    public const UKURAN_JEDA_JUDUL = 11; // baris kosong sebelum Menimbang

    // ---- Halaman (twips; 1 cm = 567 twips) ----
    public const HALAMAN_W = 11907;

    public const HALAMAN_H = 16840;

    public const MARGIN_ATAS = 879;

    public const MARGIN_BAWAH = 567;

    public const MARGIN_KIRI = 851;

    public const MARGIN_KANAN = 851;

    public const LEBAR = 10205; // lebar area teks = 11907 - 851 - 851

    // ---- Kolom blok berlabel (twips dari margin kiri) ----
    public const KOL_LABEL = 1418;   // "Menimbang" / "Pertama" mengisi 0 - 2,50 cm

    public const KOL_TITIK2 = 283;   // ":" mengisi 2,50 - 3,00 cm

    public const KOL_NOMOR = 284;    // "a." / "1." mengisi 3,00 - 3,50 cm

    public const INDENT_TTD = 5760;  // blok tanda tangan mulai 10,16 cm

    public const KOL_TTD_LABEL = 1440;

    public const KOL_TEMBUSAN_NOMOR = 360;

    public const TULISAN_MEMUTUSKAN = 'M E M U T U S K A N';

    private PhpWord $word;

    private $section;

    /**
     * @param  array  $spec  lihat dokumentasi kunci di bawah
     *                       - mode: 'standar' | 'custom'
     *                       - kop_path: ?string   path file gambar kop (null = tanpa kop)
     *                       - jabatan_judul: string
     *                       - nomor: string
     *                       - judul: string
     *                       - menimbang, mengingat, memperhatikan: string[]  (baris, boleh berawalan "a. " / "1. ")
     *                       - diktum: array<array{label:string, teks:string}>  (mode standar)
     *                       - tabel: array<array{judul:?string, kolom:string[], baris:string[][]}>  (mode standar)
     *                       - blok: array  (mode custom; tiap item: tipe=paragraf|tabel)
     *                       - kota, tanggal: string
     *                       - penandatangan: array{jabatan:string, nama:string, label_id:?string, nomor_id:?string}
     *                       - tembusan: string[]
     */
    public function build(array $spec): PhpWord
    {
        $this->word = new PhpWord;
        $this->word->setDefaultFontName(self::FONT);
        $this->word->setDefaultFontSize(self::UKURAN_ISI);
        $this->word->setDefaultParagraphStyle([
            'spaceBefore' => 0,
            'spaceAfter' => 0,
            'lineHeight' => 1.0,
        ]);

        $this->section = $this->word->addSection([
            'pageSizeW' => self::HALAMAN_W,
            'pageSizeH' => self::HALAMAN_H,
            'marginTop' => self::MARGIN_ATAS,
            'marginBottom' => self::MARGIN_BAWAH,
            'marginLeft' => self::MARGIN_KIRI,
            'marginRight' => self::MARGIN_KANAN,
        ]);

        if (! empty($spec['kop_path'])) {
            $this->kop($spec['kop_path']);
        } elseif (! empty($spec['kop_ruang'])) {
            // Tanpa kop, tetapi sisakan ruang untuk kertas berkop: 5 baris kosong 12 pt (±2,6 cm)
            for ($i = 0; $i < 5; $i++) {
                $this->kosong(self::UKURAN_JUDUL);
            }
        }

        $this->blokJudul($spec);

        if (($spec['mode'] ?? 'standar') === 'custom') {
            $this->isiCustom($spec['blok'] ?? []);
        } else {
            $this->isiStandar($spec);
        }

        $this->blokTandaTangan($spec);
        $this->blokTembusan($spec['tembusan'] ?? []);

        return $this->word;
    }

    // =====================================================================
    // Bagian-bagian dokumen
    // =====================================================================

    private function kop(string $path): void
    {
        $info = @getimagesize($path);
        $lebarPt = self::LEBAR / 20; // twips -> pt (510 pt = 18 cm)
        $tinggiPt = $info ? round($lebarPt * ($info[1] / $info[0])) : 90;
        $this->section->addImage($path, [
            'width' => $lebarPt,
            'height' => $tinggiPt,
            'alignment' => 'center',
        ]);
    }

    private function blokJudul(array $spec): void
    {
        $tebal12 = ['bold' => true, 'size' => self::UKURAN_JUDUL];
        $tengah = ['alignment' => 'center'];

        $this->section->addText('SURAT KEPUTUSAN', $tebal12, $tengah);
        $this->section->addText(mb_strtoupper($spec['jabatan_judul']), $tebal12, $tengah);
        $this->section->addText('Nomor : '.$spec['nomor'], $tebal12, $tengah);
        $this->kosong(self::UKURAN_JUDUL);
        $this->section->addText('Tentang', $tebal12, $tengah);
        $this->kosong(self::UKURAN_JUDUL);
        $this->section->addText(mb_strtoupper($spec['judul']), $tebal12, $tengah);
        $this->kosong(self::UKURAN_JEDA_JUDUL);
    }

    private function isiStandar(array $spec): void
    {
        $this->blokPoin('Menimbang', $spec['menimbang'] ?? []);
        $this->kosong(self::UKURAN_ISI);
        $this->blokPoin('Mengingat', $spec['mengingat'] ?? []);
        $this->kosong(self::UKURAN_ISI);
        if (! empty($spec['memperhatikan'])) {
            $this->blokPoin('Memperhatikan', $spec['memperhatikan']);
            $this->kosong(self::UKURAN_ISI);
        }

        $this->section->addText(self::TULISAN_MEMUTUSKAN, ['bold' => true], ['alignment' => 'center']);
        $this->section->addText('Menetapkan');

        foreach (($spec['diktum'] ?? []) as $d) {
            $this->blokBerlabel($d['label'], $d['teks']);
            $this->kosong(self::UKURAN_ISI);
        }

        foreach (($spec['tabel'] ?? []) as $t) {
            if (! empty($t['judul'])) {
                $this->section->addText($t['judul'], ['bold' => true]);
            }
            $this->tabelBergaris($t['kolom'], $t['baris']);
            $this->kosong(self::UKURAN_ISI);
        }

        $this->kosong(self::UKURAN_ISI); // total 2 baris kosong sebelum tanda tangan
    }

    private function isiCustom(array $blok): void
    {
        foreach ($blok as $b) {
            $tipe = $b['tipe'] ?? '';
            if ($tipe === 'paragraf') {
                if (! empty($b['label'])) {
                    $this->blokBerlabel($b['label'], (string) ($b['isi'] ?? ''));
                } else {
                    $this->section->addText((string) ($b['isi'] ?? ''), [], ['alignment' => 'both']);
                }
            } elseif ($tipe === 'tabel') {
                if (! empty($b['label'])) {
                    $this->section->addText($b['label'], ['bold' => true]);
                }
                $kolom = $b['kolom'] ?? [];
                if (count($kolom) > 0) {
                    $this->tabelBergaris($kolom, $b['baris'] ?? []);
                }
            }
            $this->kosong(self::UKURAN_ISI);
        }
        $this->kosong(self::UKURAN_ISI);
    }

    private function blokTandaTangan(array $spec): void
    {
        $p = $spec['penandatangan'];
        $kiri = ['indentation' => ['left' => self::INDENT_TTD]];

        $sisa = self::LEBAR - self::INDENT_TTD;
        $table = $this->section->addTable($this->gayaTabelTanpaGaris($sisa, self::INDENT_TTD));
        $garis = ['underline' => 'single'];

        $table->addRow();
        $table->addCell(self::KOL_TTD_LABEL)->addText('Ditetapkan di');
        $table->addCell($sisa - self::KOL_TTD_LABEL)->addText(': '.$spec['kota']);

        $table->addRow();
        $table->addCell(self::KOL_TTD_LABEL)->addText('Pada tanggal', $garis);
        $table->addCell($sisa - self::KOL_TTD_LABEL)->addText(': '.$spec['tanggal'], $garis);

        $this->section->addText($p['jabatan'].',', [], $kiri);
        $this->kosong(self::UKURAN_ISI);
        $this->kosong(self::UKURAN_ISI);
        $this->section->addText($p['nama'], [], $kiri);

        $id = trim(($p['label_id'] ?? '').($p['nomor_id'] ? ': '.$p['nomor_id'] : ''));
        if ($id !== '' && $id !== ':') {
            $this->section->addText($id, [], $kiri);
        }
    }

    private function blokTembusan(array $baris): void
    {
        $baris = array_values(array_filter(array_map('trim', $baris), fn ($l) => $l !== ''));
        if (! $baris) {
            return;
        }
        $this->kosong(self::UKURAN_ISI);
        $this->kosong(self::UKURAN_ISI);
        $this->section->addText('Tembusan :');

        $table = $this->section->addTable($this->gayaTabelTanpaGaris(self::LEBAR));
        foreach ($baris as $b) {
            [$nomor, $teks] = $this->pecahPenanda($b);
            $table->addRow();
            $table->addCell(self::KOL_TEMBUSAN_NOMOR)->addText($nomor);
            $table->addCell(self::LEBAR - self::KOL_TEMBUSAN_NOMOR)->addText($teks);
        }
    }

    // =====================================================================
    // Blok-blok kecil
    // =====================================================================

    /** Blok bertanda a./b. atau 1./2. : label | ":" | penanda | teks (rata kiri-kanan). */
    private function blokPoin(string $label, array $baris): void
    {
        $baris = array_values(array_filter(array_map('trim', $baris), fn ($l) => $l !== ''));
        if (! $baris) {
            $baris = ['-'];
        }
        $sisa = self::LEBAR - self::KOL_LABEL - self::KOL_TITIK2 - self::KOL_NOMOR;
        $table = $this->section->addTable($this->gayaTabelTanpaGaris(self::LEBAR));
        foreach ($baris as $i => $b) {
            [$nomor, $teks] = $this->pecahPenanda($b);
            $table->addRow();
            $table->addCell(self::KOL_LABEL)->addText($i === 0 ? $label : '');
            $table->addCell(self::KOL_TITIK2)->addText($i === 0 ? ':' : '');
            $table->addCell(self::KOL_NOMOR)->addText($nomor);
            $table->addCell($sisa)->addText($teks, [], ['alignment' => 'both']);
        }
    }

    /** Blok satu poin: "Pertama : teks" (teks mulai di 3,00 cm, rata kiri-kanan). */
    private function blokBerlabel(string $label, string $teks): void
    {
        $sisa = self::LEBAR - self::KOL_LABEL - self::KOL_TITIK2;
        $table = $this->section->addTable($this->gayaTabelTanpaGaris(self::LEBAR));
        $table->addRow();
        $table->addCell(self::KOL_LABEL)->addText($label);
        $table->addCell(self::KOL_TITIK2)->addText($label !== '' ? ':' : '');
        $table->addCell($sisa)->addText($teks, [], ['alignment' => 'both']);
    }

    private function tabelBergaris(array $kolom, array $baris): void
    {
        $jumlah = count($kolom);
        $lewar = $this->lewarKolom($kolom);

        $table = $this->section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'layout' => TableStyle::LAYOUT_FIXED,
            'width' => self::LEBAR,
            'unit' => 'dxa',
        ]);
        $table->addRow(null, ['tblHeader' => true, 'cantSplit' => true]);
        foreach ($kolom as $i => $nama) {
            $table->addCell($lewar[$i])->addText((string) $nama, ['bold' => true]);
        }
        foreach ($baris as $b) {
            $b = array_slice(array_values($b), 0, $jumlah);
            while (count($b) < $jumlah) {
                $b[] = '';
            }
            $table->addRow(null, ['cantSplit' => true]);
            foreach ($b as $i => $sel) {
                $table->addCell($lewar[$i])->addText((string) $sel);
            }
        }
    }

    /** Kolom bernama "No"/"No." sempit (1 cm), sisanya berbagi rata. */
    private function lewarKolom(array $kolom): array
    {
        $jumlah = count($kolom);
        $sempit = 567;
        $lewar = array_fill(0, $jumlah, 0);
        $adaNo = $jumlah > 1 && in_array(mb_strtolower(trim((string) $kolom[0])), ['no', 'no.'], true);
        $sisa = self::LEBAR - ($adaNo ? $sempit : 0);
        $n = $jumlah - ($adaNo ? 1 : 0);
        $per = intdiv($sisa, max($n, 1));
        foreach ($kolom as $i => $_) {
            $lewar[$i] = ($adaNo && $i === 0) ? $sempit : $per;
        }
        $lewar[$jumlah - 1] += self::LEBAR - array_sum($lewar); // sisa pembulatan

        return $lewar;
    }

    private function gayaTabelTanpaGaris(int $lewar, int $indent = 0): array
    {
        $gaya = [
            'cellMargin' => 0,
            'layout' => TableStyle::LAYOUT_FIXED,
            'width' => $lewar,
            'unit' => 'dxa',
        ];
        if ($indent > 0) {
            $gaya['indent'] = new TblWidth($indent);
        }

        return $gaya;
    }

    /** "a. Bahwa ..." -> ['a.', 'Bahwa ...']; tanpa penanda -> ['', teks]. */
    private function pecahPenanda(string $baris): array
    {
        if (preg_match('/^\s*([0-9]+[.)]|[a-zA-Z][.)])\s+(.*)$/us', $baris, $m)) {
            return [$m[1], $m[2]];
        }

        return ['', $baris];
    }

    /** Baris kosong; tingginya ditentukan ukuran huruf paragraf (seperti surat contoh). */
    private function kosong(int $ukuranPt): void
    {
        $this->section->addTextBreak(1, ['size' => $ukuranPt]);
    }
}
