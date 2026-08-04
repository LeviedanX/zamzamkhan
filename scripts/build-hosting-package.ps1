# =============================================================================
# Membangun folder paket hosting siap unggah ke cPanel IDwebhost.
# Hasil: <root>\zamzamkhan  ->  /public_html/zamzamkhan
#
#   powershell -ExecutionPolicy Bypass -File scripts\build-hosting-package.ps1
#
# Prasyarat: npm run build sudah dijalankan (public/build/manifest.json ada).
# =============================================================================
param(
    [string]$PackageName = "zamzamkhan",
    [string]$Domain = "zamzamkhan.com",
    [switch]$SkipDatabaseDump
)

$ErrorActionPreference = "Stop"
$root = (Resolve-Path (Join-Path $PSScriptRoot "..")).Path
$pkg = Join-Path $root $PackageName

function Assert-InsideWorkspace([string]$Path) {
    $full = [System.IO.Path]::GetFullPath($Path)
    if (-not $full.StartsWith($root + [System.IO.Path]::DirectorySeparatorChar, [System.StringComparison]::OrdinalIgnoreCase)) {
        throw "Path paket keluar dari workspace: $full"
    }
}

function Invoke-Mirror([string]$Source, [string]$Destination, [string[]]$ExcludeDirs = @(), [string[]]$ExcludeFiles = @()) {
    $arguments = @($Source, $Destination, "/E", "/XJ", "/NFL", "/NDL", "/NJH", "/NJS", "/NP", "/R:1", "/W:1")
    if ($ExcludeDirs.Count -gt 0) { $arguments += "/XD"; $arguments += $ExcludeDirs }
    if ($ExcludeFiles.Count -gt 0) { $arguments += "/XF"; $arguments += $ExcludeFiles }
    & robocopy.exe @arguments | Out-Null
    if ($LASTEXITCODE -ge 8) { throw "Robocopy gagal ($Source -> $Destination), exit $LASTEXITCODE" }
    $global:LASTEXITCODE = 0
}

function Read-EnvValue([string]$File, [string]$Key) {
    if (-not (Test-Path $File)) { return "" }
    foreach ($line in Get-Content -LiteralPath $File) {
        if ($line -match "^\s*$([regex]::Escape($Key))\s*=\s*(.*)$") {
            return $Matches[1].Trim().Trim('"').Trim("'")
        }
    }
    return ""
}

Assert-InsideWorkspace $pkg

# --- Guard prarilis ----------------------------------------------------------
Write-Host "==> Verifikasi prasyarat" -ForegroundColor Cyan
if (-not (Test-Path (Join-Path $root "public/build/manifest.json"))) {
    throw "public/build/manifest.json tidak ada. Jalankan: npm run build"
}
if (Test-Path (Join-Path $root "public/hot")) {
    throw "public/hot masih ada. Hentikan Vite dev server lalu hapus berkas tersebut."
}
foreach ($template in @(".env.production.example", "deploy/htaccess-root.template", "deploy/CARA-UPLOAD.md", "deploy/deploy-setup.sh")) {
    if (-not (Test-Path (Join-Path $root $template))) { throw "Template hilang: $template" }
}

# --- Bersihkan hasil lama ----------------------------------------------------
if (Test-Path $pkg) { Remove-Item -LiteralPath $pkg -Recurse -Force }
New-Item -ItemType Directory -Path $pkg -Force | Out-Null

# --- Salin source aplikasi ---------------------------------------------------
Write-Host "==> Menyalin source aplikasi" -ForegroundColor Cyan
foreach ($directory in @("app", "config", "database", "resources", "routes")) {
    Invoke-Mirror (Join-Path $root $directory) (Join-Path $pkg $directory)
}

# bootstrap: cache wajib kosong agar tidak membawa cache environment lokal.
Invoke-Mirror (Join-Path $root "bootstrap") (Join-Path $pkg "bootstrap") -ExcludeDirs @((Join-Path $root "bootstrap\cache"))
New-Item -ItemType Directory -Path (Join-Path $pkg "bootstrap/cache") -Force | Out-Null
Copy-Item -LiteralPath (Join-Path $root "bootstrap/cache/.gitignore") -Destination (Join-Path $pkg "bootstrap/cache/.gitignore") -Force

# public: tanpa symlink storage lokal, marker Vite dev, dan manifest font dev.
Invoke-Mirror (Join-Path $root "public") (Join-Path $pkg "public") `
    -ExcludeDirs @((Join-Path $root "public\storage")) `
    -ExcludeFiles @("hot", "fonts-manifest.dev.json")

# --- Kerangka storage --------------------------------------------------------
Write-Host "==> Menyiapkan kerangka storage" -ForegroundColor Cyan
$storageDirs = @(
    "storage/app", "storage/app/private", "storage/app/private/reports", "storage/app/public",
    "storage/framework", "storage/framework/cache", "storage/framework/cache/data",
    "storage/framework/sessions", "storage/framework/testing", "storage/framework/views",
    "storage/logs"
)
foreach ($relative in $storageDirs) {
    New-Item -ItemType Directory -Path (Join-Path $pkg $relative) -Force | Out-Null
    $sourceIgnore = Join-Path $root "$relative/.gitignore"
    if (Test-Path $sourceIgnore) {
        Copy-Item -LiteralPath $sourceIgnore -Destination (Join-Path $pkg "$relative/.gitignore") -Force
    }
}

# Unggahan CMS yang sudah ada ikut dibawa agar tampilan tidak kosong.
Invoke-Mirror (Join-Path $root "storage/app/public") (Join-Path $pkg "storage/app/public")

# --- Berkas root -------------------------------------------------------------
Write-Host "==> Menyalin berkas root" -ForegroundColor Cyan
foreach ($file in @("artisan", "composer.json", "composer.lock", ".env.production.example", ".editorconfig")) {
    Copy-Item -LiteralPath (Join-Path $root $file) -Destination (Join-Path $pkg $file) -Force
}
New-Item -ItemType Directory -Path (Join-Path $pkg "docs") -Force | Out-Null
Copy-Item -LiteralPath (Join-Path $root "docs/DEPLOYMENT.md") -Destination (Join-Path $pkg "docs/DEPLOYMENT.md") -Force

Copy-Item -LiteralPath (Join-Path $root "deploy/htaccess-root.template") -Destination (Join-Path $pkg ".htaccess") -Force
Copy-Item -LiteralPath (Join-Path $root "deploy/CARA-UPLOAD.md") -Destination (Join-Path $pkg "CARA-UPLOAD.md") -Force
Copy-Item -LiteralPath (Join-Path $root "deploy/deploy-setup.sh") -Destination (Join-Path $pkg "deploy-setup.sh") -Force

# deploy-setup.sh dijalankan di Linux: paksa akhir baris LF.
$setupPath = Join-Path $pkg "deploy-setup.sh"
[System.IO.File]::WriteAllText($setupPath, ((Get-Content -LiteralPath $setupPath -Raw) -replace "`r`n", "`n"), (New-Object System.Text.UTF8Encoding $false))

# --- .env production ---------------------------------------------------------
Write-Host "==> Menulis .env production untuk $Domain" -ForegroundColor Cyan
$appKey = (& php -r "echo 'base64:'.base64_encode(random_bytes(32));")
if ([string]::IsNullOrWhiteSpace($appKey)) { throw "Gagal membuat APP_KEY." }

$envLines = Get-Content -LiteralPath (Join-Path $root ".env.production.example")
$envLines = $envLines | ForEach-Object {
    if ($_ -match "^APP_KEY=") { "APP_KEY=$appKey" } else { $_ }
}
$header = @(
    "# ============================================================================",
    "# .env PRODUCTION - PT Zam Zam Khan ($Domain)",
    "# Lokasi di cPanel: /public_html/$PackageName/.env   (permission 600)",
    "#",
    "# WAJIB DIISI SEBELUM SITUS DIJALANKAN: DB_DATABASE, DB_USERNAME, DB_PASSWORD.",
    "# Nilai lain sudah benar untuk $Domain - jangan diubah tanpa alasan.",
    "# JANGAN mengganti APP_KEY setelah sistem berjalan.",
    "# ============================================================================",
    ""
)
[System.IO.File]::WriteAllLines((Join-Path $pkg ".env"), ($header + $envLines), (New-Object System.Text.UTF8Encoding $false))

# --- Dump database -----------------------------------------------------------
if (-not $SkipDatabaseDump) {
    Write-Host "==> Mengekspor dump database" -ForegroundColor Cyan
    $mysqldump = Get-ChildItem -Path "C:\Program Files\MySQL" -Filter "mysqldump.exe" -Recurse -ErrorAction SilentlyContinue |
        Select-Object -First 1 -ExpandProperty FullName
    $localEnv = Join-Path $root ".env"
    if ($mysqldump -and (Test-Path $localEnv)) {
        New-Item -ItemType Directory -Path (Join-Path $pkg "database/dump") -Force | Out-Null
        $dumpTarget = Join-Path $pkg "database/dump/zzk_web.sql"
        $env:MYSQL_PWD = Read-EnvValue $localEnv "DB_PASSWORD"
        & $mysqldump `
            "--host=$(Read-EnvValue $localEnv 'DB_HOST')" `
            "--port=$(Read-EnvValue $localEnv 'DB_PORT')" `
            "--user=$(Read-EnvValue $localEnv 'DB_USERNAME')" `
            "--default-character-set=utf8mb4" `
            "--single-transaction" "--no-tablespaces" "--skip-lock-tables" `
            "--add-drop-table" "--complete-insert" "--set-gtid-purged=OFF" `
            (Read-EnvValue $localEnv "DB_DATABASE") |
            Out-File -LiteralPath $dumpTarget -Encoding utf8
        Remove-Item Env:MYSQL_PWD -ErrorAction SilentlyContinue

        # Out-File pada PowerShell 5.1 menulis BOM. MySQL CLI menolak BOM pada
        # baris pertama, jadi berkas ditulis ulang tanpa BOM.
        [System.IO.File]::WriteAllText(
            $dumpTarget,
            (Get-Content -LiteralPath $dumpTarget -Raw),
            (New-Object System.Text.UTF8Encoding $false)
        )
        if (-not (Test-Path $dumpTarget) -or (Get-Item $dumpTarget).Length -lt 1024) {
            throw "Dump database gagal atau kosong."
        }
        Write-Host "    dump: $([math]::Round((Get-Item $dumpTarget).Length / 1KB)) KB"
    } else {
        Write-Warning "mysqldump atau .env lokal tidak ditemukan. Dump database dilewati."
    }
}

# --- Dependency production ---------------------------------------------------
Write-Host "==> composer install --no-dev" -ForegroundColor Cyan
Push-Location $pkg
try {
    & composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-scripts
    if ($LASTEXITCODE -ne 0) { throw "composer install production gagal." }
    & composer dump-autoload --no-dev --optimize --no-interaction
    if ($LASTEXITCODE -ne 0) { throw "composer dump-autoload gagal." }
} finally {
    Pop-Location
}

# --- Gate berkas terlarang ---------------------------------------------------
Write-Host "==> Verifikasi paket" -ForegroundColor Cyan
$forbidden = @(
    "node_modules", "tests", ".git", "phpunit.xml", "package.json", "package-lock.json",
    "vite.config.js", "public/hot", "public/storage", "public/fonts-manifest.dev.json",
    ".env.example", "references", "output", "scripts", "laporan", "TODO.md"
)
foreach ($relative in $forbidden) {
    if (Test-Path (Join-Path $pkg $relative)) { throw "Paket memuat path terlarang: $relative" }
}

$required = @(
    ".env", ".htaccess", "artisan", "composer.json", "CARA-UPLOAD.md", "deploy-setup.sh",
    "app", "bootstrap/cache", "config", "database/migrations", "resources/views", "routes/web.php",
    "vendor/autoload.php", "public/index.php", "public/.htaccess", "public/build/manifest.json",
    "storage/framework/views", "storage/logs", "docs/DEPLOYMENT.md"
)
foreach ($relative in $required) {
    if (-not (Test-Path (Join-Path $pkg $relative))) { throw "Paket kehilangan path wajib: $relative" }
}

$devPackages = @("vendor/phpunit", "vendor/mockery", "vendor/fakerphp", "vendor/nunomaduro/collision")
foreach ($relative in $devPackages) {
    if (Test-Path (Join-Path $pkg $relative)) { throw "Dependency development ikut terbawa: $relative" }
}

$envContent = Get-Content -LiteralPath (Join-Path $pkg ".env") -Raw
foreach ($pair in @(@("APP_ENV=production", $true), @("APP_DEBUG=false", $true), @("APP_URL=https://$Domain", $true), @("APP_DEBUG=true", $false))) {
    $present = $envContent -match [regex]::Escape($pair[0])
    if ($present -ne $pair[1]) { throw ".env production tidak konsisten pada: $($pair[0])" }
}

$fileCount = (Get-ChildItem -LiteralPath $pkg -Recurse -File -Force).Count
$sizeMb = [math]::Round(((Get-ChildItem -LiteralPath $pkg -Recurse -File -Force | Measure-Object Length -Sum).Sum / 1MB), 2)

Write-Host ""
Write-Host "PAKET SIAP UNGGAH" -ForegroundColor Green
Write-Host "  Folder   : $pkg"
Write-Host "  Tujuan   : /public_html/$PackageName"
Write-Host "  Domain   : https://$Domain"
Write-Host "  Berkas   : $fileCount"
Write-Host "  Ukuran   : $sizeMb MB"
Write-Host "  Panduan  : $PackageName\CARA-UPLOAD.md"
