# Remediasi Sesi 4 dari 4 — Production Hardening dan Final Readiness

## Status Patch

**Selesai untuk source code dan workspace. Siap dijadikan release artifact setelah operator mengisi environment production nyata.**

Status ini tidak berarti server production sudah terkonfigurasi. Domain, TLS, secret, user database least-privilege, cron, dan backup server tidak tersedia di workspace sehingga wajib diselesaikan saat deployment dan diverifikasi dengan `php artisan deploy:check --production`.

## File yang Diubah

- `app/Http/Middleware/SecurityHeaders.php`
- `app/Console/Commands/CheckDeploymentReadiness.php`
- `app/Console/Commands/RotateAdminCredentials.php`
- `bootstrap/app.php`
- `config/security.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/admin.blade.php`
- `resources/views/articles/show.blade.php`
- `resources/views/partials/seo-jsonld.blade.php`
- `resources/views/admin/analytics/index.blade.php`
- `resources/js/app.js`
- `tests/Feature/SecurityHeadersTest.php`
- `tests/Feature/AdminSecurityAndOperationsTest.php`
- `tests/Feature/PublicCmsConsistencyTest.php`
- `.env.example`
- `.env.production.example`
- `docs/DEPLOYMENT.md`
- `scripts/build-release.ps1`
- `storage/framework/sessions/.gitignore`
- `.gitignore`
- `public/hot` dihapus
- `public/build/*` diregenerasi

## Ringkasan Perubahan

- Menambahkan security headers global untuk response normal, admin, health check, dan 404.
- Menambahkan CSP berbasis nonce. Inline script layout, JSON-LD, data analytics, Vite asset, dan Speculation Rules memakai nonce request yang sama.
- Menambahkan HSTS untuk request HTTPS ketika konfigurasi production mengaktifkannya.
- Admin response memakai `Cache-Control: no-store, private` dan `Pragma: no-cache`.
- Mengaktifkan trusted-host enforcement berdasarkan host `APP_URL` pada environment non-local/non-test.
- Menghapus `public/hot`; frontend kini memakai manifest/build production, bukan Vite dev server.
- Menambahkan `deploy:check` untuk memeriksa key, build, hot marker, storage link, permission, database, migration, admin, singleton CMS, WhatsApp, scheduler, serta aturan production.
- Menambahkan `admin:rotate-credentials` dengan input password tersembunyi dan pencabutan seluruh sesi database.
- Menambahkan panduan deployment, backup, least-privilege MySQL, permission, cron, cache, smoke test, dan rollback.
- Menginisialisasi repository Git mandiri pada root project agar scope release tidak lagi mencakup profil Windows.
- Menambahkan builder ZIP production yang menyertakan vendor runtime dan build frontend tanpa data/secret lokal.
- Menambahkan regression test security header/CSP/HSTS/cache policy dan command rotasi admin.

## Modul yang Disembunyikan/Dideprecated

- Pesan Masuk, Alur Pendampingan, dan Galeri telah dihapus permanen dari kode aktif, admin, route, seeder, dan database.
- Migration `2026_07_13_110000_remove_deprecated_cms_modules.php` menghapus tabel `messages`, `process_steps`, dan `galleries` beserta datanya.
- Form inbox publik ikut dihapus; jalur konsultasi publik tetap WhatsApp-first.

## Dampak ke Public Website

- Halaman publik memakai build production tanpa ketergantungan port Vite 5173.
- Browser menerima perlindungan clickjacking, MIME sniffing, referrer leakage, permission browser, opener isolation, dan CSP.
- JSON-LD tetap valid dan seluruh executable script memiliki nonce.
- Homepage desktop/mobile tetap bebas overflow dan broken image.

## Dampak ke Admin

- Sidebar dan dashboard tidak lagi memuat Alur Pendampingan, Galeri, atau Pesan Masuk.
- URL admin ketiga modul tidak lagi terdaftar dan akan menghasilkan 404.
- Login dan dashboard berhasil berjalan pada server production-like dengan CSP aktif.
- Halaman admin tidak boleh disimpan cache browser/proxy.
- Operator memiliki command aman untuk merotasi credential tanpa password di command line.
- Deployment dapat dihentikan secara otomatis bila environment atau artifact belum memenuhi syarat.

## Pengujian yang Dilakukan

- Full regression setelah penghapusan permanen: **104 test, 716 assertion, seluruhnya lulus**.
- Test terarah integrasi dan navigasi admin: **11 test, 114 assertion, seluruhnya lulus**.
- Vite production build setelah penghapusan: lulus.
- Full regression final: **101 test, 701 assertion, seluruhnya lulus**.
- Test terfokus CSP/security/operasional: **12 test, 101 assertion, seluruhnya lulus**.
- Laravel Pint: lulus setelah formatting.
- Composer validate/audit: lulus, 0 advisory.
- npm production audit: lulus, 0 vulnerability.
- Vite production build: lulus; manifest tersedia; `public/hot` tidak ada.
- `php artisan deploy:check`: seluruh core check lulus.
- `php artisan deploy:check --production`: gagal sesuai desain pada 9 item karena workspace masih memakai env development.
- Server sementara production-like memakai `APP_ENV=production`, debug off, build production, dan CSP enforcement aktif.
- Header runtime production-like: CSP terdeteksi, `nosniff`, `DENY`, dan referrer policy benar.
- Public browser: 0 referensi `:5173`, 0 overflow, 0 broken image, 1 `h1`, 0 anchor tanpa target, 0 JSON-LD invalid, dan semua script bernonce.
- Admin browser desktop/mobile: login berhasil, 0 overflow, 0 broken image, 1 `h1`, 0 script tanpa nonce, serta 0 console error/warning.
- Akun/server/telemetry smoke-test sementara telah dibersihkan; database kembali memiliki satu admin dan nol visit smoke-test.
- Pemindaian secret pada source aktif di luar `.env`, `references`, storage, dependency, dan artifact: tidak menemukan credential aktif.
- Artifact `.dist/zzk-web-production.zip`: 7.815 entry, vendor production tersedia, dan 0 runtime storage file.
- SHA-256 artifact terbaru: `7b6df1ea05ffd56b4c3b8cafd440a58ec5fe917ab27087ec4e8d597d4fd4835c` (cocok dengan `.dist/zzk-web-production.zip.sha256`).
- Artifact tidak memuat `.env`, SQLite, session, laporan, upload lokal, test, node_modules, `public/hot`, `public/storage`, atau references.

## Hal yang Belum Dikerjakan

- Menentukan domain final dan memasang sertifikat TLS pada hosting.
- Menghasilkan `APP_KEY` production baru melalui secret management.
- Membuat user MySQL production non-root dan password unik.
- Mengisi credential admin production dan melakukan rotasi/verifikasi login.
- Mengaktifkan cron scheduler pada server.
- Menjalankan backup/restore drill pada database dan storage server.

Item di atas memerlukan akses/keputusan infrastruktur dan tidak aman untuk dipalsukan dari workspace.

## Catatan Risiko

- CSP masih mengizinkan `unsafe-eval` karena Alpine.js build standar mengevaluasi ekspresi directive. Nonce menghapus kebutuhan `unsafe-inline` untuk script, tetapi migrasi ke Alpine CSP build dapat dipertimbangkan untuk menghapus residual `unsafe-eval`.
- HSTS hanya dikirim ketika Laravel mengenali request sebagai HTTPS. Reverse proxy wajib meneruskan proto dengan benar dan upstream tidak boleh dapat diakses langsung dari internet.
- `public/build` diabaikan Git; CI/server harus menjalankan `npm ci && npm run build` atau menyertakan folder build dalam artifact.
- Password admin lokal dipulihkan dari backup SQLite lama pada Sesi 3. Jalankan `php artisan admin:rotate-credentials` sebelum memakai environment ini sebagai basis deployment.
- Repository Git project kini mandiri tetapi belum memiliki commit awal karena pembuatan commit tidak dilakukan tanpa instruksi eksplisit pengguna.

## Keputusan Akhir

**Source application: siap release.**

**Server production: belum dapat dinyatakan live-ready sampai `php artisan deploy:check --production` lulus tanpa satu pun FAIL dan checklist `docs/DEPLOYMENT.md` dijalankan pada host tujuan.**
