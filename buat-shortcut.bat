@echo off
setlocal
cd /d "%~dp0"

powershell -NoProfile -Command "$s = New-Object -ComObject WScript.Shell; $d = [Environment]::GetFolderPath('Desktop'); $a = $s.CreateShortcut($d + '\SK Generator.lnk'); $a.TargetPath = '%~dp0start.bat'; $a.WorkingDirectory = '%~dp0'; $a.Save(); $b = $s.CreateShortcut($d + '\Berhenti SK Generator.lnk'); $b.TargetPath = '%~dp0stop.bat'; $b.WorkingDirectory = '%~dp0'; $b.Save()"

echo.
echo   Shortcut "SK Generator" dan "Berhenti SK Generator" sudah dibuat di Desktop.
echo.
pause
