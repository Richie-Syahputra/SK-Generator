<?php

namespace Database\Seeders;

use App\Models\JenisSk;
use Illuminate\Database\Seeder;

class JenisSkSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'kode' => 'PENGANGKATAN',
                'nama' => 'SK Pengangkatan',
                'kode_klasifikasi' => '800',
                'field_schema' => [
                    ['key' => 'nama_pegawai', 'label' => 'Nama Pegawai', 'type' => 'text'],
                    ['key' => 'jabatan_baru', 'label' => 'Jabatan yang Diangkat', 'type' => 'text'],
                    ['key' => 'tmt', 'label' => 'Terhitung Mulai Tanggal', 'type' => 'date'],
                    ['key' => 'dasar_pengangkatan', 'label' => 'Dasar Pengangkatan', 'type' => 'textarea'],
                    ['key' => 'masa_jabatan', 'label' => 'Masa Jabatan (tahun)', 'type' => 'number'],
                ],
                'template_menimbang' => "a. Bahwa dalam rangka kelancaran pelaksanaan tugas di sekolah, perlu diangkat {nama_pegawai} sebagai {jabatan_baru};\nb. Bahwa pengangkatan tersebut perlu ditetapkan dengan Surat Keputusan Kepala Sekolah.",
                'template_mengingat' => "1. Undang-undang RI Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional;\n2. Peraturan Pemerintah Nomor 19 Tahun 2005 tentang Standar Nasional Pendidikan.",
                'template_diktum' => "Pertama : Mengangkat {nama_pegawai} sebagai {jabatan_baru} terhitung mulai tanggal {tmt}\nKedua : Dasar pengangkatan ini adalah {dasar_pengangkatan}\nKetiga : Masa jabatan berlaku selama {masa_jabatan} tahun sejak tanggal ditetapkan\nKeempat : Surat keputusan ini berlaku sejak tanggal ditetapkan, dengan ketentuan akan diperbaiki sebagaimana mestinya apabili terdapat kekeliruan",
            ],
            [
                'kode' => 'PEMBENTUKAN_TIM',
                'nama' => 'SK Pembentukan Tim / Panitia',
                'kode_klasifikasi' => '421',
                'field_schema' => [
                    ['key' => 'nama_tim', 'label' => 'Nama Tim/Panitia', 'type' => 'text'],
                    ['key' => 'tujuan', 'label' => 'Tujuan Pembentukan', 'type' => 'textarea'],
                    ['key' => 'susunan_tim', 'label' => 'Susunan Anggota', 'type' => 'table', 'columns' => ['No', 'Nama', 'Jabatan Dinas', 'Jabatan Panitia', 'Keterangan']],
                ],
                'template_menimbang' => "a. Bahwa dalam rangka memperlancar pelaksanaan tugas di sekolah, perlu dibentuk dan ditetapkan {nama_tim};\nb. Bahwa susunan tim tersebut perlu ditetapkan dengan Surat Keputusan Kepala Sekolah.",
                'template_mengingat' => "1. Undang-undang RI Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional;\n2. Peraturan Menteri Pendidikan Nasional Nomor 19 Tahun 2007 tentang Standar Pengelolaan Pendidikan.",
                'template_diktum' => "Pertama : Menunjuk dan menetapkan nama-nama yang tercantum dalam Surat Keputusan ini sebagai {nama_tim}\nKedua : Segala biaya yang timbul akibat pelaksanaan keputusan ini dibebankan pada anggaran yang sesuai\nKetiga : Surat keputusan ini berlaku sejak tanggal ditetapkan, dengan ketentuan akan diperbaiki sebagaimana mestinya apabili terdapat kekeliruan",
            ],            [
                'kode' => 'PEMBERHENTIAN',
                'nama' => 'SK Pemberhentian',
                'kode_klasifikasi' => '800',
                'field_schema' => [
                    ['key' => 'nama_pegawai', 'label' => 'Nama Pegawai', 'type' => 'text'],
                    ['key' => 'jabatan_lama', 'label' => 'Jabatan yang Diberhentikan', 'type' => 'text'],
                    ['key' => 'alasan', 'label' => 'Alasan Pemberhentian', 'type' => 'select', 'options' => ['Habis Masa Jabatan', 'Mengundurkan Diri', 'Mutasi', 'Lainnya']],
                    ['key' => 'tanggal_efektif', 'label' => 'Tanggal Efektif', 'type' => 'date'],
                ],
                'template_menimbang' => "a. Bahwa sehubungan dengan {alasan}, perlu diberhentikan {nama_pegawai} dari jabatan {jabatan_lama};\nb. Bahwa pemberhentian tersebut perlu ditetapkan dengan Surat Keputusan Kepala Sekolah.",
                'template_mengingat' => "1. Undang-undang RI Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional;\n2. Peraturan Pemerintah Nomor 19 Tahun 2005 tentang Standar Nasional Pendidikan.",
                'template_diktum' => "Pertama : Memberhentikan {nama_pegawai} dari jabatan {jabatan_lama} terhitung mulai tanggal {tanggal_efektif}\nKedua : Alasan pemberhentian adalah {alasan}\nKetiga : Surat keputusan ini berlaku sejak tanggal ditetapkan, dengan ketentuan akan diperbaiki sebagaimana mestinya apabili terdapat kekeliruan",
            ],
            [
                'kode' => 'PENUGASAN_MUTASI',
                'nama' => 'SK Penugasan / Mutasi',
                'kode_klasifikasi' => '800',
                'field_schema' => [
                    ['key' => 'nama_pegawai', 'label' => 'Nama Pegawai', 'type' => 'text'],
                    ['key' => 'tugas_baru', 'label' => 'Tugas/Jabatan Baru', 'type' => 'text'],
                    ['key' => 'unit_asal', 'label' => 'Unit/Bagian Asal', 'type' => 'text'],
                    ['key' => 'unit_tujuan', 'label' => 'Unit/Bagian Tujuan', 'type' => 'text'],
                    ['key' => 'tmt', 'label' => 'Terhitung Mulai Tanggal', 'type' => 'date'],
                ],
                'template_menimbang' => "a. Bahwa dalam rangka penataan organisasi, perlu dilakukan penugasan/mutasi {nama_pegawai} dari {unit_asal} ke {unit_tujuan};\nb. Bahwa penugasan tersebut perlu ditetapkan dengan Surat Keputusan Kepala Sekolah.",
                'template_mengingat' => "1. Undang-undang RI Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional;\n2. Peraturan Menteri Pendidikan Nasional Nomor 19 Tahun 2007 tentang Standar Pengelolaan Pendidikan.",
                'template_diktum' => "Pertama : Menugaskan {nama_pegawai} sebagai {tugas_baru} di {unit_tujuan} terhitung mulai tanggal {tmt}\nKedua : Pegawai yang bersangkutan dibebastugaskan dari {unit_asal} terhitung sejak tanggal yang sama\nKetiga : Surat keputusan ini berlaku sejak tanggal ditetapkan, dengan ketentuan akan diperbaiki sebagaimana mestinya apabili terdapat kekeliruan",
            ],            [
                'kode' => 'KEBIJAKAN_PERATURAN',
                'nama' => 'SK Kebijakan / Peraturan',
                'kode_klasifikasi' => '400',
                'field_schema' => [
                    ['key' => 'judul_kebijakan', 'label' => 'Judul Kebijakan/Peraturan', 'type' => 'text'],
                    ['key' => 'isi_kebijakan', 'label' => 'Isi Kebijakan (poin-poin)', 'type' => 'textarea'],
                    ['key' => 'tanggal_berlaku', 'label' => 'Tanggal Mulai Berlaku', 'type' => 'date'],
                ],
                'template_menimbang' => "a. Bahwa dalam rangka tertib administrasi dan pelaksanaan kegiatan sekolah, perlu ditetapkan kebijakan tentang {judul_kebijakan};\nb. Bahwa kebijakan tersebut perlu ditetapkan dengan Surat Keputusan Kepala Sekolah.",
                'template_mengingat' => "1. Undang-undang RI Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional;\n2. Peraturan Pemerintah Nomor 19 Tahun 2005 tentang Standar Nasional Pendidikan.",
                'template_diktum' => "Pertama : Menetapkan kebijakan tentang {judul_kebijakan} sebagai berikut {isi_kebijakan}\nKedua : Kebijakan ini mulai berlaku sejak tanggal {tanggal_berlaku}\nKetiga : Surat keputusan ini berlaku sejak tanggal ditetapkan, dengan ketentuan akan diperbaiki sebagaimana mestinya apabili terdapat kekeliruan",
            ],
            [
                'kode' => 'PERUBAHAN',
                'nama' => 'SK Perubahan',
                'kode_klasifikasi' => '000',
                'field_schema' => [
                    ['key' => 'sk_yang_diubah', 'label' => 'Nomor SK yang Diubah', 'type' => 'text'],
                    ['key' => 'bagian_yang_diubah', 'label' => 'Bagian yang Diubah', 'type' => 'textarea'],
                    ['key' => 'isi_perubahan', 'label' => 'Isi Perubahan', 'type' => 'textarea'],
                    ['key' => 'tanggal_berlaku', 'label' => 'Tanggal Berlaku Perubahan', 'type' => 'date'],
                ],
                'template_menimbang' => "a. Bahwa terdapat hal yang perlu diubah pada Surat Keputusan Nomor {sk_yang_diubah};\nb. Bahwa perubahan tersebut perlu ditetapkan dengan Surat Keputusan Kepala Sekolah.",
                'template_mengingat' => "1. Undang-undang RI Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional;\n2. Peraturan Pemerintah Nomor 19 Tahun 2005 tentang Standar Nasional Pendidikan.",
                'template_diktum' => "Pertama : Mengubah {bagian_yang_diubah} pada Surat Keputusan Nomor {sk_yang_diubah}\nKedua : Perubahan tersebut adalah sebagai berikut {isi_perubahan}\nKetiga : Perubahan ini berlaku sejak tanggal {tanggal_berlaku}\nKeempat : Ketentuan lain yang tidak diubah dalam Surat Keputusan ini tetap berlaku sebagaimana mestinya",
            ],
            [
                'kode' => 'DELEGASI_WEWENANG',
                'nama' => 'SK Delegasi Wewenang',
                'kode_klasifikasi' => '000',
                'field_schema' => [
                    ['key' => 'nama_pemberi', 'label' => 'Nama Pemberi Wewenang', 'type' => 'text'],
                    ['key' => 'nama_penerima', 'label' => 'Nama Penerima Wewenang', 'type' => 'text'],
                    ['key' => 'lingkup_wewenang', 'label' => 'Lingkup Wewenang yang Didelegasikan', 'type' => 'textarea'],
                    ['key' => 'periode', 'label' => 'Periode Berlaku', 'type' => 'text'],
                ],
                'template_menimbang' => "a. Bahwa dalam rangka kelancaran pelaksanaan tugas, {nama_pemberi} perlu mendelegasikan sebagian wewenangnya kepada {nama_penerima};\nb. Bahwa delegasi wewenang tersebut perlu ditetapkan dengan Surat Keputusan Kepala Sekolah.",
                'template_mengingat' => "1. Undang-undang RI Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional;\n2. Peraturan Menteri Pendidikan Nasional Nomor 19 Tahun 2007 tentang Standar Pengelolaan Pendidikan.",
                'template_diktum' => "Pertama : Memberikan delegasi wewenang dari {nama_pemberi} kepada {nama_penerima}\nKedua : Lingkup wewenang yang didelegasikan meliputi {lingkup_wewenang}\nKetiga : Delegasi ini berlaku untuk periode {periode}\nKeempat : Surat keputusan ini berlaku sejak tanggal ditetapkan, dengan ketentuan akan diperbaiki sebagaimana mestinya apabili terdapat kekeliruan",
            ],
            [
                'kode' => 'PENGHAPUSAN_ASET',
                'nama' => 'SK Penghapusan Aset',
                'kode_klasifikasi' => '028',
                'field_schema' => [
                    ['key' => 'alasan_penghapusan', 'label' => 'Alasan Penghapusan', 'type' => 'textarea'],
                    ['key' => 'daftar_aset', 'label' => 'Daftar Aset', 'type' => 'table', 'columns' => ['No', 'Nama Aset', 'Kode Inventaris', 'Kondisi', 'Keterangan']],
                ],
                'template_menimbang' => "a. Bahwa terdapat aset sekolah yang sudah tidak layak digunakan sehingga perlu dihapuskan dari daftar inventaris;\nb. Bahwa penghapusan aset tersebut perlu ditetapkan dengan Surat Keputusan Kepala Sekolah.",
                'template_mengingat' => "1. Undang-undang RI Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional;\n2. Peraturan Pemerintah Nomor 19 Tahun 2005 tentang Standar Nasional Pendidikan.",
                'template_diktum' => "Pertama : Menghapuskan aset-aset yang tercantum dalam Surat Keputusan ini dari daftar inventaris sekolah\nKedua : Alasan penghapusan adalah {alasan_penghapusan}\nKetiga : Surat keputusan ini berlaku sejak tanggal ditetapkan, dengan ketentuan akan diperbaiki sebagaimana mestinya apabili terdapat kekeliruan",
            ],            [
                'kode' => 'ZONA_INTEGRITAS',
                'nama' => 'SK Penetapan Zona Integritas',
                'kode_klasifikasi' => '061',
                'field_schema' => [
                    ['key' => 'nama_program', 'label' => 'Nama Program/Zona Integritas', 'type' => 'text'],
                    ['key' => 'tim_pelaksana', 'label' => 'Tim Pelaksana', 'type' => 'table', 'columns' => ['No', 'Nama', 'Jabatan', 'Peran dalam Tim']],
                    ['key' => 'target_capaian', 'label' => 'Target Capaian', 'type' => 'textarea'],
                ],
                'template_menimbang' => "a. Bahwa dalam rangka mewujudkan tata kelola sekolah yang bersih dan melayani, perlu ditetapkan {nama_program};\nb. Bahwa penetapan tersebut perlu ditetapkan dengan Surat Keputusan Kepala Sekolah.",
                'template_mengingat' => "1. Undang-undang RI Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional;\n2. Peraturan Menteri Pendidikan Nasional Nomor 19 Tahun 2007 tentang Standar Pengelolaan Pendidikan.",
                'template_diktum' => "Pertama : Menetapkan {nama_program} beserta Tim Pelaksana yang tercantum dalam Surat Keputusan ini\nKedua : Target capaian program ini adalah {target_capaian}\nKetiga : Surat keputusan ini berlaku sejak tanggal ditetapkan, dengan ketentuan akan diperbaiki sebagaimana mestinya apabili terdapat kekeliruan",
            ],
            [
                'kode' => 'PENGHARGAAN_SANKSI',
                'nama' => 'SK Penghargaan / Sanksi',
                'kode_klasifikasi' => '800',
                'field_schema' => [
                    ['key' => 'nama_penerima', 'label' => 'Nama Pegawai/Siswa', 'type' => 'text'],
                    ['key' => 'jenis', 'label' => 'Jenis', 'type' => 'select', 'options' => ['Penghargaan', 'Sanksi']],
                    ['key' => 'alasan', 'label' => 'Alasan/Prestasi', 'type' => 'textarea'],
                    ['key' => 'bentuk', 'label' => 'Bentuk Penghargaan/Sanksi', 'type' => 'text'],
                ],
                'template_menimbang' => "a. Bahwa sehubungan dengan {alasan}, perlu diberikan {jenis} kepada {nama_penerima};\nb. Bahwa pemberian tersebut perlu ditetapkan dengan Surat Keputusan Kepala Sekolah.",
                'template_mengingat' => "1. Undang-undang RI Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional;\n2. Peraturan Pemerintah Nomor 19 Tahun 2005 tentang Standar Nasional Pendidikan.",
                'template_diktum' => "Pertama : Memberikan {jenis} kepada {nama_penerima} berupa {bentuk}\nKedua : Dasar pemberian ini adalah {alasan}\nKetiga : Surat keputusan ini berlaku sejak tanggal ditetapkan, dengan ketentuan akan diperbaiki sebagaimana mestinya apabili terdapat kekeliruan",
            ],
            [
                'kode' => 'PENGELOLA_LAYANAN',
                'nama' => 'SK Pengelola Layanan',
                'kode_klasifikasi' => '065',
                'field_schema' => [
                    ['key' => 'nama_layanan', 'label' => 'Nama Layanan', 'type' => 'text'],
                    ['key' => 'pengelola', 'label' => 'Susunan Pengelola', 'type' => 'table', 'columns' => ['No', 'Nama', 'Jabatan', 'Tugas']],
                    ['key' => 'periode', 'label' => 'Periode Pengelolaan', 'type' => 'text'],
                ],
                'template_menimbang' => "a. Bahwa dalam rangka kelancaran penyelenggaraan {nama_layanan}, perlu ditetapkan susunan pengelola;\nb. Bahwa susunan pengelola tersebut perlu ditetapkan dengan Surat Keputusan Kepala Sekolah.",
                'template_mengingat' => "1. Undang-undang RI Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional;\n2. Peraturan Menteri Pendidikan Nasional Nomor 19 Tahun 2007 tentang Standar Pengelolaan Pendidikan.",
                'template_diktum' => "Pertama : Menetapkan susunan pengelola {nama_layanan} sebagaimana tercantum dalam Surat Keputusan ini\nKedua : Masa pengelolaan berlaku untuk periode {periode}\nKetiga : Surat keputusan ini berlaku sejak tanggal ditetapkan, dengan ketentuan akan diperbaiki sebagaimana mestinya apabili terdapat kekeliruan",
            ],
            [
                'kode' => 'SOP_LAYANAN',
                'nama' => 'SK Standar Operasional Layanan',
                'kode_klasifikasi' => '065',
                'field_schema' => [
                    ['key' => 'nama_sop', 'label' => 'Nama SOP/Layanan', 'type' => 'text'],
                    ['key' => 'tujuan', 'label' => 'Tujuan SOP', 'type' => 'textarea'],
                    ['key' => 'langkah_langkah', 'label' => 'Langkah-langkah SOP', 'type' => 'textarea'],
                    ['key' => 'penanggung_jawab', 'label' => 'Penanggung Jawab Pelaksanaan', 'type' => 'text'],
                ],
                'template_menimbang' => "a. Bahwa dalam rangka memberikan pelayanan yang tertib dan terstandar, perlu ditetapkan {nama_sop};\nb. Bahwa standar operasional tersebut perlu ditetapkan dengan Surat Keputusan Kepala Sekolah.",
                'template_mengingat' => "1. Undang-undang RI Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional;\n2. Peraturan Menteri Pendidikan Nasional Nomor 19 Tahun 2007 tentang Standar Pengelolaan Pendidikan.",
                'template_diktum' => "Pertama : Menetapkan {nama_sop} dengan tujuan {tujuan}\nKedua : Langkah-langkah pelaksanaan adalah sebagai berikut {langkah_langkah}\nKetiga : Penanggung jawab pelaksanaan SOP ini adalah {penanggung_jawab}\nKeempat : Surat keputusan ini berlaku sejak tanggal ditetapkan, dengan ketentuan akan diperbaiki sebagaimana mestinya apabili terdapat kekeliruan",
            ],
        ];

        foreach ($data as $item) {
            JenisSk::updateOrCreate(['kode' => $item['kode']], $item);
        }
    }
}