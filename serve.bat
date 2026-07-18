@echo off
REM Launcher server lokal PT Zam Zam Khan.
setlocal
cd /d "%~dp0"
set "ZZK_PORT=%~1"
if not defined ZZK_PORT set "ZZK_PORT=8000"
php -S 127.0.0.1:%ZZK_PORT% -t public scripts/dev-server.php
