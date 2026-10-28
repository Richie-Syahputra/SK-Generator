# ===========================================================================
# siapkan-php.ps1 - menyiapkan PHP portabel milik SK Generator (Tahap 17A)
#
#  - PHP dipasang di folder runtime\php di dalam folder aplikasi.
#  - Tidak mengubah PATH / pengaturan Windows dan tidak butuh Administrator.
#  - php.ini dibuat otomatis (ekstensi yang dibutuhkan sudah diaktifkan).
#  - Kalau PHP sudah siap, skrip ini selesai dalam sekejap.
#
# Kode keluar: 0 = PHP siap dipakai, 1 = gagal.
# Semua teks sengaja ASCII supaya tampil benar di jendela perintah Windows.
# ===========================================================================

$ErrorActionPreference = 'Stop'
$ProgressPreference = 'SilentlyContinue'

# Cabang PHP yang dicoba berurutan (aplikasi butuh PHP 8.3 atau lebih baru)
$CabangPhp     = @('8.5', '8.4', '8.3')
$UrlDaftar     = 'https://windows.php.net/downloads/releases/releases.json'
$UrlRilis      = 'https://windows.php.net/downloads/releases/'
$UrlArsip      = 'https://windows.php.net/downloads/releases/archives/'
$VersiMinimum  = 80300
$UrlVcRedist   = 'https://aka.ms/vs/17/release/vc_redist.x64.exe'
# Kode keluar Windows bila DLL (0xC0000135) atau fungsi (0xC0000139) tidak ditemukan
$KodeVc        = @(-1073741515, -1073741511, 3221225781, 3221225785)
$EkstensiAktif = @('curl', 'fileinfo', 'gd', 'mbstring', 'openssl', 'pdo_sqlite', 'zip')
$EkstensiWajib = $EkstensiAktif + @('dom', 'xml', 'xmlwriter', 'libxml', 'ctype', 'tokenizer')

$Akar    = Split-Path -Parent $PSScriptRoot
$Runtime = Join-Path $Akar 'runtime'
$PhpDir  = Join-Path $Runtime 'php'
$PhpExe  = Join-Path $PhpDir 'php.exe'

function Write-Info($teks) { Write-Host "  [INFO]       $teks" }
function Write-Ok($teks)   { Write-Host "  [OK]         $teks" }
function Write-Gagal($teks, $saran = '') {
    Write-Host "  [GAGAL]      $teks"
    if ($saran -ne '') { Write-Host "               -> $saran" }
}

# Memilih rilis PHP dari daftar releases.json: cabang pertama yang tersedia,
# varian NTS x64 dengan nomor Visual Studio tertinggi.
function Select-RilisPhp($daftar) {
    foreach ($cabang in $CabangPhp) {
        $prop = $daftar.PSObject.Properties[$cabang]
        if ($null -eq $prop) { continue }
        $rilis = $prop.Value
        $varian = $rilis.PSObject.Properties |
            Where-Object { $_.Name -match '^nts-vs\d+-x64$' } |
            Sort-Object { [int]([regex]::Match($_.Name, 'vs(\d+)').Groups[1].Value) } -Descending |
            Select-Object -First 1
        if ($null -eq $varian -or $null -eq $varian.Value.zip) { continue }
        return [pscustomobject]@{
            Versi  = $rilis.version
            Zip    = $varian.Value.zip.path
            Sha256 = $varian.Value.zip.sha256
        }
    }
    return $null
}

function Get-Berkas($url, $tujuan) {
    Invoke-WebRequest -UseBasicParsing -Uri $url -OutFile $tujuan
}

function Install-Php {
    # Windows 10 lama kadang belum mengaktifkan TLS 1.2 untuk PowerShell
    [Net.ServicePointManager]::SecurityProtocol = [Net.ServicePointManager]::SecurityProtocol -bor [Net.SecurityProtocolType]::Tls12

    Write-Info 'PHP belum ada. Mengambil daftar versi PHP dari windows.php.net ...'
    $respons = Invoke-WebRequest -UseBasicParsing -Uri $UrlDaftar
    $rilis = Select-RilisPhp (ConvertFrom-Json $respons.Content)
    if ($null -eq $rilis) {
        throw 'Daftar rilis PHP tidak memuat PHP 8.3 sampai 8.5 untuk Windows x64.'
    }

    $null = New-Item -ItemType Directory -Force -Path $Runtime
    $zip = Join-Path $Runtime 'php-unduhan.zip'
    $tmp = Join-Path $Runtime 'php-sementara'
    Remove-Item -Recurse -Force $zip, $tmp -ErrorAction SilentlyContinue

    Write-Info ('Mengunduh PHP {0} (sekitar 30 MB), mohon tunggu ...' -f $rilis.Versi)
    try {
        Get-Berkas ($UrlRilis + $rilis.Zip) $zip
    } catch {
        Write-Info 'Alamat utama tidak tersedia, mencoba alamat arsip ...'
        Get-Berkas ($UrlArsip + $rilis.Zip) $zip
    }

    if ($rilis.Sha256) {
        $hash = (Get-FileHash -Algorithm SHA256 -Path $zip).Hash
        if ($hash -ne $rilis.Sha256) {
            Remove-Item $zip -Force
            throw 'Berkas PHP yang diunduh rusak (sidik jari SHA256 tidak cocok).'
        }
        Write-Ok 'Sidik jari SHA256 berkas PHP cocok'
    } else {
        Write-Info 'Daftar rilis tidak memuat SHA256, pemeriksaan sidik jari dilewati.'
    }

    Add-Type -AssemblyName System.IO.Compression.FileSystem
    [System.IO.Compression.ZipFile]::ExtractToDirectory($zip, $tmp)
    if (-not (Test-Path (Join-Path $tmp 'php.exe'))) {
        throw 'php.exe tidak ditemukan di dalam berkas yang diunduh.'
    }
    if (Test-Path $PhpDir) { Remove-Item -Recurse -Force $PhpDir }
    Move-Item -Path $tmp -Destination $PhpDir
    Remove-Item $zip -Force
    Write-Ok ('PHP {0} terpasang di runtime\php' -f $rilis.Versi)
}

# Membuat php.ini dari php.ini-production + pengaturan SK Generator.
# Dibuat ulang bila isinya berbeda (mis. folder aplikasi dipindahkan).
function New-PhpIni {
    $sumber = Join-Path $PhpDir 'php.ini-production'
    if (-not (Test-Path $sumber)) {
        throw 'php.ini-production tidak ada di folder runtime\php.'
    }
    $baris = New-Object System.Collections.Generic.List[string]
    foreach ($b in [System.IO.File]::ReadAllLines($sumber)) { $baris.Add($b) }
    $baris.Add('')
    $baris.Add('; --- Pengaturan SK Generator (dibuat otomatis oleh siapkan-php.ps1) ---')
    $baris.Add('extension_dir = "' + (Join-Path $PhpDir 'ext') + '"')
    foreach ($e in $EkstensiAktif) { $baris.Add('extension=' + $e) }
    $baris.Add('memory_limit = 512M')
    $baris.Add('upload_max_filesize = 32M')
    $baris.Add('post_max_size = 40M')
    $baris.Add('max_execution_time = 120')
    $baris.Add('date.timezone = Asia/Makassar')
    $isiBaru = ($baris -join "`r`n") + "`r`n"

    $tujuan = Join-Path $PhpDir 'php.ini'
    $isiLama = ''
    if (Test-Path $tujuan) { $isiLama = [System.IO.File]::ReadAllText($tujuan) }
    if ($isiLama -ne $isiBaru) {
        [System.IO.File]::WriteAllText($tujuan, $isiBaru, (New-Object System.Text.UTF8Encoding($false)))
        Write-Ok 'php.ini disiapkan (ekstensi aktif, batas unggah 32 MB)'
    }
}

# Menjalankan PHP milik aplikasi: cek versi dan ekstensi.
# Hasil: 'ok', 'vc' (komponen Visual C++ perlu dipasang) atau 'gagal'.
function Test-Php($percobaanUlang = $false) {
    $env:PHPRC = $PhpDir
    $env:PHP_INI_SCAN_DIR = ''
    $lama = $ErrorActionPreference
    $ErrorActionPreference = 'Continue'
    try {
        $keluaran = & $PhpExe -r 'echo PHP_VERSION_ID;' 2>$null
        $kode = $LASTEXITCODE
        $teksId = ''
        if ($null -ne $keluaran) { $teksId = ([string](@($keluaran)[-1])).Trim() }
        if ($KodeVc -contains $kode) {
            if (-not $percobaanUlang) { return 'vc' }
            Write-Gagal 'PHP tetap tidak bisa dijalankan: komponen Windows "Visual C++ Redistributable" belum terpasang dengan benar.' 'Pasang manual dari https://aka.ms/vs/17/release/vc_redist.x64.exe, restart komputer, lalu jalankan start.bat lagi.'
            return 'gagal'
        }
        if ($kode -ne 0 -or $teksId -notmatch '^\d+$') {
            Write-Gagal ('PHP tidak bisa dijalankan (kode keluar {0}).' -f $kode) 'Hapus folder runtime lalu jalankan start.bat lagi agar PHP diunduh ulang.'
            return 'gagal'
        }
        $id = [int]$teksId
        if ($id -lt $VersiMinimum) {
            Write-Gagal ('PHP di runtime\php terlalu lama (kode versi {0}).' -f $id) 'Hapus folder runtime lalu jalankan start.bat lagi.'
            return 'gagal'
        }
        $modul = @(& $PhpExe -m 2>$null | ForEach-Object { $_.Trim().ToLower() })
        $hilang = @($EkstensiWajib | Where-Object { $modul -notcontains $_ })
        if ($hilang.Count -gt 0) {
            Write-Gagal ('Ekstensi PHP belum aktif: ' + ($hilang -join ', ')) 'Hapus folder runtime lalu jalankan start.bat lagi.'
            return 'gagal'
        }
        return 'ok'
    } finally {
        $ErrorActionPreference = $lama
    }
}

# Memasang Visual C++ Redistributable (perlu izin Administrator / UAC satu kali)
function Install-VcRedist {
    $exe = Join-Path $Runtime 'vc_redist.x64.exe'
    Write-Info 'Komponen Windows "Visual C++ Redistributable" belum ada atau terlalu lama.'
    Write-Info 'Mengunduh (sekitar 25 MB). Saat jendela izin Windows (UAC) muncul, pilih Ya.'
    try {
        $null = New-Item -ItemType Directory -Force -Path $Runtime
        Get-Berkas $UrlVcRedist $exe
        $proses = Start-Process -FilePath $exe -ArgumentList '/install', '/quiet', '/norestart' -Verb RunAs -Wait -PassThru
        if (@(0, 1638, 3010) -notcontains $proses.ExitCode) {
            Write-Gagal ('Pemasangan Visual C++ Redistributable gagal (kode {0}).' -f $proses.ExitCode) 'Pasang manual dari https://aka.ms/vs/17/release/vc_redist.x64.exe lalu jalankan start.bat lagi.'
            return $false
        }
        Write-Ok 'Visual C++ Redistributable terpasang'
        return $true
    } catch {
        Write-Gagal ('Visual C++ Redistributable tidak terpasang: ' + $_.Exception.Message) 'Jalankan start.bat lagi dan pilih Ya pada jendela izin Windows, atau pasang manual dari https://aka.ms/vs/17/release/vc_redist.x64.exe'
        return $false
    } finally {
        Remove-Item $exe -Force -ErrorAction SilentlyContinue
    }
}

function Invoke-Main {
    Write-Host ''
    Write-Host '  Menyiapkan PHP SK Generator'
    Write-Host '  ---------------------------'
    try {
        if (-not [Environment]::Is64BitOperatingSystem) {
            Write-Gagal 'Windows 32-bit tidak didukung oleh SK Generator.' 'Gunakan komputer dengan Windows 64-bit.'
            return 1
        }
        if (Test-Path $PhpExe) {
            Write-Ok 'PHP milik aplikasi sudah ada (runtime\php)'
        } else {
            Install-Php
        }
        New-PhpIni
        $hasil = Test-Php
        if ($hasil -eq 'vc') {
            if (-not (Install-VcRedist)) { return 1 }
            $hasil = Test-Php $true
        }
        if ($hasil -ne 'ok') { return 1 }
        return 0
    } catch {
        Write-Gagal ('Gagal menyiapkan PHP: ' + $_.Exception.Message) 'Pastikan komputer terhubung ke internet, lalu jalankan start.bat lagi. Jika tetap gagal: unduh PHP "NTS x64" (zip) dari windows.php.net, ekstrak ke folder runtime\php sehingga php.exe berada langsung di dalamnya, lalu jalankan start.bat lagi.'
        return 1
    }
}

# Dijalankan hanya bila skrip dipanggil langsung (bukan di-dot-source untuk uji)
if ($MyInvocation.InvocationName -ne '.') {
    exit (Invoke-Main)
}
