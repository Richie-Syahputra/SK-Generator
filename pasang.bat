@echo off
setlocal
title SK Generator - Pemasangan

rem Pemakaian:
rem   pasang.bat                      pasang rilis terbaru ke C:\SK-Generator
rem   pasang.bat "D:\SK-Generator"    pasang ke folder lain
rem   pasang.bat main                 pakai cabang pengembangan (bukan rilis)
rem Berkas ini mandiri: boleh disalin sendirian ke PC lain. Bagian PowerShell
rem ada di bawah baris penanda; yang dilakukannya: mengambil skrip pemasang dari
rem GitHub sesuai versi rilis, lalu menjalankannya.

set "SK_TUJUAN=%~1"
set "SK_VERSI=%~2"
if /i "%SK_TUJUAN%"=="main" (
    set "SK_TUJUAN="
    set "SK_VERSI=main"
)

powershell -NoProfile -ExecutionPolicy Bypass -Command "$t = [IO.File]::ReadAllText('%~f0'); Invoke-Expression $t.Substring($t.IndexOf('#' + 'PS-MULAI'))"
set "HASIL=%errorlevel%"
echo.
pause
exit /b %HASIL%
#PS-MULAI
$ErrorActionPreference = 'Stop'
$ProgressPreference = 'SilentlyContinue'
$Pemilik = 'Richie-Syahputra'
$Repo = 'SK-Generator'
$versi = $env:SK_VERSI
$tujuan = $env:SK_TUJUAN
Write-Host ''
Write-Host '  Pemasangan SK Generator'
Write-Host '  -----------------------'
try {
    [Net.ServicePointManager]::SecurityProtocol = [Net.ServicePointManager]::SecurityProtocol -bor [Net.SecurityProtocolType]::Tls12
    if ($versi -and $versi -ne 'main' -and $versi -notmatch '^[A-Za-z0-9._-]+$') { throw 'Nama versi tidak valid.' }
    if (-not $versi) {
        Write-Host '  [INFO]       Mencari versi rilis terbaru di GitHub ...'
        $r = Invoke-WebRequest -UseBasicParsing -Uri "https://github.com/$Pemilik/$Repo/releases/latest"
        $uri = $null
        if ($r.BaseResponse.PSObject.Properties['ResponseUri']) { $uri = $r.BaseResponse.ResponseUri.AbsoluteUri }
        elseif ($r.BaseResponse.PSObject.Properties['RequestMessage']) { $uri = $r.BaseResponse.RequestMessage.RequestUri.AbsoluteUri }
        if ($uri -match '/releases/tag/([^/?#]+)') { $versi = [Uri]::UnescapeDataString($Matches[1]) }
        else { throw 'Belum ada versi rilis di GitHub. Pengelola perlu membuat rilis, atau jalankan: pasang.bat main' }
    }
    $mesin = Join-Path $env:TEMP 'sk-perbarui.ps1'
    Invoke-WebRequest -UseBasicParsing -Uri "https://raw.githubusercontent.com/$Pemilik/$Repo/$versi/launcher/perbarui.ps1" -OutFile $mesin
    $argumen = @('-NoProfile', '-ExecutionPolicy', 'Bypass', '-File', $mesin, '-Pasang', '-Versi', $versi)
    if ($tujuan) { $argumen += @('-Akar', $tujuan) }
    & powershell @argumen
    exit $LASTEXITCODE
} catch {
    Write-Host ('  [GAGAL]      ' + $_.Exception.Message)
    exit 1
}
