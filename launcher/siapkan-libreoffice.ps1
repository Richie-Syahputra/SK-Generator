# ===========================================================================
# siapkan-libreoffice.ps1 - memasang LibreOffice otomatis bila belum ada (Tahap 17C)
#
# LibreOffice dibutuhkan untuk membuat PDF. Skrip ini:
#  - mengecek LIBREOFFICE_BINARY di .env, lalu lokasi umum dan registri;
#  - bila ditemukan di tempat lain, memperbarui .env;
#  - bila belum ada: mengunduh installer resmi (.msi) dari documentfoundation.org,
#    memeriksa sidik jari SHA256, lalu memasangnya secara senyap. Windows akan
#    meminta izin Administrator (UAC) satu kali;
#  - mengisi LIBREOFFICE_BINARY di .env.
#
# Tidak fatal: bila gagal, aplikasi tetap jalan (file Word jadi, PDF tidak).
# Untuk melewati pemasangan otomatis, buat file kosong: storage\app\lewati-libreoffice.txt
# Kode keluar selalu 0. Semua teks sengaja ASCII.
# ===========================================================================

$ErrorActionPreference = 'Stop'
$ProgressPreference = 'SilentlyContinue'

$UrlStable   = 'https://download.documentfoundation.org/libreoffice/stable/'
$MaksCoba    = 3

$Akar        = Split-Path -Parent $PSScriptRoot
$Runtime     = Join-Path $Akar 'runtime'
$FileEnv     = Join-Path $Akar '.env'
$FileLewati  = Join-Path (Join-Path (Join-Path $Akar 'storage') 'app') 'lewati-libreoffice.txt'
$FileMsi     = Join-Path $Runtime 'libreoffice.msi'
$FileLog     = Join-Path $Runtime 'libreoffice-pasang.log'

$KandidatPath = @()
foreach ($dasar in @($env:ProgramFiles, ${env:ProgramFiles(x86)})) {
    if ($dasar) { $KandidatPath += (Join-Path (Join-Path $dasar 'LibreOffice') 'program\soffice.exe') }
}

function Write-Info($teks)      { Write-Host "  [INFO]       $teks" }
function Write-Ok($teks)        { Write-Host "  [OK]         $teks" }
function Write-Peringatan($teks, $saran = '') {
    Write-Host "  [PERINGATAN] $teks"
    if ($saran -ne '') { Write-Host "               -> $saran" }
}

function Get-NilaiEnv {
    if (-not (Test-Path $FileEnv)) { return '' }
    foreach ($b in [System.IO.File]::ReadAllLines($FileEnv)) {
        if ($b -match '^LIBREOFFICE_BINARY=(.*)$') {
            return $Matches[1].Trim().Trim('"').Trim("'")
        }
    }
    return ''
}

# Mengisi LIBREOFFICE_BINARY dengan kutip TUNGGAL (kutip ganda + backslash merusak .env)
function Set-NilaiEnv($path) {
    $baris = New-Object System.Collections.Generic.List[string]
    $ada = $false
    foreach ($b in [System.IO.File]::ReadAllLines($FileEnv)) {
        if ($b -match '^LIBREOFFICE_BINARY=') {
            if (-not $ada) { $baris.Add("LIBREOFFICE_BINARY='" + $path + "'") }
            $ada = $true
        } else {
            $baris.Add($b)
        }
    }
    if (-not $ada) { $baris.Add("LIBREOFFICE_BINARY='" + $path + "'") }
    [System.IO.File]::WriteAllLines($FileEnv, $baris, (New-Object System.Text.UTF8Encoding($false)))
}

function Find-LibreOffice {
    foreach ($k in $KandidatPath) {
        if ($k -and (Test-Path $k)) { return $k }
    }
    foreach ($kunci in @('HKLM:\SOFTWARE\LibreOffice\UNO\InstallPath', 'HKLM:\SOFTWARE\WOW6432Node\LibreOffice\UNO\InstallPath')) {
        try {
            $nilai = (Get-ItemProperty -Path $kunci -ErrorAction Stop).'(default)'
            if ($nilai) {
                $exe = Join-Path $nilai 'soffice.exe'
                if (Test-Path $exe) { return $exe }
            }
        } catch { }
    }
    return $null
}

# Daftar versi dari halaman indeks stable/, dari yang terbaru
function Select-VersiLibreOffice($html) {
    $versi = @([regex]::Matches([string]$html, 'href="(\d+\.\d+\.\d+)/"') | ForEach-Object { $_.Groups[1].Value } | Select-Object -Unique)
    return @($versi | Sort-Object { [version]$_ } -Descending)
}

function Get-Berkas($url, $tujuan) {
    Invoke-WebRequest -UseBasicParsing -Uri $url -OutFile $tujuan
}

function Get-Teks($url) {
    return (Invoke-WebRequest -UseBasicParsing -Uri $url).Content
}

# Mengunduh MSI (versi terbaru yang tersedia) ke $FileMsi dan memeriksa SHA256
function Get-InstallerLibreOffice {
    $null = New-Item -ItemType Directory -Force -Path $Runtime
    if ((Test-Path $FileMsi) -and ((Get-Item $FileMsi).Length -gt 100MB)) {
        Write-Info 'Installer LibreOffice dari percobaan sebelumnya dipakai ulang.'
        return
    }
    Remove-Item $FileMsi -Force -ErrorAction SilentlyContinue

    Write-Info 'Mencari versi LibreOffice terbaru ...'
    $daftar = Select-VersiLibreOffice (Get-Teks $UrlStable)
    if ($daftar.Count -eq 0) { throw 'Daftar versi LibreOffice tidak ditemukan.' }

    $coba = 0
    foreach ($v in $daftar) {
        if ($coba -ge $MaksCoba) { break }
        $coba++
        $nama = "LibreOffice_${v}_Win_x86-64.msi"
        $url = "${UrlStable}${v}/win/x86_64/${nama}"
        try {
            Write-Info "Mengunduh LibreOffice $v (sekitar 350 MB), mohon tunggu ..."
            Get-Berkas $url $FileMsi
        } catch {
            Write-Info "Versi $v belum tersedia untuk Windows 64-bit, mencoba versi sebelumnya ..."
            Remove-Item $FileMsi -Force -ErrorAction SilentlyContinue
            continue
        }

        $hashResmi = $null
        try { $hashResmi = ((Get-Teks ($url + '.sha256')) -split '\s+')[0] } catch { }
        if ($hashResmi) {
            $hash = (Get-FileHash -Algorithm SHA256 -Path $FileMsi).Hash
            if ($hash -ne $hashResmi) {
                Remove-Item $FileMsi -Force -ErrorAction SilentlyContinue
                throw 'Berkas LibreOffice yang diunduh rusak (sidik jari SHA256 tidak cocok).'
            }
            Write-Ok 'Sidik jari SHA256 installer cocok'
        } else {
            Write-Info 'Sidik jari resmi tidak tersedia, pemeriksaan SHA256 dilewati.'
        }
        return
    }
    throw 'Installer LibreOffice Windows 64-bit tidak berhasil diunduh.'
}

function Install-LibreOffice {
    Get-InstallerLibreOffice

    Write-Info 'Memasang LibreOffice. Saat jendela izin Windows (UAC) muncul, pilih Ya.'
    $argumen = '/i "' + $FileMsi + '" /qn /norestart /l*v "' + $FileLog + '" ' +
        'REGISTER_NO_MSO_TYPES=1 CREATEDESKTOPLINK=0 ISCHECKFORPRODUCTUPDATES=0 QUICKSTART=0'
    $proses = Start-Process -FilePath 'msiexec.exe' -ArgumentList $argumen -Verb RunAs -Wait -PassThru
    if (@(0, 3010) -notcontains $proses.ExitCode) {
        throw ('Pemasangan LibreOffice gagal (kode {0}). Catatan: runtime\libreoffice-pasang.log' -f $proses.ExitCode)
    }
    Remove-Item $FileMsi -Force -ErrorAction SilentlyContinue
}

function Invoke-Main {
    try {
        if (Test-Path $FileLewati) { return 0 }
        if (-not (Test-Path $FileEnv)) { return 0 }

        $sekarang = Get-NilaiEnv
        if ($sekarang -and (Test-Path $sekarang)) { return 0 }

        $ketemu = Find-LibreOffice
        if ($ketemu) {
            Set-NilaiEnv $ketemu
            Write-Ok "LibreOffice ditemukan di $ketemu (.env diperbarui)"
            return 0
        }

        Write-Host ''
        Write-Host '  Menyiapkan LibreOffice (untuk membuat PDF)'
        Write-Host '  ------------------------------------------'
        if (-not [Environment]::Is64BitOperatingSystem) {
            Write-Peringatan 'Windows 32-bit: LibreOffice tidak dipasang otomatis.' 'Pasang LibreOffice 32-bit secara manual dari libreoffice.org lalu isi LIBREOFFICE_BINARY di .env.'
            return 0
        }
        [Net.ServicePointManager]::SecurityProtocol = [Net.ServicePointManager]::SecurityProtocol -bor [Net.SecurityProtocolType]::Tls12
        Write-Info 'LibreOffice belum terpasang. Dibutuhkan untuk membuat PDF; file Word tetap bisa dibuat tanpanya.'

        Install-LibreOffice

        $ketemu = Find-LibreOffice
        if (-not $ketemu) { throw 'LibreOffice selesai dipasang tetapi soffice.exe tidak ditemukan.' }
        Set-NilaiEnv $ketemu
        Write-Ok "LibreOffice terpasang di $ketemu"
        return 0
    } catch {
        $pesan = $_.Exception.Message
        $batal = ($pesan -match 'canceled|dibatalkan')
        foreach ($ex in @($_.Exception, $_.Exception.InnerException)) {
            if ($ex -and $ex.PSObject.Properties['NativeErrorCode'] -and $ex.NativeErrorCode -eq 1223) { $batal = $true }
        }
        if ($batal) {
            Write-Peringatan 'Pemasangan LibreOffice dibatalkan (izin Administrator tidak diberikan).' 'Jalankan start.bat lagi dan pilih Ya pada jendela izin Windows, atau minta bantuan pengelola komputer.'
        } else {
            Write-Peringatan ('LibreOffice belum bisa dipasang otomatis: ' + $pesan) 'PDF belum bisa dibuat (file Word tetap jadi). Pasang LibreOffice manual dari libreoffice.org, atau jalankan start.bat lagi.'
        }
        return 0
    }
}

# Dijalankan hanya bila skrip dipanggil langsung (bukan di-dot-source untuk uji)
if ($MyInvocation.InvocationName -ne '.') {
    exit (Invoke-Main)
}
