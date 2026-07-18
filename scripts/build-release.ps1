param(
    [string]$OutputDirectory = ".dist"
)

$ErrorActionPreference = "Stop"
$root = (Resolve-Path (Join-Path $PSScriptRoot "..")).Path
$stageParent = Join-Path $root ".release"
$stage = Join-Path $stageParent "zzk-web"
$output = Join-Path $root $OutputDirectory
$archive = Join-Path $output "zzk-web-production.zip"
$checksum = "$archive.sha256"

function Assert-WorkspacePath([string]$Path) {
    $full = [System.IO.Path]::GetFullPath($Path)
    if (-not $full.StartsWith($root + [System.IO.Path]::DirectorySeparatorChar, [System.StringComparison]::OrdinalIgnoreCase)) {
        throw "Path release keluar dari workspace: $full"
    }
}

Assert-WorkspacePath $stageParent
Assert-WorkspacePath $output

if (Test-Path $stageParent) { Remove-Item -LiteralPath $stageParent -Recurse -Force }
if (Test-Path $archive) { Remove-Item -LiteralPath $archive -Force }
if (Test-Path $checksum) { Remove-Item -LiteralPath $checksum -Force }

New-Item -ItemType Directory -Path $stage -Force | Out-Null
New-Item -ItemType Directory -Path $output -Force | Out-Null

$directories = @("app", "config", "database", "public", "resources", "routes")
foreach ($directory in $directories) {
    Copy-Item -LiteralPath (Join-Path $root $directory) -Destination (Join-Path $stage $directory) -Recurse -Force
}

Copy-Item -LiteralPath (Join-Path $root "bootstrap") -Destination (Join-Path $stage "bootstrap") -Recurse -Force
Remove-Item -LiteralPath (Join-Path $stage "bootstrap/cache") -Recurse -Force -ErrorAction SilentlyContinue
New-Item -ItemType Directory -Path (Join-Path $stage "bootstrap/cache") -Force | Out-Null
Copy-Item -LiteralPath (Join-Path $root "bootstrap/cache/.gitignore") -Destination (Join-Path $stage "bootstrap/cache/.gitignore") -Force

Remove-Item -LiteralPath (Join-Path $stage "public/storage") -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item -LiteralPath (Join-Path $stage "public/hot") -Force -ErrorAction SilentlyContinue

$storagePlaceholders = @(
    "storage/app/.gitignore",
    "storage/app/private/.gitignore",
    "storage/app/public/.gitignore",
    "storage/framework/.gitignore",
    "storage/framework/cache/.gitignore",
    "storage/framework/cache/data/.gitignore",
    "storage/framework/sessions/.gitignore",
    "storage/framework/testing/.gitignore",
    "storage/framework/views/.gitignore",
    "storage/logs/.gitignore"
)
foreach ($relative in $storagePlaceholders) {
    $destination = Join-Path $stage $relative
    New-Item -ItemType Directory -Path (Split-Path $destination) -Force | Out-Null
    Copy-Item -LiteralPath (Join-Path $root $relative) -Destination $destination -Force
}

$files = @(
    "artisan", "composer.json", "composer.lock", "package.json", "package-lock.json",
    "vite.config.js", ".editorconfig", ".gitattributes", ".gitignore",
    ".env.production.example", "README.md"
)
foreach ($file in $files) {
    Copy-Item -LiteralPath (Join-Path $root $file) -Destination (Join-Path $stage $file) -Force
}

New-Item -ItemType Directory -Path (Join-Path $stage "docs") -Force | Out-Null
Copy-Item -LiteralPath (Join-Path $root "docs/DEPLOYMENT.md") -Destination (Join-Path $stage "docs/DEPLOYMENT.md") -Force

if (-not (Test-Path (Join-Path $stage "public/build/manifest.json"))) {
    throw "Vite manifest tidak ditemukan. Jalankan npm run build terlebih dahulu."
}

Push-Location $stage
try {
    composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
    if ($LASTEXITCODE -ne 0) { throw "Composer production install gagal." }
} finally {
    Pop-Location
}

$forbidden = @(
    (Join-Path $stage ".env"),
    (Join-Path $stage "public/hot"),
    (Join-Path $stage "public/storage"),
    (Join-Path $stage "node_modules"),
    (Join-Path $stage "tests"),
    (Join-Path $stage "references")
)
foreach ($path in $forbidden) {
    if (Test-Path $path) { throw "Artifact memuat path terlarang: $path" }
}

& tar.exe -a -c -f $archive -C $stage .
if ($LASTEXITCODE -ne 0) { throw "Kompresi ZIP release gagal." }
$hash = (Get-FileHash -Algorithm SHA256 -LiteralPath $archive).Hash.ToLowerInvariant()
Set-Content -LiteralPath $checksum -Value "$hash  zzk-web-production.zip" -Encoding ascii

Write-Output "RELEASE_ARCHIVE=$archive"
Write-Output "SHA256=$hash"
