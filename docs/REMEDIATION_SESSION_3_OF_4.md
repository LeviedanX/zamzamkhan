# Remediasi Sesi 3 dari 4 — Admin, Media, Data, dan Operasional

## Status Patch

Selesai untuk ruang lingkup Sesi 3/4. Project belum dinyatakan siap deploy sebelum hardening environment, header keamanan, release artifact, dan smoke test production-like pada Sesi 4.

## File yang Diubah

- `app/Support/PublicMedia.php`
- `app/Support/AgendaPurger.php`
- `app/Http/Middleware/EnsureAdminIsActive.php`
- `app/Console/Commands/PurgeOperationalData.php`
- `app/Http/Controllers/Admin/AccountController.php`
- `app/Http/Controllers/Admin/AgendaController.php`
- `app/Http/Controllers/Admin/ArticleController.php`
- `app/Http/Controllers/Admin/BusinessApplicationController.php`
- `app/Http/Controllers/Admin/ClientController.php`
- `app/Http/Controllers/Admin/DashboardController.php`
- `app/Http/Controllers/Admin/FaqController.php`
- `app/Http/Controllers/Admin/HeroController.php`
- `app/Http/Controllers/Admin/SeoController.php`
- `app/Http/Controllers/Admin/ServiceController.php`
- `app/Http/Controllers/Admin/SiteSettingController.php`
- `app/Http/Controllers/Admin/TestimonialController.php`
- `app/Http/Controllers/Admin/VisitorAnalyticsController.php`
- `app/Http/Requests/Admin/BusinessApplicationRequest.php`
- `app/Http/Requests/Admin/UpdateSiteSettingRequest.php`
- `config/admin.php`
- `routes/web.php`
- `routes/console.php`
- `database/seeders/ContentSeeder.php`
- `database/migrations/2026_07_13_010000_enforce_cms_data_integrity.php`
- `database/migrations/2026_07_13_020000_fix_order_column_defaults.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/admin.blade.php`
- `resources/views/admin/agendas/form.blade.php`
- `resources/views/admin/articles/form.blade.php`
- `resources/views/admin/faqs/form.blade.php`
- `resources/views/admin/seo/edit.blade.php`
- `resources/views/admin/services/form.blade.php`
- `resources/views/admin/settings/edit.blade.php`
- `resources/css/app.css`
- `resources/js/app.js`
- `tests/TestCase.php`
- `tests/Feature/AdminSecurityAndOperationsTest.php`
- `tests/Feature/AgendaScheduleTest.php`
- `.env.example`
- `.env.production.example`

## Ringkasan Perubahan

- Sesi admin yang akunnya telah dinonaktifkan sekarang langsung ditolak dan diinvalidasi.
- Rotasi email/password mencabut sesi database lain serta merotasi remember token.
- Upload SVG untuk logo dilarang; hanya raster JPG/PNG/WebP yang diterima pada public disk.
- Penggantian media menyimpan file baru dan database terlebih dahulu, lalu menghapus file lama. Kegagalan database membersihkan file baru sebagai compensating action.
- Artikel, Agenda, SEO image, dan logo memiliki opsi hapus media eksplisit.
- Delete record media dilakukan sebelum cleanup file lama sehingga kegagalan database tidak meninggalkan record yang menunjuk file hilang.
- GET daftar Agenda kembali read-only. Purge permanen hanya berjalan lewat command/scheduler.
- Analytics tidak lagi memuat seluruh record ke collection PHP; ringkasan, bucket waktu, halaman, perangkat, dan referrer diagregasi di SQL.
- Retensi terjadwal ditambahkan untuk analytics dan export laporan, termasuk file export orphan yang melewati grace period.
- Service dan FAQ memakai pengurutan transaksional yang sama dengan modul lain; order minimal satu dan ikon Service memakai allowlist.
- Filter daftar pengajuan divalidasi; tanggal sertifikat wajib ketika status `Sertifikat Terbit`.
- Dashboard menghitung hanya Agenda aktif yang belum selesai.
- Singleton SiteSetting/Hero, domain status/format, serta order Service/FAQ ditegakkan pada schema MySQL. Index query publik Service/FAQ ditambahkan.
- Skip link publik/admin, satu `h1` utama per halaman admin, focus trap dialog, dan focus return ditambahkan.
- PHPUnit sekarang fail-fast sebelum `RefreshDatabase` jika koneksi bukan SQLite `:memory:`.

## Modul yang Disembunyikan/Dideprecated

- Tidak ada modul baru yang dideprecated.
- Pesan Masuk, Alur Pendampingan, dan Galeri tetap tidak diaktifkan kembali.

## Dampak ke Public Website

- Media CMS tidak lagi berisiko menunjuk file lama yang sudah terhapus ketika upload pengganti gagal.
- Logo SVG aktif tidak dapat disimpan ke origin publik.
- Navigasi keyboard memiliki skip link; fokus dialog tetap berada dalam dialog sampai ditutup.
- Tidak ada perubahan struktur konten publik atau CTA utama.

## Dampak ke Admin

- Akun nonaktif dan sesi perangkat lama ditangani lebih ketat.
- Admin dapat menghapus cover Artikel, gambar Agenda, OG image, dan logo tanpa mengunggah pengganti.
- Service/FAQ mempunyai urutan 1..N yang stabil.
- Membuka daftar Agenda tidak lagi menghapus record.
- Dashboard analytics lebih aman untuk pertumbuhan data jangka panjang.
- Seluruh 27 halaman utama/index/create admin diuji dapat dirender.

## Pengujian yang Dilakukan

- PHP lint seluruh file baru/utama yang berubah: lulus.
- Laravel Pint pada file PHP yang berubah: lulus setelah formatting.
- Full regression akhir: **97 test, 667 assertion, seluruhnya lulus**.
- Test terfokus awal: **48 test, 331 assertion, seluruhnya lulus**.
- Test keamanan/operasional baru: **6 test, 55 assertion, seluruhnya lulus**.
- Migration MySQL `010000` dan `020000`: berhasil diterapkan.
- Schema runtime: unique singleton SiteSetting/Hero dan index Service aktif terverifikasi.
- Scheduler: purge Agenda setiap 15 menit; purge operasional setiap hari pukul 02:15.
- Dry-run retensi: tidak ada data/file production-like yang melewati masa retensi.
- Build Vite production dan kompilasi Blade: lulus.
- Composer validate/audit dan npm production audit: lulus, 0 advisory/kerentanan dependency.
- Browser admin desktop: tanpa overflow, gambar rusak, atau console warning/error; satu `h1` dan skip target valid.
- Browser admin mobile 390×844: tanpa overflow; drawer terbuka normal dan fokus pindah ke tombol tutup.
- Modal delete: fokus awal, siklus Shift+Tab, Escape, dan return ke trigger tervalidasi.
- Akun smoke-test lokal dihapus setelah pengujian.

## Insiden dan Pemulihan Data Lokal

- Saat regresi dijalankan setelah `config:cache`, PHPUnit mewarisi koneksi MySQL dari cache sehingga `RefreshDatabase` mengosongkan data lokal. Ini bukan database production, tetapi tetap merupakan insiden data lokal.
- Baseline dipulihkan dari seeder project: 8 layanan, 5 FAQ, 6 keunggulan, 4 statistik, 8 klien, 11 testimoni, 3 artikel, 7 kategori bisnis, SiteSetting, dan Hero.
- Admin lokal dipulihkan dari backup SQLite project yang tersedia.
- Record `PT Uji Coba / Merek Uji` tidak dipulihkan karena file export membuktikan bahwa record tersebut adalah fixture pengujian.
- Setelah pemulihan dan full test terisolasi, MySQL tetap berisi 1 admin, 8 layanan, 5 FAQ, dan 3 artikel.
- Guard baru diuji dengan config cache aktif: test berhenti sebelum migrasi dan data MySQL tetap utuh.

## Hal yang Belum Dikerjakan

- Konfigurasi domain/credential production nyata dan database user least-privilege.
- Security headers/CSP final pada aplikasi atau reverse proxy.
- Penghapusan `public/hot`, release artifact bersih, permission storage, backup/restore drill, dan rollback migration.
- Verifikasi login menggunakan credential admin lokal yang dipulihkan dari backup.
- Smoke test production-like setelah env final tersedia.
- Seluruh item tersebut menjadi scope Sesi 4/4.

## Catatan Risiko

- Password admin yang dipulihkan berasal dari backup SQLite lokal tanggal 7 Juli 2026; bila password pernah diubah setelah tanggal tersebut, credential terbaru perlu dirotasi kembali melalui prosedur aman.
- Migration singleton sengaja gagal eksplisit jika environment lain memiliki lebih dari satu SiteSetting atau Hero; data harus dikonsolidasikan sebelum deploy, tidak dihapus otomatis.
- CHECK constraint diterapkan pada MySQL; SQLite test menjaga aturan yang sama melalui validator aplikasi karena SQLite tidak mendukung penambahan constraint tersebut dengan pola ALTER yang sama.
- Retensi membutuhkan cron `php artisan schedule:run` setiap menit pada server deployment.
- Root Git masih mengarah ke profil pengguna, bukan repository mandiri project; ini harus dibenahi di luar perubahan source setelah backup/history asal dipastikan.
