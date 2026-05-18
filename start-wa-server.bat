@echo off
setlocal

set "SCRIPT_DIR=%~dp0"
set "WA_SCRIPT=%SCRIPT_DIR%whatsapp-server\start-whatsapp-server.bat"

if not exist "%WA_SCRIPT%" (
    echo File launcher WhatsApp server tidak ditemukan:
    echo %WA_SCRIPT%
    pause
    exit /b 1
)

echo Menjalankan WhatsApp server di window baru...
start "WhatsApp Server" /min "%ComSpec%" /k ""%WA_SCRIPT%""

exit /b 0
