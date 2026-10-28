<?php

namespace Database\Seeders;

use App\Models\JenisSk;
use Illuminate\Database\Seeder;

class Tahap9bPatchSeeder extends Seeder
{
    public function run(): void
    {
        $isi = [
            'PEMBERHENTIAN' => "1. Hasil evaluasi kinerja pegawai yang bersangkutan;\n2. Kebutuhan organisasi sekolah saat ini.",
            'PENUGASAN_MUTASI' => "1. Hasil rapat dewan guru tentang penataan tugas;\n2. Kebutuhan pelaksanaan kegiatan sekolah.",
            'KEBIJAKAN_PERATURAN' => "1. Hasil rapat dewan guru tentang kebijakan yang akan ditetapkan;\n2. Kebutuhan tertib administrasi sekolah.",
            'PERUBAHAN' => "1. Surat Keputusan yang akan diubah;\n2. Kebutuhan penyesuaian terhadap kondisi terkini.",
            'DELEGASI_WEWENANG' => "1. Hasil rapat dewan guru tentang pendelegasian wewenang;\n2. Kebutuhan kelancaran pelaksanaan tugas.",
            'PENGHAPUSAN_ASET' => "1. Hasil pemeriksaan kondisi aset sekolah;\n2. Ketentuan pengelolaan barang milik sekolah.",
            'ZONA_INTEGRITAS' => "1. Hasil rapat dewan guru tentang penetapan Zona Integritas;\n2. Komitmen bersama mewujudkan tata kelola yang bersih dan melayani.",
            'PENGHARGAAN_SANKSI' => "1. Hasil rapat dewan guru tentang pemberian penghargaan/sanksi;\n2. Prestasi/pelanggaran yang bersangkutan.",
            'PENGELOLA_LAYANAN' => "1. Hasil rapat dewan guru tentang pengelolaan layanan;\n2. Kebutuhan kelancaran penyelenggaraan layanan sekolah.",
            'SOP_LAYANAN' => "1. Hasil rapat dewan guru tentang standar operasional layanan;\n2. Kebutuhan pelayanan yang tertib dan terstandar.",
        ];

        foreach ($isi as $kode => $teks) {
            JenisSk::where('kode', $kode)->update(['template_memperhatikan' => $teks]);
        }
    }
}
