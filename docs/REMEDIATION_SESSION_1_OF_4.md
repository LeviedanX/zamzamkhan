# Remediasi Project PT Zam Zam Khan — Sesi 1/4

## Status Patch

Selesai untuk scope Sesi 1: rate limiting login, keamanan AdminSeeder, invalidasi cookie bocor, export POST ber-CSRF, path export unik, konsistensi file-histori, dan isolasi filesystem test.

Project belum dinyatakan siap deploy karena remediasi CMS, admin/data integrity, dan hardening environment masih dijadwalkan pada Sesi 2–4.

## File yang Diubah

- `app/Http/Controllers/Admin/AuthController.php`
- `app/Http/Controllers/Admin/ReportController.php`
- `config/admin.php`
- `database/seeders/AdminSeeder.php`
- `database/migrations/2026_07_13_000000_harden_report_export_paths.php`
- `routes/web.php`
- `resources/views/admin/reports/index.blade.php`
- `resources/js/app.js`
- `tests/Feature/SiteSettingTest.php`
- `tests/Feature/OperasionalInternalTest.php`
- `tests/Feature/AdminSeederSecurityTest.php`
- `.gitignore`
- `.env.example`
- `.env.production.example`
- `public/build/*` diregenerasi oleh Vite

File `ck.txt` dihapus karena berisi cookie sesi aktual.

## Ringkasan Perubahan

### Autentikasi admin

- Email login di-trim dan dinormalisasi ke lowercase.
- Login dibatasi lima kegagalan per kombinasi hash email dan IP selama 60 detik.
- Percobaan berikutnya menghasilkan HTTP 429 beserta waktu tunggu.
- Limiter dibersihkan setelah login berhasil.
- Nilai email tidak disimpan mentah sebagai cache key limiter.

### AdminSeeder

- Credential fallback statis dihapus.
- `ADMIN_EMAIL` dan `ADMIN_PASSWORD` wajib tersedia; seeder gagal eksplisit bila kosong.
- Konfigurasi seed dipindah ke `config/admin.php` agar kompatibel dengan config cache.
- Seeding ulang hanya memperbarui nama/status dan tidak mereset password admin existing.
- Template env development dan production mendokumentasikan variabel wajib.

### Export laporan

- Endpoint pembuat CSV/XLSX berubah dari GET menjadi POST ber-CSRF.
- Link export diubah menjadi form POST sehingga prefetch/prerender tidak dapat membuat file.
- Nama file memakai timestamp + ULID sehingga dua export pada detik yang sama tetap berbeda.
- Unique index ditambahkan pada `report_exports.file_path`.
- Migration menangani data lama yang memiliki path ganda sebelum membuat unique index.
- Jika pencatatan histori gagal, file baru langsung dihapus sebagai compensating action.
- Download histori diberi `download` dan `data-no-prefetch`.
- Speculation Rules dan partial navigation mengecualikan link download report.
- Tombol form download otomatis aktif kembali setelah respons download.

### Cookie dan test isolation

- `ck.txt` dihapus tanpa mereproduksi nilainya.
- Tujuh file session lokal diinvalidasi; seluruh admin lokal harus login ulang.
- Pola cookie jar ditambahkan ke `.gitignore`.
- Test report memakai `Storage::fake('local')`, sehingga tidak lagi meninggalkan artefak pada private storage aplikasi.

## Modul yang Disembunyikan/Dideprecated

Tidak ada perubahan modul pada sesi ini.

## Dampak ke Public Website

- Tidak ada perubahan alur website publik.
- Asset frontend berhasil dibangun ulang.
- Tidak ada credential atau token baru yang ditambahkan ke frontend.

## Dampak ke Admin

- Login diblokir sementara setelah lima kegagalan beruntun.
- Seluruh sesi lokal lama telah berakhir sebagai respons terhadap cookie yang bocor.
- Export tetap menghasilkan download CSV/XLSX, tetapi request kini aman melalui POST.
- History download tidak lagi diprefetch atau diproses partial navigation.

## Pengujian yang Dilakukan

- PHP lint seluruh file PHP yang diubah: lulus.
- Targeted test: 26 test, 123 assertion, seluruhnya lulus.
- Full regression: **86 test, 569 assertion, seluruhnya lulus**.
- Test GET export menghasilkan HTTP 405: lulus.
- Test dua export pada detik sama mempunyai path berbeda: lulus.
- Test form export memakai POST dan CSRF: lulus.
- Test login menghasilkan 429 setelah lima kegagalan: lulus.
- Test seeder tanpa credential gagal eksplisit: lulus.
- Test seeding ulang tidak mereset password: lulus.
- Vite production build: lulus.
- Composer validate dan audit: lulus, 0 advisory.
- npm production audit: lulus, 0 vulnerability.
- Config cache, route cache, dan view cache: lulus; cache pengujian dibersihkan.
- Migration diterapkan: seluruh migration berstatus `Ran`.
- Metadata MySQL: `report_exports_file_path_unique` aktif dan tidak ada path ganda.
- Pemindaian scope aplikasi aktif: tidak ada marker fallback credential/cookie.

## Hal yang Belum Dikerjakan

- Sinkronisasi nullable CMS, navigasi/section dinamis, Agenda, URL allowlist, dan JSON-LD.
- Penggantian media atomic, purge Agenda, revoke sesi setelah perubahan password, constraint/index lain, dan accessibility admin.
- User MySQL production least-privilege beserta credential server nyata.
- Header keamanan, env production final, release build tanpa `public/hot`, smoke test production, dan rollback check.

## Catatan Risiko

- Folder `references/` masih memuat source pembanding lama dengan credential fallback. Folder tersebut tidak dijalankan dan sengaja tidak diubah sesuai instruksi project.
- Migration akan mempertahankan satu referensi file untuk setiap path lama yang duplikat dan membuat referensi duplikat lain menjadi null. Database lokal saat migration tidak memiliki duplikasi.
- Sebanyak 13 file report orphan lama tetap tidak dihapus karena belum ada kebijakan rekonsiliasi/grace period; test baru tidak menambah jumlah tersebut.
- Template production memakai placeholder. Credential production tetap harus dibuat dan dimasukkan melalui secret management server, bukan disimpan di repository.

## Rencana Sesi 2/4

Menyempurnakan integrasi CMS ke website publik: nullable field, section/nav/form dinamis, Agenda tanpa empty section, URL allowlist, structured data aman, SEO, dan regression browser public.
