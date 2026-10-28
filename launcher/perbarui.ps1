# ===========================================================================
# perbarui.ps1 - memasang / memperbarui SK Generator dari GitHub (Tahap 17D)
#
# Dipakai oleh update.bat (memperbarui instalasi yang sudah ada) dan oleh
# pasang.bat (instalasi baru di PC lain).
#
#   -Versi   nama rilis (mis. v1.0.1) atau "main"; kosong = rilis terbaru
#   -Akar    folder aplikasi (pasang baru: bawaan C:\SK-Generator)
#   -Pasang  mode instalasi baru
#   -Paksa   perbarui walau versinya sama
#   -TanpaJalan  jangan menjalankan start.bat di akhir
#
# Pembaruan TIDAK menyentuh: .env, database\*.sqlite, storage\, runtime\,
# vendor\, bootstrap\cache\, public\storage. Database dicadangkan dulu.
# Migrasi database dan pemasangan komponen baru dikerjakan start.bat.
#
# Kode keluar: 0 = berhasil / tidak ada yang perlu dikerjakan, 1 = gagal.
# Semua teks sengaja ASCII supaya tampil benar di jendela perintah Windows.
# ===========================================================================

param(
    [Parameter(Position = 0)][string]$Versi = '',
    [string]$Akar = '',
    [switch]$Pasang,
    [switch]$Paksa,
    [switch]$TanpaJalan
)

$ErrorActionPreference = 'Stop'
$ProgressPreference = 'SilentlyContinue'

$Pemilik         = 'Richie-Syahputra'
$Repo            = 'SK-Generator'
$UrlRilisTerbaru = "https://github.com/$Pemilik/$Repo/releases/latest"
$EsWindows       = ($env:OS -eq 'Windows_NT')
$AkarBawaan      = 'C:\SK-Generator'
$MaksCadangan    = 5

$AkarDitentukan = ($Akar -ne '')
if (-not $AkarDitentukan) {
    if ($Pasang) { $Akar = $AkarBawaan } else { $Akar = Split-Path -Parent $PSScriptRoot }
}
$Akar = [System.IO.Path]::GetFullPath($Akar)
if ($Akar.Length -gt 3) { $Akar = $Akar.TrimEnd('\', '/') }

function Write-Info($teks)      { Write-Host "  [INFO]       $teks" }
function Write-Ok($teks)        { Write-Host "  [OK]         $teks" }
function Write-Peringatan($teks) { Write-Host "  [PERINGATAN] $teks" }
function Write-Gagal($teks, $saran = '') {
    Write-Host "  [GAGAL]      $teks"
    if ($saran -ne '') { Write-Host "               -> $saran" }
}

function Get-FolderKerja {
    if ($Pasang) { return (Join-Path ([System.IO.Path]::GetTempPath()) 'sk-generator-pasang') }
    return (Join-Path (Join-Path $Akar 'runtime') 'pembaruan')
}

function Get-FileVersi { return (Join-Path (Join-Path $Akar 'runtime') 'versi-terpasang.txt') }

function Get-VersiTerpasang {
    $f = Get-FileVersi
    if (Test-Path $f) { return ([System.IO.File]::ReadAllText($f)).Trim() }
    return ''
}

function Set-VersiTerpasang($versi) {
    $f = Get-FileVersi
    $null = New-Item -ItemType Directory -Force -Path (Split-Path -Parent $f)
    [System.IO.File]::WriteAllText($f, $versi + "`r`n", (New-Object System.Text.UTF8Encoding($false)))
}

# Nama rilis terbaru dari GitHub ($null bila belum ada rilis)
function Get-VersiTerbaru {
    try {
        $r = Invoke-WebRequest -UseBasicParsing -Uri $UrlRilisTerbaru
        $uri = $null
        if ($r.BaseResponse.PSObject.Properties['ResponseUri']) {
            $uri = $r.BaseResponse.ResponseUri.AbsoluteUri
        } elseif ($r.BaseResponse.PSObject.Properties['RequestMessage']) {
            $uri = $r.BaseResponse.RequestMessage.RequestUri.AbsoluteUri
        }
        if ($uri -match '/releases/tag/([^/?#]+)') {
            return [System.Uri]::UnescapeDataString($Matches[1])
        }
    } catch { }
    return $null
}

function Resolve-Versi {
    if ($Versi -ne '') { return $Versi }
    Write-Info 'Mencari versi rilis terbaru di GitHub ...'
    $v = Get-VersiTerbaru
    if ($null -eq $v) {
        Write-Gagal 'Belum ada versi rilis di GitHub (atau GitHub tidak bisa dihubungi).' 'Periksa koneksi internet. Pengelola dapat membuat rilis di GitHub; untuk memakai cabang pengembangan jalankan perintah ini dengan tambahan: main'
        return $null
    }
    return $v
}

function Get-UrlZip($versi) {
    if ($versi -eq 'main') { return "https://github.com/$Pemilik/$Repo/archive/refs/heads/main.zip" }
    return "https://github.com/$Pemilik/$Repo/archive/refs/tags/$versi.zip"
}

function Get-Berkas($url, $tujuan) {
    Invoke-WebRequest -UseBasicParsing -Uri $url -OutFile $tujuan
}

# Mengunduh dan mengekstrak versi ke folder kerja; mengembalikan folder sumber
function Get-Sumber($versi) {
    $kerja = Get-FolderKerja
    Remove-Item -Recurse -Force $kerja -ErrorAction SilentlyContinue
    $null = New-Item -ItemType Directory -Force -Path $kerja
    $zip = Join-Path $kerja 'sk.zip'
    $ekstrak = Join-Path $kerja 'ekstrak'

    Write-Info "Mengunduh SK Generator $versi dari GitHub ..."
    Get-Berkas (Get-UrlZip $versi) $zip
    Add-Type -AssemblyName System.IO.Compression.FileSystem
    [System.IO.Compression.ZipFile]::ExtractToDirectory($zip, $ekstrak)

    $folder = @(Get-ChildItem -LiteralPath $ekstrak -Directory)
    $berkas = @(Get-ChildItem -LiteralPath $ekstrak -File)
    $akarSumber = $ekstrak
    if ($folder.Count -eq 1 -and $berkas.Count -eq 0) { $akarSumber = $folder[0].FullName }

    foreach ($wajib in @('artisan', 'composer.json', 'composer.lock', 'start.bat')) {
        if (-not (Test-Path (Join-Path $akarSumber $wajib))) {
            throw "Berkas unduhan tidak lengkap ($wajib tidak ada). Tidak ada yang diubah."
        }
    }
    return $akarSumber
}

function Remove-Kerja {
    Remove-Item -Recurse -Force (Get-FolderKerja) -ErrorAction SilentlyContinue
}

# Berkas/folder yang TIDAK disentuh saat memperbarui (relatif dari akar, pemisah "/")
function Test-Dikecualikan($rel, $modePasang) {
    $awalan = @('.git')
    $nama = @('.env', '*.sqlite', '*.sqlite-journal', '*.sqlite-wal', '*.sqlite-shm')
    if (-not $modePasang) {
        $awalan += @('vendor', 'runtime', 'storage', 'bootstrap/cache', 'public/storage')
    }
    foreach ($a in $awalan) {
        if ($rel -eq $a -or $rel.StartsWith($a + '/')) { return $true }
    }
    $leaf = $rel.Substring($rel.LastIndexOf('/') + 1)
    foreach ($p in $nama) {
        if ($leaf -like $p) { return $true }
    }
    return $false
}

# Menyalin berkas dari sumber ke tujuan; mengembalikan jumlah berkas yang disalin
function Copy-Pembaruan($sumber, $tujuan, $modePasang) {
    $dasar = [System.IO.Path]::GetFullPath($sumber).TrimEnd('\', '/')
    $jumlah = 0
    $galat = New-Object System.Collections.Generic.List[string]
    foreach ($f in (Get-ChildItem -LiteralPath $dasar -Recurse -File -Force)) {
        $rel = ($f.FullName.Substring($dasar.Length).TrimStart('\', '/')) -replace '\\', '/'
        if (Test-Dikecualikan $rel $modePasang) { continue }
        $dest = Join-Path $tujuan ($rel -replace '/', [System.IO.Path]::DirectorySeparatorChar)
        try {
            $null = New-Item -ItemType Directory -Force -Path (Split-Path -Parent $dest)
            Copy-Item -LiteralPath $f.FullName -Destination $dest -Force
            $jumlah++
        } catch {
            $galat.Add($rel + ': ' + $_.Exception.Message)
        }
    }
    if ($galat.Count -gt 0) {
        throw ('Sebagian berkas tidak bisa ditimpa (' + $galat.Count + '). Contoh: ' + $galat[0] + ' Tutup aplikasi lalu jalankan pembaruan lagi.')
    }
    return $jumlah
}

function Stop-Server {
    $pid_ = Join-Path (Join-Path (Join-Path $Akar 'storage') 'app') 'server.pid'
    if (-not (Test-Path $pid_)) { return }
    $id = ([System.IO.File]::ReadAllText($pid_)).Trim()
    if ($id -match '^\d+$') {
        if ($EsWindows) { $null = & taskkill /PID $id /T /F 2>$null }
        Write-Info 'Aplikasi yang sedang berjalan dihentikan sementara untuk pembaruan.'
    }
    Remove-Item $pid_ -Force -ErrorAction SilentlyContinue
}

function Get-JalurDatabase {
    $jalur = Join-Path (Join-Path $Akar 'database') 'database.sqlite'
    $env_ = Join-Path $Akar '.env'
    if (Test-Path $env_) {
        foreach ($b in [System.IO.File]::ReadAllLines($env_)) {
            if ($b -match '^DB_DATABASE=(.*)$') {
                $nilai = $Matches[1].Trim().Trim('"').Trim("'")
                if ($nilai -ne '') {
                    if (Test-Path $nilai) { return $nilai }
                    $rel = Join-Path $Akar $nilai
                    if (Test-Path $rel) { return $rel }
                }
            }
        }
    }
    return $jalur
}

function Backup-Data {
    $db = Get-JalurDatabase
    if (-not (Test-Path $db) -or (Get-Item $db).Length -eq 0) { return }
    $folder = Join-Path $Akar 'database'
    $null = New-Item -ItemType Directory -Force -Path $folder
    $nama = 'backup-update-' + (Get-Date -Format 'yyyyMMdd-HHmmss') + '.sqlite'
    Copy-Item -LiteralPath $db -Destination (Join-Path $folder $nama) -Force
    Write-Ok "Cadangan database dibuat: $nama"
    Get-ChildItem -LiteralPath $folder -Filter 'backup-update-*.sqlite' |
        Sort-Object LastWriteTime -Descending |
        Select-Object -Skip $MaksCadangan |
        Remove-Item -Force -ErrorAction SilentlyContinue
}

function Get-HashLock {
    $f = Join-Path $Akar 'composer.lock'
    if (Test-Path $f) { return (Get-FileHash -Algorithm SHA256 -Path $f).Hash }
    return ''
}

function Start-Aplikasi {
    if (-not $EsWindows) { return }
    Write-Info 'Menjalankan SK Generator ...'
    Start-Process -FilePath (Join-Path $Akar 'start.bat') -WorkingDirectory $Akar
}

function New-Pintasan {
    if (-not $EsWindows) { return }
    try {
        $s = New-Object -ComObject WScript.Shell
        $d = [Environment]::GetFolderPath('Desktop')
        foreach ($p in @(@('SK Generator', 'start.bat'), @('Berhenti SK Generator', 'stop.bat'), @('Perbarui SK Generator', 'update.bat'))) {
            $a = $s.CreateShortcut((Join-Path $d ($p[0] + '.lnk')))
            $a.TargetPath = Join-Path $Akar $p[1]
            $a.WorkingDirectory = $Akar
            $a.Save()
        }
        Write-Ok 'Pintasan di Desktop dibuat (SK Generator, Berhenti, Perbarui)'
    } catch {
        Write-Peringatan ('Pintasan Desktop tidak bisa dibuat: ' + $_.Exception.Message)
    }
}

function Invoke-Perbarui {
    if (-not ((Test-Path (Join-Path $Akar 'artisan')) -and (Test-Path (Join-Path $Akar 'start.bat')))) {
        Write-Gagal "Folder ini bukan instalasi SK Generator: $Akar" 'Jalankan update.bat dari dalam folder SK Generator.'
        return 1
    }
    $target = Resolve-Versi
    if ($null -eq $target) { return 1 }
    if ($target -eq 'main') { Write-Peringatan 'Memakai cabang "main" (versi pengembangan, belum tentu stabil).' }

    $terpasang = Get-VersiTerpasang
    if (-not $Paksa -and $target -ne 'main' -and $terpasang -eq $target) {
        Write-Ok "SK Generator sudah versi terbaru ($target). Tidak ada yang diubah."
        return 0
    }
    if ($terpasang -ne '') { Write-Info "Versi terpasang: $terpasang, versi tujuan: $target" }
    else { Write-Info "Versi tujuan: $target" }

    $sumber = Get-Sumber $target
    Stop-Server
    $lockLama = Get-HashLock
    Backup-Data
    $jumlah = Copy-Pembaruan $sumber $Akar $false
    Write-Ok "$jumlah berkas diperbarui (database, .env, dan folder penyimpanan tidak diubah)"

    if ($lockLama -ne (Get-HashLock)) {
        Remove-Item (Join-Path (Join-Path $Akar 'vendor') 'autoload.php') -Force -ErrorAction SilentlyContinue
        Write-Info 'Daftar komponen berubah: komponen akan diperbarui otomatis saat aplikasi dijalankan (butuh internet).'
    }
    Set-VersiTerpasang $target
    Remove-Kerja
    Write-Ok "Pembaruan ke $target selesai. Database diperbarui otomatis saat aplikasi dijalankan."
    if (-not $TanpaJalan) { Start-Aplikasi }
    return 0
}

function Invoke-Pasang {
    if (Test-Path (Join-Path $Akar 'artisan')) {
        Write-Ok "SK Generator sudah terpasang di $Akar."
        Write-Info 'Untuk memperbarui, jalankan update.bat di folder itu.'
        return 0
    }
    if ((Test-Path $Akar) -and (@(Get-ChildItem -LiteralPath $Akar -Force).Count -gt 0)) {
        Write-Gagal "Folder tujuan tidak kosong: $Akar" 'Pilih folder lain: pasang.bat "D:\SK-Generator"'
        return 1
    }
    $target = Resolve-Versi
    if ($null -eq $target) { return 1 }
    if ($target -eq 'main') { Write-Peringatan 'Memakai cabang "main" (versi pengembangan, belum tentu stabil).' }

    $sumber = Get-Sumber $target
    try {
        $null = New-Item -ItemType Directory -Force -Path $Akar
    } catch {
        if ($AkarDitentukan -or -not $env:USERPROFILE) { throw }
        $script:Akar = Join-Path $env:USERPROFILE 'SK-Generator'
        Write-Info "Folder $AkarBawaan tidak bisa dibuat, memakai $script:Akar"
        $null = New-Item -ItemType Directory -Force -Path $script:Akar
    }
    Write-Info "Memasang ke $Akar ..."
    $jumlah = Copy-Pembaruan $sumber $Akar $true
    Set-VersiTerpasang $target
    New-Pintasan
    Remove-Kerja
    Write-Ok "SK Generator $target terpasang di $Akar ($jumlah berkas)"
    if (-not $TanpaJalan) { Start-Aplikasi }
    return 0
}

function Invoke-Main {
    Write-Host ''
    if ($Pasang) { Write-Host '  Memasang SK Generator' } else { Write-Host '  Memperbarui SK Generator' }
    Write-Host '  ------------------------'
    try {
        if ($Versi -ne '' -and $Versi -ne 'main' -and $Versi -notmatch '^[A-Za-z0-9._-]+$') {
            throw 'Nama versi tidak valid.'
        }
        [Net.ServicePointManager]::SecurityProtocol = [Net.ServicePointManager]::SecurityProtocol -bor [Net.SecurityProtocolType]::Tls12
        if ($Pasang) { return (Invoke-Pasang) }
        return (Invoke-Perbarui)
    } catch {
        Write-Gagal ('Gagal: ' + $_.Exception.Message) 'Pastikan komputer terhubung ke internet dan aplikasi sedang tidak dipakai, lalu jalankan lagi. Data tidak dihapus.'
        return 1
    }
}

# Dijalankan hanya bila skrip dipanggil langsung (bukan di-dot-source untuk uji)
if ($MyInvocation.InvocationName -ne '.') {
    exit (Invoke-Main)
}
