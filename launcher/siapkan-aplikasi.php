<?php

/**
 * Penyiapan otomatis SK Generator (dipanggil oleh start.bat, sebelum cek-awal.php).
 *
 *   php launcher\siapkan-aplikasi.php
 *
 * Hanya mengerjakan yang BELUM ada dan tidak menyentuh yang sudah ada:
 *   1. file .env (nilai untuk pemakaian sehari-hari) dan APP_KEY
 *   2. folder penyimpanan
 *   3. folder vendor (composer install; butuh internet)
 *   4. file database kosong
 *   5. tautan public\storage (junction, tanpa hak Administrator)
 *
 * Migrasi database dan pengisian data awal dikerjakan oleh cek-awal.php.
 * Kode keluar 0 = siap, 1 = gagal. Semua teks sengaja ASCII.
 *
 * Catatan pengujian: bila variabel lingkungan SKGEN_COMPOSER_PHAR berisi path
 * sebuah composer.phar, berkas itu disalin (tidak diunduh).
 */

const URL_COMPOSER = 'https://getcomposer.org/download/latest-stable/composer.phar';

$akar = dirname(__DIR__);
chdir($akar);

// ----------------------------------------------------------------------
// Fungsi bantu
// ----------------------------------------------------------------------

$judulSudahTampil = false;
$jumlahGagal = 0;

function cetak(string $teks): void
{
    global $judulSudahTampil;
    if (!$judulSudahTampil) {
        echo "\n  Menyiapkan aplikasi\n  -------------------\n";
        $judulSudahTampil = true;
    }
    echo $teks . "\n";
}

function ok(string $teks): void
{
    cetak("  [OK]         $teks");
}

function info(string $teks): void
{
    cetak("  [INFO]       $teks");
}

function peringatan(string $teks, string $saran = ''): void
{
    cetak("  [PERINGATAN] $teks" . ($saran !== '' ? "\n               -> $saran" : ''));
}

function gagal(string $teks, string $saran = ''): void
{
    global $jumlahGagal;
    $jumlahGagal++;
    cetak("  [GAGAL]      $teks" . ($saran !== '' ? "\n               -> $saran" : ''));
}

function nilaiEnv(string $isi, string $kunci): string
{
    if (preg_match('/^' . preg_quote($kunci, '/') . '=(.*)$/m', $isi, $cocok)) {
        return trim(trim($cocok[1]), "\"'");
    }
    return '';
}

function aturEnv(string $isi, string $kunci, string $nilai): string
{
    $baris = $kunci . '=' . $nilai;
    $pola = '/^' . preg_quote($kunci, '/') . '=.*$/m';
    if (preg_match($pola, $isi)) {
        return preg_replace_callback($pola, fn () => $baris, $isi, 1);
    }
    return rtrim($isi) . "\n" . $baris . "\n";
}

function unduh(string $url, string $tujuan): bool
{
    if (PHP_OS_FAMILY !== 'Windows') {
        return false;
    }
    $perintah = 'powershell -NoProfile -ExecutionPolicy Bypass -Command "'
        . '$ErrorActionPreference = \'Stop\'; $ProgressPreference = \'SilentlyContinue\'; '
        . '[Net.ServicePointManager]::SecurityProtocol = [Net.ServicePointManager]::SecurityProtocol -bor [Net.SecurityProtocolType]::Tls12; '
        . 'Invoke-WebRequest -UseBasicParsing -Uri \'' . $url . '\' -OutFile \'' . $tujuan . '\'"';
    passthru($perintah, $kode);

    return $kode === 0 && is_file($tujuan);
}

function pasangVendor(string $akar): bool
{
    if (!is_file($akar . '/composer.json') || !is_file($akar . '/composer.lock')) {
        gagal('composer.json / composer.lock tidak ada di folder project.', 'Salin ulang folder project secara lengkap.');
        return false;
    }

    $runtime = $akar . '/runtime';
    if (!is_dir($runtime)) {
        mkdir($runtime, 0777, true);
    }
    $phar = $runtime . '/composer.phar';
    $php = '"' . PHP_BINARY . '"';

    $siap = fn () => is_file($phar) && filesize($phar) > 1000000;
    if (!$siap()) {
        @unlink($phar);
        $contoh = getenv('SKGEN_COMPOSER_PHAR');
        if ($contoh !== false && $contoh !== '' && is_file($contoh)) {
            copy($contoh, $phar);
        } else {
            info('Mengunduh Composer (pemasang komponen) ...');
            unduh(URL_COMPOSER, $phar);
        }
    }
    if (!$siap()) {
        @unlink($phar);
        gagal('Composer gagal diunduh.', 'Pastikan komputer terhubung ke internet, lalu jalankan start.bat lagi.');
        return false;
    }

    putenv('COMPOSER_HOME=' . $runtime . '/composer-home');
    putenv('COMPOSER_CACHE_DIR=' . $runtime . '/composer-cache');
    putenv('COMPOSER_NO_INTERACTION=1');

    info('Memasang komponen aplikasi, butuh internet dan bisa memakan beberapa menit. Mohon tunggu ...');
    passthru($php . ' "' . $phar . '" install --no-dev --optimize-autoloader --no-interaction --no-ansi --no-progress', $kode);

    if ($kode !== 0 || !is_file($akar . '/vendor/autoload.php')) {
        gagal('Pemasangan komponen aplikasi gagal (lihat pesan di atas).', 'Pastikan internet aktif (dan tidak diblokir), lalu jalankan start.bat lagi. Proses akan dilanjutkan dari yang sudah terunduh.');
        return false;
    }
    ok('Komponen aplikasi (vendor) terpasang');

    return true;
}

// ----------------------------------------------------------------------
// 1. File .env dan APP_KEY
// ----------------------------------------------------------------------

$jalurEnv = $akar . '/.env';
if (!is_file($jalurEnv)) {
    if (!is_file($akar . '/.env.example')) {
        gagal('File .env.example tidak ada, .env tidak bisa dibuat.', 'Salin ulang folder project secara lengkap.');
    } else {
        $isi = (string) file_get_contents($akar . '/.env.example');
        $nilaiAwal = [
            'APP_NAME' => '"SK Generator"',
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
            'APP_URL' => 'http://127.0.0.1:8000',
            'APP_TIMEZONE' => 'Asia/Makassar',
            'LOG_LEVEL' => 'warning',
        ];
        // Path Windows di dalam tanda kutip GANDA dianggap rusak oleh pembaca .env
        // (\P, \L, dst. dikira karakter khusus). Kutip TUNGGAL membuatnya apa adanya.
        $libre = nilaiEnv($isi, 'LIBREOFFICE_BINARY');
        if ($libre !== '') {
            $nilaiAwal['LIBREOFFICE_BINARY'] = "'" . $libre . "'";
        }
        foreach ($nilaiAwal as $kunci => $nilai) {
            $isi = aturEnv($isi, $kunci, $nilai);
        }
        file_put_contents($jalurEnv, $isi);
        info('File pengaturan (.env) dibuat');
    }
}

if (is_file($jalurEnv)) {
    $isi = (string) file_get_contents($jalurEnv);
    if (nilaiEnv($isi, 'APP_KEY') === '') {
        $isi = aturEnv($isi, 'APP_KEY', 'base64:' . base64_encode(random_bytes(32)));
        file_put_contents($jalurEnv, $isi);
        info('Kunci aplikasi (APP_KEY) dibuat');
    }
}

// ----------------------------------------------------------------------
// 2. Folder penyimpanan
// ----------------------------------------------------------------------

foreach ([
    'storage/app/public/sk-output', 'storage/app/public/letterhead', 'storage/logs',
    'storage/framework/cache', 'storage/framework/sessions', 'storage/framework/views',
    'bootstrap/cache', 'database',
] as $folder) {
    if (!is_dir($akar . '/' . $folder)) {
        @mkdir($akar . '/' . $folder, 0777, true);
    }
}

// ----------------------------------------------------------------------
// 3. Folder vendor
// ----------------------------------------------------------------------

if ($jumlahGagal === 0 && !is_file($akar . '/vendor/autoload.php')) {
    pasangVendor($akar);
}

// ----------------------------------------------------------------------
// 4. File database kosong (isinya dibuat oleh cek-awal.php)
// ----------------------------------------------------------------------

if ($jumlahGagal === 0 && is_file($jalurEnv)) {
    $isi = (string) file_get_contents($jalurEnv);
    $koneksi = nilaiEnv($isi, 'DB_CONNECTION');
    if ($koneksi === '' || $koneksi === 'sqlite') {
        $jalur = nilaiEnv($isi, 'DB_DATABASE');
        if ($jalur === '') {
            $jalur = $akar . '/database/database.sqlite';
        } elseif (!file_exists($jalur)) {
            $jalur = $akar . '/' . ltrim($jalur, '/\\');
        }
        if (!is_file($jalur)) {
            if (!is_dir(dirname($jalur))) {
                @mkdir(dirname($jalur), 0777, true);
            }
            if (@touch($jalur)) {
                info('File database baru dibuat');
            } else {
                gagal("File database tidak bisa dibuat: $jalur", 'Periksa izin folder project.');
            }
        }
    }
}

// ----------------------------------------------------------------------
// 5. Tautan public\storage (junction, tidak butuh Administrator)
// ----------------------------------------------------------------------

$tautan = $akar . '/public/storage';
$sasaran = $akar . '/storage/app/public';
if ($jumlahGagal === 0 && !file_exists($tautan) && !is_link($tautan)) {
    if (PHP_OS_FAMILY === 'Windows') {
        $tautanWin = str_replace('/', '\\', $tautan);
        $sasaranWin = str_replace('/', '\\', $sasaran);
        exec('powershell -NoProfile -Command "New-Item -ItemType Junction -Path \'' . $tautanWin . '\' -Target \'' . $sasaranWin . '\' | Out-Null" 2>&1', $keluaran, $kodeTautan);
    } else {
        @symlink($sasaran, $tautan);
    }
    if (file_exists($tautan)) {
        ok('Tautan folder penyimpanan dibuat');
    } else {
        peringatan('Tautan folder penyimpanan belum bisa dibuat (akan dicoba lagi oleh pemeriksaan berikutnya).');
    }
}

exit($jumlahGagal > 0 ? 1 : 0);
