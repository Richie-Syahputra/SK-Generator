<?php

namespace App\Console\Commands;

use App\Models\LetterheadAsset;
use App\Models\PejabatPenandatangan;
use Illuminate\Console\Command;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Symfony\Component\Process\Process;

class TestPipeline extends Command
{
    protected $signature = 'test:pipeline';
    protected $description = 'Uji coba pipeline: ambil kop surat & pejabat aktif dari Pengaturan, generate DOCX, convert ke PDF';

    public function handle()
    {
        $letterhead = LetterheadAsset::aktif();
        $pejabat = PejabatPenandatangan::aktif();

        if (!$letterhead) {
            $this->error('Belum ada kop surat aktif. Buka /pengaturan/kop-surat, upload lalu klik "Jadikan Aktif".');
            return Command::FAILURE;
        }

        if (!$pejabat) {
            $this->error('Belum ada pejabat penandatangan aktif. Buka /pengaturan/pejabat, tambah lalu klik "Jadikan Aktif".');
            return Command::FAILURE;
        }

        $folder = storage_path('app/public/sk-output');
        if (!is_dir($folder)) {
            mkdir($folder, 0755, true);
        }

        $this->info('Membuat file DOCX dengan kop surat & data pejabat aktif...');

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        $letterheadPath = storage_path('app/public/' . $letterhead->file_path);
        $header = $section->addHeader();

        $imageInfo = @getimagesize($letterheadPath);
        $targetWidthPt = 470;
        $targetHeightPt = $imageInfo ? round($targetWidthPt * ($imageInfo[1] / $imageInfo[0])) : 90;

        $header->addImage($letterheadPath, [
            'width' => $targetWidthPt,
            'height' => $targetHeightPt,
            'align' => 'center',
        ]);

        $section->addText('UJI COBA PIPELINE - TAHAP 2', ['bold' => true, 'size' => 14]);
        $section->addText(
                        'Kop surat di atas dan data penandatangan di bawah diambil otomatis dari menu '
            . 'Pengaturan, bukan ditulis manual di kode.'
        );

        $section->addTextBreak(2);
        $section->addText('Ditetapkan di : Banjarmasin');
        $section->addText('Pada tanggal : ' . now()->translatedFormat('d F Y'));
        $section->addText($pejabat->jabatan . ',');
        $section->addTextBreak(3);
        $section->addText($pejabat->nama_lengkap, ['underline' => 'single']);
        $section->addText(trim($pejabat->label_id . '. ' . $pejabat->nomor_id));

        $docxPath = $folder . '/test.docx';
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($docxPath);

        $this->info("DOCX berhasil dibuat: $docxPath");
        $this->info('Menjalankan konversi ke PDF via LibreOffice...');

        $pdfPath = $folder . '/test.pdf';
        $binary = env('LIBREOFFICE_BINARY', 'soffice');

        $process = new Process([
            $binary, '--headless', '--convert-to', 'pdf', '--outdir', $folder, $docxPath
        ]);
        $process->setTimeout(60);
        $process->run();

        if (!$process->isSuccessful() || !file_exists($pdfPath)) {
            $this->error('GAGAL konversi ke PDF.');
            $this->error('Error output: ' . $process->getErrorOutput());
            return Command::FAILURE;
        }

        $this->info("BERHASIL. PDF dibuat: $pdfPath");
        $this->info('Buka PDF tersebut. Kop surat dan nama penandatangan harus sesuai data yang diisi di menu Pengaturan.');

        return Command::SUCCESS;
    }
}
