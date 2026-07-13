@echo off
REM Launcher server lokal PT Zam Zam Khan.
REM Mengaktifkan driver SQLite (pdo_sqlite) via PHP_INI_SCAN_DIR lalu menjalankan server.
setlocal
cd /d "%~dp0"
set "PHP_INI_SCAN_DIR=%~dp0php-ini"
echo [serve.bat] SQLite driver diaktifkan dari: %PHP_INI_SCAN_DIR%
php artisan serve %*
