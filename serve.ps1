# Launcher server lokal PT Zam Zam Khan (PowerShell).
# Mengaktifkan driver SQLite (pdo_sqlite) lalu menjalankan server.
Set-Location $PSScriptRoot
$env:PHP_INI_SCAN_DIR = Join-Path $PSScriptRoot 'php-ini'
Write-Host "[serve.ps1] SQLite driver diaktifkan dari: $env:PHP_INI_SCAN_DIR"
php artisan serve @args
