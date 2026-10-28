@echo off
setlocal
title SK Generator - Peluncur
cd /d "%~dp0"

echo.
echo ================================================
echo    SK GENERATOR
echo ================================================

rem --- PHP milik aplikasi (portabel). PHP lain di komputer ini tidak dipakai. ---
set "PHP=%~dp0runtime\php\php.exe"
set "PHPRC=%~dp0runtime\php"
set "PHP_INI_SCAN_DIR="

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0launcher\siapkan-php.ps1"
if errorlevel 1 goto gagalphp

"%PHP%" launcher\siapkan-aplikasi.php
if errorlevel 1 goto adamasalah

rem --- LibreOffice (untuk PDF): dipasang otomatis bila belum ada. Tidak fatal. ---
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0launcher\siapkan-libreoffice.ps1"

"%PHP%" launcher\cek-awal.php
if errorlevel 1 goto adamasalah

for /f "tokens=1,2" %%a in (storage\app\launcher.txt) do (
    set PORT=%%a
    set MODE=%%b
)

if /i "%MODE%"=="jalan" goto buka

echo.
echo   Menjalankan aplikasi di port %PORT% ...
powershell -NoProfile -Command "$p = Start-Process -FilePath '%PHP%' -ArgumentList 'artisan','serve','--host=127.0.0.1','--port=%PORT%' -WorkingDirectory '%CD%' -WindowStyle Hidden -PassThru; Set-Content -Path 'storage\app\server.pid' -Value $p.Id"
"%PHP%" launcher\cek-awal.php --tunggu %PORT% 40
if errorlevel 1 goto servertidakmerespons

:buka
echo.
echo   Membuka browser ...
start "" "http://127.0.0.1:%PORT%"
echo.
echo   SK Generator berjalan di http://127.0.0.1:%PORT%
echo   Untuk menghentikan, jalankan stop.bat atau shortcut Berhenti SK Generator.
echo   Jendela ini akan menutup sendiri...
timeout /t 6 >nul
exit /b 0

:gagalphp
echo.
echo   [GAGAL] PHP milik aplikasi belum siap (lihat pesan di atas).
echo           Pastikan komputer terhubung ke internet, lalu jalankan start.bat lagi.
echo.
pause
exit /b 1

:adamasalah
echo.
echo   Aplikasi TIDAK dijalankan karena ada masalah di atas.
echo   Perbaiki sesuai saran (tanda ->), lalu jalankan start.bat lagi.
echo.
pause
exit /b 1

:servertidakmerespons
echo.
echo   [GAGAL] Aplikasi tidak merespons dalam 40 detik.
echo           Lihat catatan kesalahan di: storage\logs\laravel.log
echo.
pause
exit /b 1
