@echo off
setlocal
title SK Generator - Berhenti
cd /d "%~dp0"

if not exist storage\app\server.pid goto tidakada

set /p PID=<storage\app\server.pid
taskkill /PID %PID% /T /F >nul 2>nul
set HASIL=%errorlevel%
del storage\app\server.pid >nul 2>nul

echo.
if "%HASIL%"=="0" goto berhasil
echo   SK Generator sudah tidak berjalan.
goto selesai

:berhasil
echo   SK Generator dihentikan.
goto selesai

:tidakada
echo.
echo   Tidak ada SK Generator yang tercatat berjalan dari start.bat.

:selesai
echo.
timeout /t 4 >nul
exit /b 0
