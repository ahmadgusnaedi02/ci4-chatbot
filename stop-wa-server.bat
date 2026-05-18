@echo off
setlocal

set "WA_PORT=3001"

echo Mencari WhatsApp server di port %WA_PORT%...
powershell -NoProfile -ExecutionPolicy Bypass -Command "$connections = Get-NetTCPConnection -LocalPort %WA_PORT% -State Listen -ErrorAction SilentlyContinue; if (-not $connections) { Write-Host 'Tidak ada proses yang listen di port %WA_PORT%.'; exit 0 }; $ids = $connections | Select-Object -ExpandProperty OwningProcess -Unique; foreach ($processId in $ids) { $process = Get-Process -Id $processId -ErrorAction SilentlyContinue; if ($process -and $process.ProcessName -like 'node*') { Stop-Process -Id $processId -Force; Write-Host ('WhatsApp server dihentikan. PID: ' + $processId) } else { Write-Host ('Port %WA_PORT% dipakai proses non-node, dilewati. PID: ' + $processId) } }"

pause
