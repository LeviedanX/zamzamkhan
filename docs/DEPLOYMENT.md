# Deployment Aman

## Prasyarat

- Document root wajib menunjuk ke `public/`, bukan root project.
- Gunakan PHP-FPM/web server production; jangan memakai `php artisan serve`.
- Salin `.env.production.example` menjadi `.env`, isi secret melalui secret manager, lalu batasi permission file.
- Gunakan akun database khusus aplikasi dengan hak minimum. Jangan gunakan `root`.
- TLS/HTTPS wajib aktif.

## Urutan release

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan optimize:clear
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan security:scan --production
php artisan deploy:check --production
```

Jangan lanjutkan pergantian release aktif bila `security:scan` atau `deploy:check`
gagal. Pastikan `public/hot` tidak ikut ke artefak production.

## Scheduler dan worker

Jalankan scheduler setiap menit:

```cron
* * * * * cd /var/www/zzk-web && php artisan schedule:run >> /dev/null 2>&1
```

Scheduler menjalankan pemindaian integritas harian. Pantau
`storage/logs/security.log` dan kirim alert bila ada event level `critical`.

## Akun admin

- Password minimum 14 karakter, huruf besar/kecil, angka, dan simbol.
- Buat/rotasi credential melalui `php artisan admin:rotate-credentials`.
- Jangan menyimpan `ADMIN_PASSWORD` permanen setelah proses bootstrap selesai.
- Perubahan email/password otomatis mencabut sesi admin lain.

## Web server

- Apache memakai aturan `public/.htaccess`.
- Nginx dapat memakai `docs/nginx.conf.example`; ganti domain, path, dan socket
  PHP-FPM, lalu wajib jalankan `nginx -t`.
- Folder `/storage/` hanya boleh menyajikan JPG, PNG, dan WEBP. Semua file aktif,
  HTML, SVG, dotfile, dan ekstensi lain harus ditolak.

## Verifikasi pascadeploy

1. Buka homepage dan panel admin melalui HTTPS.
2. Pastikan response memiliki CSP tanpa `unsafe-inline`/`unsafe-eval`, HSTS,
   `X-Content-Type-Options: nosniff`, dan `X-Frame-Options: DENY`.
3. Uji login salah berulang hingga throttle aktif.
4. Uji logout dan rotasi password; sesi lain harus tidak berlaku.
5. Unggah gambar valid, lalu pastikan file non-gambar/polyglot ditolak.
6. Uji input `<script>`, event handler HTML, `javascript:`, `judol`, dan bentuk
   obfuscation; request harus ditolak dengan validation error.
7. Jalankan ulang:

```bash
php artisan security:scan --production
php artisan deploy:check --production
```

## Respons insiden judol/webshell

1. Aktifkan maintenance mode dan snapshot bukti sebelum membersihkan.
2. Putuskan credential hosting, database, email, deployment, dan seluruh akun admin.
3. Periksa `security.log`, access log web server, cron, user sistem, SSH key,
   database, serta seluruh file di `public/` dan `storage/app/public/`.
4. Deploy ulang dari artefak bersih; jangan hanya menghapus halaman spam.
5. Jalankan migration, `security:scan`, test suite, dan `deploy:check` sebelum
   membuka trafik kembali.
