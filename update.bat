@echo off
setlocal
title SK Generator - Perbarui
cd /d "%~dp0"

echo.
echo ================================================
echo    SK GENERATOR - PERBARUI
echo ================================================

if not exist launcher\perbarui.ps1 goto tidakada

rem Skrip disalin ke folder sementara supaya aman ikut diperbarui saat berjalan.
rem Baris di bawah sengaja satu baris: setelah selesai, cmd langsung keluar tanpa
rem membaca berkas ini lagi (berkas ini sendiri ikut diperbarui).
set "TMPPS=%TEMP%\sk-perbarui-%RANDOM%.ps1"
copy /y "launcher\perbarui.ps1" "%TMPPS%" >nul
powershell -NoProfile -ExecutionPolicy Bypass -File "%TMPPS%" -Akar "%~dp0." %* & del "%TMPPS%" >nul 2>nul & echo. & pause & exit /b

:tidakada
echo.
echo   [GAGAL] launcher\perbarui.ps1 tidak ada. Salin ulang folder SK Generator secara lengkap.
echo.
pause
exit /b 1
