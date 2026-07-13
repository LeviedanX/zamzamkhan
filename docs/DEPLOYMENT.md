# Panduan Deployment Production

## 1. Prasyarat

- PHP 8.3+ beserta ekstensi Laravel/MySQL, Node.js/npm, Composer 2, MySQL 8, dan HTTPS aktif.
- Document root web server wajib menunjuk ke folder `public/`, bukan root project.
- Buat user database khusus aplikasi. Jangan memakai `root`.

Contoh hak minimum setelah database dan user dibuat oleh administrator MySQL:

```sql
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP, REFERENCES
ON zzk_web.* TO 'zzk_app'@'127.0.0.1';
FLUSH PRIVILEGES;
```

Simpan password database, `APP_KEY`, dan credential admin di secret manager/server. Jangan commit `.env`.

## 2. Backup Sebelum Release

```bash
mysqldump --single-transaction --routines --triggers zzk_web > /secure-backup/zzk_web_before_release.sql
tar -czf /secure-backup/zzk_storage_before_release.tar.gz storage/app/public storage/app/private
```

Pastikan kedua backup dapat dibaca dan berada di luar document root.

## 3. Siapkan Release

Pada Windows, artifact siap unggah dapat dibuat otomatis:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File scripts\build-release.ps1
```

Output berada di `.dist/zzk-web-production.zip` beserta file checksum SHA-256. Artifact sudah menyertakan vendor production dan build frontend, tetapi sengaja tidak menyertakan `.env`, database lokal, session, upload, laporan, test, atau folder referensi.

Alternatif build langsung pada CI/server:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
npm ci
npm run build
rm -f public/hot
```

Folder `public/build` diabaikan Git. Karena itu build harus dijalankan di CI/server atau disertakan eksplisit dalam artifact release.

Salin `.env.production.example` menjadi `.env`, isi seluruh placeholder, lalu buat key production baru:

```bash
php artisan key:generate
```

Jangan menyalin `APP_KEY` development karena akan memperluas dampak kebocoran cookie/data terenkripsi.

## 4. Database dan Admin

```bash
php artisan migrate --force
php artisan db:seed --class=DatabaseSeeder --force
```

`ADMIN_EMAIL` dan `ADMIN_PASSWORD` wajib tersedia saat seeding instalasi baru. Setelah admin tercipta, hapus nilai password dari environment bila pipeline tidak lagi membutuhkannya.

Untuk merotasi credential tanpa menampilkan password:

```bash
php artisan admin:rotate-credentials
```

## 5. Storage dan Permission

```bash
php artisan storage:link
chown -R www-data:www-data storage bootstrap/cache
find storage bootstrap/cache -type d -exec chmod 775 {} \;
find storage bootstrap/cache -type f -exec chmod 664 {} \;
```

Sesuaikan user web server jika bukan `www-data`. Jangan memberikan permission `777`.

## 6. Scheduler dan Queue

Tambahkan satu cron:

```cron
* * * * * cd /var/www/zzk-web && php artisan schedule:run >> /dev/null 2>&1
```

Scheduler menjalankan retensi data operasional setiap hari. Agenda selesai tetap disimpan sebagai histori CMS dan otomatis tidak ditampilkan di homepage. Project belum memiliki job async wajib; worker queue baru diperlukan ketika job async ditambahkan.

## 7. Cache dan Pemeriksaan

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan deploy:check --production
```

Jangan menjalankan PHPUnit setelah `config:cache`. Guard test akan membatalkan proses jika koneksi bukan SQLite `:memory:`. Untuk test:

```bash
php artisan config:clear
php artisan test
```

## 8. Smoke Test

- `/`, `/artikel`, satu detail artikel, `/sitemap.xml`, `/robots.txt`, `/up`, dan halaman 404.
- Login admin, dashboard, Profil, Hero, Layanan, Artikel, FAQ, SEO, laporan, analytics, dan logout.
- Upload lalu hapus satu media uji.
- Semua CTA WhatsApp menuju nomor production.
- Header `Content-Security-Policy`, `Strict-Transport-Security`, `X-Content-Type-Options`, dan `X-Frame-Options` tersedia.
- Browser desktop/mobile tidak mengalami overflow, broken image, atau error console.

## 9. Rollback

1. Aktifkan maintenance mode: `php artisan down --render="errors::503"`.
2. Kembalikan source/artifact release sebelumnya.
3. Untuk migration yang aman dibalik, gunakan `php artisan migrate:rollback --step=N` hanya setelah memeriksa migration batch.
4. Jika rollback schema berisiko kehilangan data, pulihkan dump MySQL dan storage dari backup, jangan memaksakan rollback migration.
5. Jalankan `php artisan optimize:clear`, cache ulang release lama, smoke test, lalu `php artisan up`.

## 10. Konfigurasi Reverse Proxy

- Teruskan HTTPS/proto dengan benar agar Laravel mengenali request secure dan mengirim HSTS.
- Batasi akses langsung ke upstream aplikasi; jangan percaya header proxy dari internet terbuka.
- Terapkan TLS modern, redirect HTTP ke HTTPS, batas ukuran request yang sesuai upload maksimum 4 MB, rate limit tambahan untuk `/admin/login`, serta backup terjadwal terenkripsi.
