param(
    [string]$HostAddress = '127.0.0.1',
    [ValidateRange(1, 65535)]
    [int]$Port = 8000
)

# Router PHP langsung menghindari overhead polling artisan serve pada Windows.
Set-Location $PSScriptRoot
php -S "${HostAddress}:${Port}" -t public scripts/dev-server.php
