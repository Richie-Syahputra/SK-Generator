<?php

namespace App\Services;

use App\Models\JenisSk;
use App\Models\LetterheadAsset;
use App\Models\PejabatPenandatangan;
use App\Models\PengaturanSekolah;
use App\Models\SkTerbit;
use Carbon\Carbon;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use Symfony\Component\Process\Process;

class SkDocumentGenerator
{
    public function generate(JenisSk $jenisSk, array $data, array $opsi = []): SkTerbit
    {
        $skema = $jenisSk->field_schema ?? [];
        $data = $this->normalisasiPilihan($skema, $data);

        $siap = $this->siapkanOpsi($opsi);
        $pengaturan = PengaturanSekolah::ambil();

        [$nomorUrut, $nomorSurat] = app(PenomoranService::class)->buat($jenisSk, $siap['tanggal']);

        $dataTampil = $this->formatTanggalIsian($skema, $data);

        $spec = $this->specDasar($siap, $pengaturan, $nomorSurat) + [
            'mode' => 'standar',
            'judul' => preg_replace('/^SK\s+/i', '', $jenisSk->nama),
            'menimbang' => $this->barisTemplate($jenisSk->template_menimbang, $dataTampil),
            'mengingat' => $this->barisTemplate($jenisSk->template_mengingat, $dataTampil),
            'memperhatikan' => $this->barisTemplate($jenisSk->template_memperhatikan, $dataTampil),
            'diktum' => $this->diktumDariTemplate($jenisSk->template_diktum, $dataTampil),
            'tabel' => $this->tabelDariField($skema, $data),
        ];

        $phpWord = (new SkLayoutBuilder)->build($spec);

        return $this->simpanDanKonversi($phpWord, $jenisSk->kode, [
            'jenis_sk_id' => $jenisSk->id,
            'nomor_surat' => $nomorSurat,
            'nomor_urut' => $nomorUrut,
            'tanggal_sk' => $siap['tanggal'],
            'data_isian' => $data,
        ] + $this->catatanOpsi($siap));
    }

    public function generateCustom(JenisSk $jenisSk, string $judul, array $blocks, array $opsi = []): SkTerbit
    {
        $siap = $this->siapkanOpsi($opsi);
        $pengaturan = PengaturanSekolah::ambil();

        [$nomorUrut, $nomorSurat] = app(PenomoranService::class)->buat($jenisSk, $siap['tanggal']);

        $spec = $this->specDasar($siap, $pengaturan, $nomorSurat) + [
            'mode' => 'custom',
            'judul' => $judul,
            'blok' => $blocks,
        ];

        $phpWord = (new SkLayoutBuilder)->build($spec);

        return $this->simpanDanKonversi($phpWord, 'CUSTOM', [
            'jenis_sk_id' => $jenisSk->id,
            'nomor_surat' => $nomorSurat,
            'nomor_urut' => $nomorUrut,
            'tanggal_sk' => $siap['tanggal'],
            'data_isian' => ['judul_sk' => $judul, 'blocks' => $blocks],
        ] + $this->catatanOpsi($siap));
    }

    /**
     * Terjemahkan pilihan form ("opsi") menjadi data siap pakai.
     * Kunci $opsi: tanggal, kop (id kop | aktif | tanpa | kosong),
     * penandatangan (id | aktif | manual), manual[nama|jabatan|jabatan_judul|label_id|nomor_id].
     */
    private function siapkanOpsi(array $opsi): array
    {
        try {
            $tanggal = ! empty($opsi['tanggal']) ? Carbon::parse($opsi['tanggal'])->startOfDay() : Carbon::today();
        } catch (\Throwable $e) {
            throw new \Exception('Tanggal penetapan tidak valid.');
        }

        $pilihanKop = (string) ($opsi['kop'] ?? 'aktif');
        $kopMode = 'tanpa';
        $kopPath = null;
        $kopId = null;

        if ($pilihanKop === 'kosong') {
            $kopMode = 'kosong';
        } elseif ($pilihanKop !== 'tanpa') {
            $kop = $pilihanKop === 'aktif' ? LetterheadAsset::aktif() : LetterheadAsset::find((int) $pilihanKop);
            $path = $kop ? storage_path('app/public/'.$kop->file_path) : null;
            if ($kop && $path && is_file($path)) {
                $kopMode = 'kop';
                $kopPath = $path;
                $kopId = $kop->id;
            }
        }

        $pilihanPejabat = (string) ($opsi['penandatangan'] ?? 'aktif');
        if ($pilihanPejabat === 'manual') {
            $m = (array) ($opsi['manual'] ?? []);
            $nama = trim((string) ($m['nama'] ?? ''));
            $jabatan = trim((string) ($m['jabatan'] ?? ''));
            if ($nama === '' || $jabatan === '') {
                throw new \Exception('Penandatangan manual: nama lengkap dan jabatan wajib diisi.');
            }
            $pejabat = [
                'id' => null,
                'nama' => $nama,
                'jabatan' => $jabatan,
                'jabatan_judul' => trim((string) ($m['jabatan_judul'] ?? '')) ?: $jabatan,
                'label_id' => trim((string) ($m['label_id'] ?? '')),
                'nomor_id' => trim((string) ($m['nomor_id'] ?? '')),
            ];
        } else {
            $model = $pilihanPejabat === 'aktif' ? PejabatPenandatangan::aktif() : PejabatPenandatangan::find((int) $pilihanPejabat);
            if (! $model) {
                throw new \Exception('Pejabat penandatangan belum diaktifkan. Lengkapi dulu di menu Pengaturan.');
            }
            $pejabat = [
                'id' => $model->id,
                'nama' => $model->nama_lengkap,
                'jabatan' => $model->jabatan,
                'jabatan_judul' => $model->jabatan_judul ?: $model->jabatan,
                'label_id' => (string) $model->label_id,
                'nomor_id' => (string) $model->nomor_id,
            ];
        }

        return [
            'tanggal' => $tanggal,
            'kop_mode' => $kopMode,
            'kop_path' => $kopPath,
            'kop_id' => $kopId,
            'pejabat' => $pejabat,
        ];
    }

    private function catatanOpsi(array $siap): array
    {
        return [
            'kop_mode' => $siap['kop_mode'],
            'letterhead_asset_id' => $siap['kop_id'],
            'penandatangan_id' => $siap['pejabat']['id'],
            'penandatangan_nama' => $siap['pejabat']['nama'],
            'penandatangan_jabatan' => $siap['pejabat']['jabatan'],
        ];
    }

    private function specDasar(array $siap, PengaturanSekolah $pengaturan, string $nomorSurat): array
    {
        $p = $siap['pejabat'];

        return [
            'kop_path' => $siap['kop_path'],
            'kop_ruang' => $siap['kop_mode'] === 'kosong',
            'jabatan_judul' => $p['jabatan_judul'],
            'nomor' => $nomorSurat,
            'kota' => $pengaturan->kota,
            'tanggal' => $siap['tanggal']->copy()->locale('id')->translatedFormat('d F Y'),
            'penandatangan' => [
                'jabatan' => $p['jabatan'],
                'nama' => $p['nama'],
                'label_id' => $p['label_id'],
                'nomor_id' => $p['nomor_id'],
            ],
            'tembusan' => preg_split('/\R/u', (string) $pengaturan->tembusan_default) ?: [],
        ];
    }

    private function barisTemplate(?string $template, array $data): array
    {
        $hasil = [];
        foreach (preg_split('/\R/u', (string) $template) as $baris) {
            $baris = trim($baris);
            if ($baris !== '') {
                $hasil[] = $this->isiTemplate($baris, $data);
            }
        }

        return $hasil;
    }

    private function diktumDariTemplate(?string $template, array $data): array
    {
        $hasil = [];
        foreach (preg_split('/\R/u', (string) $template) as $baris) {
            $baris = trim($baris);
            if ($baris === '') {
                continue;
            }
            if (str_contains($baris, ':')) {
                [$label, $isi] = array_map('trim', explode(':', $baris, 2));
            } else {
                $label = '';
                $isi = $baris;
            }
            $hasil[] = ['label' => $label, 'teks' => $this->isiTemplate($isi, $data)];
        }

        return $hasil;
    }

    private function tabelDariField(array $fieldSchema, array $data): array
    {
        $hasil = [];
        foreach ($fieldSchema as $field) {
            if (($field['type'] ?? '') === 'table' && ! empty($data[$field['key']])) {
                $hasil[] = [
                    'judul' => null,
                    'kolom' => $field['columns'],
                    'baris' => array_values($data[$field['key']]),
                ];
            }
        }

        return $hasil;
    }

    private function formatTanggalIsian(array $fieldSchema, array $data): array
    {
        foreach ($fieldSchema as $field) {
            $kunci = $field['key'] ?? null;
            if (($field['type'] ?? '') === 'date' && $kunci && ! empty($data[$kunci]) && is_string($data[$kunci])) {
                try {
                    $data[$kunci] = Carbon::parse($data[$kunci])->locale('id')->translatedFormat('d F Y');
                } catch (\Throwable $e) {
                    // biarkan apa adanya kalau bukan data tanggal yang valid
                }
            }
        }

        return $data;
    }

    /** Pilihan "Lainnya (isi manual)": ganti nilai penanda dengan teks yang diketik pengguna. */
    private function normalisasiPilihan(array $fieldSchema, array $data): array
    {
        foreach ($fieldSchema as $field) {
            if (($field['type'] ?? '') !== 'select' || empty($field['key'])) {
                continue;
            }

            $kunci = $field['key'];
            $kunciManual = $kunci . '__manual';

            if (($data[$kunci] ?? null) === '__manual__') {
                $isi = trim((string) ($data[$kunciManual] ?? ''));
                if ($isi === '') {
                    throw new \Exception('Isian manual untuk "' . ($field['label'] ?? $kunci) . '" wajib diisi.');
                }
                $data[$kunci] = $isi;
            }

            unset($data[$kunciManual]);
        }

        return $data;
    }

    private function simpanDanKonversi(PhpWord $phpWord, string $kodeUntukNamaFile, array $dataSkTerbit): SkTerbit
    {
        $folder = storage_path('app/public/sk-output');
        if (! is_dir($folder)) {
            mkdir($folder, 0755, true);
        }

        $namaFile = 'SK-'.$kodeUntukNamaFile.'-'.time();
        $docxPath = $folder.'/'.$namaFile.'.docx';
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($docxPath);

        $pdfPath = $folder.'/'.$namaFile.'.pdf';
        $binary = env('LIBREOFFICE_BINARY', 'soffice');
        $loCmd = escapeshellarg($binary).' --headless --nofirststartwizard --convert-to pdf --outdir '.escapeshellarg($folder).' '.escapeshellarg($docxPath);
        $process = Process::fromShellCommandline($loCmd, base_path());
        $env = getenv();
        $process->setEnv($env);
        $process->setTimeout(120);
        set_time_limit(0);
        $process->run();

        if (! $process->isSuccessful() || ! file_exists($pdfPath)) {
            throw new \Exception('Gagal konversi ke PDF. Exit code: '
                .$process->getExitCode()
                .', Output: '.$process->getOutput()
                .', Error: '.$process->getErrorOutput()
                .', Binary: '.$binary
                .', Cmd: '.$loCmd
            );
        }

        return SkTerbit::create($dataSkTerbit + [
            'file_docx' => 'sk-output/'.$namaFile.'.docx',
            'file_pdf' => 'sk-output/'.$namaFile.'.pdf',
        ]);
    }

    private function isiTemplate(?string $template, array $data): string
    {
        if (! $template) {
            return '-';
        }
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                continue;
            }
            $value = str_replace(["\r\n", "\n", "\r"], ' ', (string) $value);
            $template = str_replace('{'.$key.'}', $value, $template);
        }

        return $template;
    }
}
