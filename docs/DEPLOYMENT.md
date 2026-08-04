# Deployment Aman

## Batas kesiapan

Paket release membuktikan source, dependency, build frontend, dan pemeriksaan
aplikasi dapat dijalankan. Go-live tetap bersyarat sampai pemeriksaan pada server
asli lulus. Jangan menandai deployment selesai hanya berdasarkan hasil lokal.

Jangan pernah mengirim atau menyimpan password, `APP_KEY`, token, maupun isi
`.env` production di Git, tiket, screenshot, atau dokumentasi.

## Data hosting yang harus tersedia

Catat tanpa menuliskan secret:

- provider dan nama paket hosting;
- control panel serta ketersediaan SSH/Terminal;
- sistem operasi dan web server: Apache/LiteSpeed atau Nginx;
- domain kanonik dan apakah `www` diarahkan ke domain tersebut;
- path absolut aplikasi dan document root;
- path binary PHP yang digunakan web server dan cron;
- versi PHP dan daftar extension aktif;
- nama host, port, database, dan user MySQL non-root;
- dukungan symbolic link dan cron setiap menit;
- lokasi backup database dan `storage/app/public`.

Jika salah satu informasi tersebut belum diketahui, konfirmasi ke provider
sebelum upload.

## Preflight hosting sebelum upload

PHP untuk web server, Terminal, dan cron wajib memakai versi yang sama: PHP 8.3
atau lebih baru. Jalankan:

```bash
php -v
php -r '$required=["pdo_mysql","dom","fileinfo","openssl","xmlreader","zip"];$missing=array_values(array_filter($required,fn($ext)=>!extension_loaded($ext)));echo $missing?"MISSING: ".implode(", ",$missing).PHP_EOL:"OK: ekstensi PHP lengkap".PHP_EOL;exit($missing?1:0);'
```

Syarat lain:

- document root wajib menunjuk ke folder `public/`, bukan root project;
- gunakan PHP-FPM/web server production, bukan `php artisan serve`;
- TLS/HTTPS wajib aktif untuk domain kanonik;
- `storage` dan `bootstrap/cache` harus writable oleh user web server;
- hosting harus mengizinkan link `public/storage`;
- database harus MySQL dengan user khusus aplikasi, bukan `root`;
- cron harus dapat menjalankan Artisan setiap menit.

Jika provider tidak dapat memenuhi salah satu syarat wajib, jangan lanjutkan
go-live pada paket tersebut.

## Pilih sumber deployment

### Paket ZIP dari folder DEPLOY

`zzk-web-production.zip` sudah memuat `vendor/` production dan hasil build
`public/build`. Hosting tidak memerlukan Node.js. Verifikasi checksum sebelum
ekstraksi dan jangan mengunggah file `.sha256` sebagai bagian aplikasi.

### Checkout source dari Git

Jika deployment dilakukan dari Git, bangun dependency dan aset:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
npm ci
npm run build
```

Pastikan `public/hot` tidak ada. `node_modules/`, test, dan `.git` tidak perlu
disajikan oleh web server.

## Siapkan environment production

Salin `.env.production.example` menjadi `.env`, sesuaikan domain dan database,
lalu batasi permission:

```bash
cp .env.production.example .env
chmod 600 .env
```

Aturan `APP_KEY`:

- fresh install: buat sekali dengan `php artisan key:generate --force`;
- update/restore: pertahankan `APP_KEY` lama; menggantinya dapat merusak data
  terenkripsi, cookie, dan sesi.

Jangan menyalin `.env` development. `APP_ENV=production`, `APP_DEBUG=false`,
`APP_URL`, `ASSET_URL`, dan `SESSION_DOMAIN` harus sesuai domain asli.

## Fresh install

Pastikan database kosong yang benar telah dipilih. Perintah berikut membuat
schema, admin pertama, storage link, cache production, lalu menjalankan gate
aplikasi:

```bash
php artisan optimize:clear
php artisan key:generate --force
php artisan migrate --force
php artisan admin:rotate-credentials
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan security:scan --production
php artisan deploy:check --production
```

`admin:rotate-credentials` adalah pilihan utama karena password dimasukkan
secara tersembunyi dan tidak menjadi argumen shell.

Jika Terminal tidak mendukung prompt interaktif, isi `ADMIN_EMAIL` dan
`ADMIN_PASSWORD` sementara di `.env`, kemudian jalankan:

```bash
php artisan db:seed --class=AdminSeeder --force
```

Setelah berhasil, kosongkan `ADMIN_PASSWORD`, jalankan `php artisan
optimize:clear`, kemudian ulangi cache dan kedua pemeriksaan production. Jangan
menjalankan seluruh `DatabaseSeeder` pada database yang sudah berisi data.

## Update deployment yang sudah berjalan

Jangan membuat `APP_KEY` baru dan jangan mengganti `.env` production dengan
template. Backup database dan upload sebelum migration:

Pada rilis yang menyatukan akun admin ke tabel `users`, migration akan
memindahkan akun beserta hash password dan statusnya, menyesuaikan relasi audit,
menghapus tabel lama `admins`, menormalkan admin tunggal menjadi ID `1`, lalu
mencabut sesi lama. Karena itu backup wajib tersedia dan seluruh admin harus
login ulang setelah deployment.

```bash
php artisan down
php artisan optimize:clear
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan security:scan --production
php artisan deploy:check --production
php artisan up
```

Jika salah satu pemeriksaan gagal, jangan menjalankan `php artisan up`; perbaiki
penyebabnya atau rollback ke release dan backup yang telah diverifikasi.

## Verifikasi server setelah konfigurasi

```bash
php artisan about --only=environment
php artisan migrate:status
php artisan schedule:list
php artisan security:scan --production
php artisan deploy:check --production
test -w storage && test -w bootstrap/cache
test -L public/storage
```

`deploy:check --production` memeriksa PHP, extension, konfigurasi aplikasi,
MySQL, migration, admin, singleton konten, storage, cookie, CSP, HSTS, dan log.
Command tersebut tidak dapat membuktikan document root, sertifikat TLS, cron
sistem, konfigurasi web server, atau kualitas backup; semua gate eksternal itu
tetap harus diperiksa manual.

Pastikan URL berikut tidak pernah menghasilkan `200 OK`:

```text
https://domain-anda/.env
https://domain-anda/composer.json
https://domain-anda/storage/file.php
```

## Scheduler dan worker

Jalankan scheduler setiap menit. Sesuaikan path aplikasi dan binary PHP dengan
hasil dari provider:

```cron
* * * * * cd /var/www/zzk-web && php artisan schedule:run >> /dev/null 2>&1
```

Pada cPanel, gunakan path absolut, misalnya
`/usr/local/bin/php /home/USER/zzk-web/artisan schedule:run`. Setelah disimpan,
periksa `php artisan schedule:list` dan log cron provider.

Queue production memakai `sync`, sehingga worker terpisah tidak diperlukan.
Scheduler menjalankan pemindaian integritas harian. Pantau
`storage/logs/security.log` dan kirim alert bila ada event level `critical`.

## Akun admin

- Password minimum 10 karakter.
- Fresh install wajib membuat admin sebelum `deploy:check`.
- Buat/rotasi credential melalui `php artisan admin:rotate-credentials`.
- Jangan menyimpan `ADMIN_PASSWORD` permanen setelah proses bootstrap selesai.
- Perubahan email/password otomatis mencabut sesi admin lain.

## Web server

- Apache/LiteSpeed memakai aturan `public/.htaccess`.
- Nginx dapat memakai `docs/nginx.conf.example`; ganti domain, path, dan socket
  PHP-FPM, lalu wajib jalankan `nginx -t`.
- Pada shared hosting Apache/LiteSpeed, `nginx -t` tidak relevan.
- Folder `/storage/` hanya boleh menyajikan JPG, PNG, dan WEBP. Semua file aktif,
  HTML, SVG, dotfile, dan ekstensi lain harus ditolak.

## Verifikasi pascadeploy

1. Pastikan domain HTTP mengarah ke HTTPS dan domain `www` mengikuti URL kanonik.
2. Buka health check `/up`, homepage, artikel, dan panel admin melalui HTTPS.
3. Pastikan `.env`, `composer.json`, `.git`, dan root project tidak dapat diakses.
4. Pastikan response memiliki CSP tanpa `unsafe-inline`/`unsafe-eval`, HSTS,
   `X-Content-Type-Options: nosniff`, dan `X-Frame-Options: DENY`.
5. Uji login salah berulang hingga throttle aktif.
6. Uji login benar, logout, dan rotasi password; sesi lain harus tidak berlaku.
7. Unggah gambar valid, lalu pastikan file non-gambar/polyglot ditolak.
8. Uji input `<script>`, event handler HTML, `javascript:`, `judol`, dan bentuk
   obfuscation; request harus ditolak dengan validation error.
9. Pastikan cron benar-benar menghasilkan eksekusi pada log provider.
10. Jalankan ulang:

```bash
php artisan security:scan --production
php artisan deploy:check --production
```

Go-live hanya boleh dinyatakan selesai bila pemeriksaan aplikasi dan seluruh gate
eksternal lulus pada domain asli.

## Respons insiden judol/webshell

1. Aktifkan maintenance mode dan snapshot bukti sebelum membersihkan.
2. Putuskan credential hosting, database, email, deployment, dan seluruh akun admin.
3. Periksa `security.log`, access log web server, cron, user sistem, SSH key,
   database, serta seluruh file di `public/` dan `storage/app/public/`.
4. Deploy ulang dari artefak bersih; jangan hanya menghapus halaman spam.
5. Jalankan migration, `security:scan`, test suite, dan `deploy:check` sebelum
   membuka trafik kembali.
