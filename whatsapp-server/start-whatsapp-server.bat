@echo off
setlocal

set "NODE_HOME=C:\laragon\bin\nodejs\node-v18"
set "PATH=%NODE_HOME%;%PATH%"

cd /d "%~dp0"

if not exist "%NODE_HOME%\node.exe" (
    echo Node.js Laragon tidak ditemukan di %NODE_HOME%
    pause
    exit /b 1
)

if not exist "node_modules" (
    echo Menginstall dependency WhatsApp server...
    call "%NODE_HOME%\npm.cmd" install
    if errorlevel 1 (
        echo Install dependency gagal.
        pause
        exit /b 1
    )
)

echo Menjalankan WhatsApp server...
call "%NODE_HOME%\npm.cmd" run start

pause
