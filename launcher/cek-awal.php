<?php

/**
 * Pemeriksaan awal SK Generator (dipanggil oleh start.bat).
 *
 *   php launcher\cek-awal.php                    periksa kesiapan, tulis storage\app\launcher.txt
 *   php launcher\cek-awal.php --tunggu PORT [DETIK]   tunggu sampai aplikasi merespons
 *
 * Kode keluar 0 = boleh dilanjutkan, selain 0 = ada masalah yang menghalangi.
 * Semua teks sengaja ASCII supaya tampil benar di jendela perintah Windows.
 */

const PHP_MINIMUM_ID = 80300;
const PHP_MINIMUM_TEKS = '8.3';
const PORT_AWAL = 8000;
const PORT_AKHIR = 8010;
const EKSTENSI_WAJIB = [
    'pdo_sqlite', 'mbstring', 'openssl', 'fileinfo', 'gd', 'zip', 'dom', 'xml',
    'xmlwriter', 'libxml', 'curl', 'ctype', 'tokenizer',
];
const TABEL_WAJIB = [
    'migrations', 'jenis_sk', 'sk_terbit', 'pejabat_penandatangan',
    'letterhead_assets', 'pengaturan_sekolah', 'penomoran_tahun',
];

$akar = dirname(__DIR__);
chdir($akar);

// ----------------------------------------------------------------------
// Fungsi bantu
// ----------------------------------------------------------------------

$jumlahGagal = 0;
$jumlahPeringatan = 0;

function ok(string $teks): void
{
    echo "  [OK]         $teks\n";
}

function info(string $teks): void
{
    echo "  [INFO]       $teks\n";
}

function peringatan(string $teks, string $saran = ''): void
{
    global $jumlahPeringatan;
    $jumlahPeringatan++;
    echo "  [PERINGATAN] $teks\n";
    if ($saran !== '') {
        echo "               -> $saran\n";
    }
}

function gagal(string $teks, string $saran = ''): void
{
    global $jumlahGagal;
    $jumlahGagal++;
    echo "  [GAGAL]      $teks\n";
    if ($saran !== '') {
        echo "               -> $saran\n";
    }
}

function bacaEnv(string $berkas): array
{
    $hasil = [];
    foreach (file($berkas, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $baris) {
        $baris = trim($baris);
        if ($baris === '' || $baris[0] === '#' || !str_contains($baris, '=')) {
            continue;
        }
        [$kunci, $nilai] = explode('=', $baris, 2);
        $hasil[trim($kunci)] = trim(trim($nilai), "\"'");
    }
    return $hasil;
}

function ambilHalaman(int $port): ?string
{
    $konteks = stream_context_create(['http' => ['timeout' => 3, 'ignore_errors' => true]]);
    $isi = @file_get_contents("http://127.0.0.1:$port/", false, $konteks);

    return $isi === false ? null : $isi;
}

function portTerpakai(int $port): bool
{
    $soket = @stream_socket_client("tcp://127.0.0.1:$port", $no, $pesan, 0.5);
    if ($soket) {
        fclose($soket);
        return true;
    }
    return false;
}

function aplikasiBerjalan(int $port): bool
{
    $isi = ambilHalaman($port);

    return $isi !== null && str_contains($isi, 'SK Generator');
}

// ----------------------------------------------------------------------
// Mode --tunggu : tunggu sampai server siap
// ----------------------------------------------------------------------

if (($argv[1] ?? '') === '--tunggu') {
    $port = (int) ($argv[2] ?? PORT_AWAL);
    $batas = (int) ($argv[3] ?? 40);
    for ($i = 0; $i < $batas; $i++) {
        if (aplikasiBerjalan($port)) {
            exit(0);
        }
        sleep(1);
    }
    exit(1);
}

// ----------------------------------------------------------------------
// Pemeriksaan kesiapan
// ----------------------------------------------------------------------

echo "\n  Memeriksa kesiapan SK Generator\n  -------------------------------\n";

// 1. Folder project yang benar
if (!is_file($akar . '/artisan') || !is_file($akar . '/routes/web.php')) {
    gagal('Peluncur tidak berada di folder project SK Generator.', 'Pastikan start.bat ada di folder yang sama dengan file "artisan".');
}

// 2. Versi PHP
$infoPhp = 'PHP ' . PHP_VERSION . ' (' . PHP_BINARY . ')';
if (PHP_VERSION_ID >= PHP_MINIMUM_ID) {
    ok($infoPhp);
} else {
    gagal("PHP terlalu lama: $infoPhp. Aplikasi butuh PHP " . PHP_MINIMUM_TEKS . ' atau lebih baru.', 'Kemungkinan ada lebih dari satu PHP di PC ini dan yang lama terpakai lebih dulu.');
    if (PHP_OS_FAMILY === 'Windows') {
        $daftar = trim((string) shell_exec('where php 2>nul'));
        if ($daftar !== '') {
            echo "               Urutan PHP di komputer ini (yang paling atas dipakai):\n";
            foreach (preg_split('/\R/', $daftar) as $baris) {
                echo "                 $baris\n";
            }
        }
    }
}

// 3. Ekstensi PHP
$hilang = array_values(array_filter(EKSTENSI_WAJIB, fn ($e) => !extension_loaded($e)));
if ($hilang === []) {
    ok('Ekstensi PHP lengkap');
} else {
    $ini = php_ini_loaded_file() ?: '(php.ini tidak ditemukan)';
    gagal('Ekstensi PHP belum aktif: ' . implode(', ', $hilang), "Aktifkan di php.ini ($ini) dengan menghapus tanda ; di depan baris extension=...");
}

// 4. Berkas dasar
$adaVendor = is_file($akar . '/vendor/autoload.php');
$adaEnv = is_file($akar . '/.env');
if (!$adaVendor) {
    gagal('Folder "vendor" tidak ada.', 'Jalankan: composer install');
}
$env = [];
if (!$adaEnv) {
    gagal('File ".env" tidak ada.', 'Salin .env.example menjadi .env, lalu jalankan: php artisan key:generate');
} else {
    $env = bacaEnv($akar . '/.env');
    if (($env['APP_KEY'] ?? '') === '') {
        gagal('APP_KEY di .env masih kosong.', 'Jalankan: php artisan key:generate');
    }
}
if ($adaVendor && $adaEnv && ($env['APP_KEY'] ?? '') !== '') {
    ok('File dasar aplikasi lengkap (vendor, .env)');
}

// 5. Database
$jalurDb = null;
if ($adaEnv) {
    if (($env['DB_CONNECTION'] ?? 'sqlite') !== 'sqlite') {
        peringatan('Koneksi database bukan sqlite; peluncur tidak memeriksa database.');
    } else {
        $jalur = $env['DB_DATABASE'] ?? '';
        if ($jalur === '') {
            $jalur = $akar . '/database/database.sqlite';
        } elseif (!file_exists($jalur)) {
            $jalur = $akar . '/' . ltrim($jalur, '/\\');
        }
        if (!is_file($jalur)) {
            gagal("File database tidak ditemukan: $jalur", 'Jalankan: php artisan migrate (akan membuat database baru yang kosong)');
        } else {
            $jalurDb = $jalur;
        }
    }
}

if ($jalurDb !== null && $adaVendor) {
    try {
        $pdo = new PDO('sqlite:' . $jalurDb);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $tabel = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table'")->fetchAll(PDO::FETCH_COLUMN);
        $sudahJalan = in_array('migrations', $tabel, true)
            ? $pdo->query('SELECT migration FROM migrations')->fetchAll(PDO::FETCH_COLUMN)
            : [];

        $semuaMigrasi = array_map(fn ($f) => basename($f, '.php'), glob($akar . '/database/migrations/*.php') ?: []);
        $tertunda = array_values(array_diff($semuaMigrasi, $sudahJalan));

        if ($tertunda !== []) {
            info(count($tertunda) . ' pembaruan database belum diterapkan, menerapkan sekarang...');
            if (filesize($jalurDb) > 0) {
                $cadangan = dirname($jalurDb) . '/backup-otomatis-' . date('Ymd-His') . '.sqlite';
                copy($jalurDb, $cadangan);
                info('Cadangan database dibuat: ' . basename($cadangan));
            }
            passthru('"' . PHP_BINARY . '" artisan migrate --force', $kodeMigrasi);
            if ($kodeMigrasi !== 0) {
                gagal('Pembaruan database gagal. Database belum diubah secara penuh.', 'Pulihkan dari file backup-otomatis-*.sqlite di folder database, lalu hubungi pengelola aplikasi.');
            } else {
                ok('Pembaruan database selesai');
                $pdo = new PDO('sqlite:' . $jalurDb);
                $tabel = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table'")->fetchAll(PDO::FETCH_COLUMN);
            }
        }

        if ($jumlahGagal === 0 || $tertunda === []) {
            $kurang = array_diff(TABEL_WAJIB, $tabel);
            if ($kurang !== []) {
                gagal('Tabel database belum lengkap: ' . implode(', ', $kurang), 'Jalankan: php artisan migrate');
            } else {
                ok('Database siap (' . count($tabel) . ' tabel)');
                $jumlahJenis = (int) $pdo->query('SELECT COUNT(*) FROM jenis_sk')->fetchColumn();
                if ($jumlahJenis === 0) {
                    info('Data awal (jenis SK dan pengaturan sekolah) belum ada, mengisi sekarang...');
                    passthru('"' . PHP_BINARY . '" artisan db:seed --force', $kodeSeed);
                    $jumlahJenis = (int) $pdo->query('SELECT COUNT(*) FROM jenis_sk')->fetchColumn();
                }
                if ($jumlahJenis === 0) {
                    peringatan('Data jenis SK masih kosong, menu Buat SK tidak akan menampilkan apa pun.', 'Jalankan: php artisan db:seed --force');
                } else {
                    ok("Data jenis SK ada ($jumlahJenis jenis)");
                }
                if (!is_writable($jalurDb)) {
                    gagal('File database tidak bisa ditulis.', 'Pastikan file bukan read-only: ' . $jalurDb);
                }
            }
        }
    } catch (Throwable $e) {
        gagal('Database tidak bisa dibuka: ' . $e->getMessage());
    }
}

// 6. Folder penyimpanan
$folder = [
    'storage/app/public/sk-output', 'storage/app/public/letterhead', 'storage/logs',
    'storage/framework/cache', 'storage/framework/sessions', 'storage/framework/views',
];
foreach ($folder as $f) {
    if (!is_dir($akar . '/' . $f)) {
        @mkdir($akar . '/' . $f, 0777, true);
    }
}
$tidakBisaTulis = array_filter(['storage/app/public/sk-output', 'storage/logs'], fn ($f) => !is_writable($akar . '/' . $f));
if ($tidakBisaTulis !== []) {
    gagal('Folder tidak bisa ditulis: ' . implode(', ', $tidakBisaTulis), 'Periksa izin folder project (jangan di lokasi yang dilindungi Windows).');
} else {
    ok('Folder penyimpanan siap');
}

if (!file_exists($akar . '/public/storage')) {
    info('Menautkan folder penyimpanan (storage:link)...');
    passthru('"' . PHP_BINARY . '" artisan storage:link', $kodeLink);
    if ($kodeLink !== 0 || !file_exists($akar . '/public/storage')) {
        gagal('Tautan folder penyimpanan (storage:link) gagal.', 'Buka PowerShell sebagai Administrator, masuk ke folder project, jalankan: php artisan storage:link');
    } else {
        ok('Tautan folder penyimpanan dibuat');
    }
} else {
    ok('Tautan folder penyimpanan ada');
}

// 7. LibreOffice (untuk PDF) dan font
$libre = $env['LIBREOFFICE_BINARY'] ?? '';
if ($libre === '') {
    peringatan('LIBREOFFICE_BINARY belum diisi di .env; PDF tidak bisa dibuat (file Word tetap jadi).');
} elseif (!is_file($libre)) {
    peringatan("LibreOffice tidak ditemukan di: $libre", 'PDF tidak bisa dibuat (file Word tetap jadi). Periksa LIBREOFFICE_BINARY di file .env.');
} else {
    ok('LibreOffice ditemukan');
}

if (PHP_OS_FAMILY === 'Windows') {
    $kandidat = [
        (getenv('WINDIR') ?: 'C:\\Windows') . '\\Fonts\\calibril.ttf',
        (getenv('LOCALAPPDATA') ?: '') . '\\Microsoft\\Windows\\Fonts\\calibril.ttf',
    ];
    if (array_filter($kandidat, 'is_file') === []) {
        peringatan('Font "Calibri Light" tidak terdeteksi.', 'PDF akan memakai font pengganti dan bisa sedikit berbeda dari tampilan di Word. Pasang Microsoft Office/font Calibri.');
    } else {
        ok('Font Calibri Light ada');
    }
}

// ----------------------------------------------------------------------
// Ringkasan + pemilihan port (hanya kalau semua lolos)
// ----------------------------------------------------------------------

echo "\n";
if ($jumlahGagal > 0) {
    echo "  HASIL: ada $jumlahGagal masalah yang harus diperbaiki dulu (lihat [GAGAL] di atas).\n";
    exit(1);
}

$port = null;
$mode = 'baru';
for ($p = PORT_AWAL; $p <= PORT_AKHIR; $p++) {
    if (portTerpakai($p) && aplikasiBerjalan($p)) {
        $port = $p;
        $mode = 'jalan';
        break;
    }
}
if ($port === null) {
    for ($p = PORT_AWAL; $p <= PORT_AKHIR; $p++) {
        if (!portTerpakai($p)) {
            $port = $p;
            break;
        }
    }
}
if ($port === null) {
    echo '  HASIL: port ' . PORT_AWAL . '-' . PORT_AKHIR . " semuanya sedang dipakai program lain.\n";
    exit(1);
}

file_put_contents($akar . '/storage/app/launcher.txt', "$port $mode");

if ($mode === 'jalan') {
    echo "  HASIL: SK Generator sudah berjalan di port $port.\n";
} else {
    echo $jumlahPeringatan > 0
        ? "  HASIL: siap dijalankan (ada $jumlahPeringatan peringatan, aplikasi tetap bisa dipakai).\n"
        : "  HASIL: semua siap.\n";
}
exit(0);