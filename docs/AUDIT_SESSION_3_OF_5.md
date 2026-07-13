# Audit Project PT Zam Zam Khan — Sesi 3/5

## Status

Selesai untuk scope sesi 3: autentikasi dan akun admin, dashboard/sidebar, seluruh CRUD, kategori, upload/media, pengajuan, laporan/export, analytics, route-model binding, CSRF, dan otorisasi.

Tidak ada source code aplikasi yang diubah. Validasi visual admin belum dapat dilakukan karena kegagalan kanal browser pada sesi 2; laporan ini tidak mengklaim layout admin bebas overflow/overlap.

## Ringkasan Eksekutif

Fondasi admin cukup baik: 84 route admin terdaftar; 82 route non-login seluruhnya dilindungi `auth:admin`, sedangkan GET/POST login memakai `guest:admin`. Seluruh form POST admin yang ditemukan memiliki CSRF. Input CRUD utama divalidasi, password di-hash, sesi diregenerasi, upload raster dibatasi, laporan disimpan pada private disk, dan query utama memakai Eloquent/binding.

Risiko tertinggi ada pada export laporan. Endpoint export memakai GET dan menghasilkan file/histori, sedangkan JavaScript global melakukan prefetch/prerender dan navigasi parsial pada link GET. Hover dapat memicu export tanpa klik; klik normal dapat memanggil endpoint dua kali. Nama file hanya presisi detik, sehingga histori berbeda dapat menunjuk file yang sama dan saling merusak ketika salah satu dihapus.

## Inventaris Modul Admin

| Kelompok | Modul | Status audit |
|---|---|---|
| Autentikasi | Login, logout, akun admin | Aktif; session handling benar, rate limit belum ada |
| Dashboard | Statistik konten, navigasi cepat | Aktif; route dashboard/sidebar sinkron |
| Konten | Hero, Profil, Layanan, Keunggulan, Statistik, Klien, Testimoni, Agenda, Artikel, FAQ | Aktif; validasi tersedia, media replacement belum atomic |
| Master data | Kategori Artikel, Kategori Bisnis | Aktif; kategori terpakai dilindungi dari penghapusan |
| Operasional | Data Pengajuan dan histori status | Aktif; transaksi dipakai untuk data + histori |
| Pelaporan | Filter, CSV, XLSX, cetak, histori download/hapus | Aktif; terdapat bug GET/prefetch/double-request |
| Analytics | Periode day/week/month/year/overall | Aktif; agregasi masih dilakukan di memori |
| Deprecated | Galeri, Pesan Masuk, Alur Pendampingan | Tidak memiliki route/menu aktif |

## Temuan Prioritas

### ADM-01 — High — Export GET berinteraksi dengan prefetch dan menghasilkan request ganda

- Lokasi: `routes/web.php:87-93`, `resources/views/admin/reports/index.blade.php:60-64`, `resources/js/app.js:715-748,775-808,828-833,894-924`, `app/Http/Controllers/Admin/ReportController.php:35-85`.
- Bukti:
  - CSV/XLSX memakai GET tetapi menulis file dan baris `report_exports`.
  - Link export tidak memiliki atribut `download` atau opt-out prefetch.
  - Speculation Rules mencocokkan seluruh `/*`; fallback hover membuat `<link rel="prefetch">` untuk seluruh internal GET.
  - Navigasi parsial admin mencegat klik, melakukan `fetch()`, lalu menolak respons non-HTML dan menjalankan `location.assign(url)`.
- Dampak:
  - Hover/focus/prerender dapat membuat export tanpa aksi eksplisit.
  - Klik export dapat menghasilkan dua request: fetch pertama dan navigasi fallback kedua.
  - Storage dan histori dapat bertambah tanpa kontrol; audit trail menjadi menyesatkan.
- Fix:
  - Ubah proses pembuatan export menjadi POST ber-CSRF, lalu download hasilnya melalui GET yang read-only; atau minimal beri opt-out eksplisit dari seluruh mekanisme prefetch/partial navigation.
  - Kecualikan `/admin/reports/export/*` dan `/admin/reports/history/*/download` dari Speculation Rules, hover prefetch, dan partial navigation.
- Mitigation: tambahkan idempotency key dan rate limit export.

### ADM-02 — High — Nama file export dapat bertabrakan dalam detik yang sama

- Lokasi: `app/Http/Controllers/Admin/ReportController.php:41,71,114-149`.
- Bukti: path hanya memakai `now()->format('Y-m-d-His')`.
- Dampak: dua export format sama dalam detik yang sama menimpa file yang sama tetapi membuat dua histori. Menghapus salah satu histori menghapus file yang masih dirujuk histori lainnya.
- Fix: gunakan UUID/ULID/random suffix, tambahkan unique constraint pada `file_path`, dan hapus file hanya jika tidak lagi dirujuk record lain.
- Keterkaitan: bug request ganda ADM-01 membuat collision ini sangat mudah terjadi.

### ADM-03 — High — Login admin belum memiliki rate limiting

- Lokasi: `routes/web.php:60-64`, `app/Http/Controllers/Admin/AuthController.php:16-39`.
- Bukti: tidak ada `throttle`, `RateLimiter`, atau limiter email+IP.
- Dampak: brute force dan credential stuffing tidak dibatasi oleh aplikasi.
- Fix: limiter khusus login, respons 429, reset setelah login sukses, dan test batas percobaan.

### ADM-04 — High — AdminSeeder mempunyai credential fallback yang dapat ditebak

- Lokasi: `database/seeders/AdminSeeder.php:12-20`.
- Bukti: `ADMIN_EMAIL`/`ADMIN_PASSWORD` mempunyai default literal dan `updateOrCreate` selalu menulis password.
- Dampak: seeding ulang tanpa env dapat membuat atau mereset admin ke credential yang diketahui.
- Kondisi runtime: akun aktif saat audit tidak menggunakan password fallback, tetapi env `ADMIN_PASSWORD` tidak diset.
- Fix: fail fast dan jangan reset password akun existing dari seeder umum.

## Temuan Media dan Storage

### ADM-05 — Medium — Penggantian media tidak atomic

- Lokasi: `HeroController.php:54-70`, `SiteSettingController.php:34-41`, `SeoController.php:31-39`, `ArticleController.php:65-70`, `AgendaController.php:46-54`, `ClientController.php:17-20`, `TestimonialController.php:17-20`.
- Bukti: sebagian besar update menghapus file lama sebelum file baru tersimpan dan sebelum database berhasil diperbarui.
- Dampak: kegagalan disk atau database meninggalkan record yang menunjuk file hilang. Sebaliknya, file baru dapat menjadi orphan bila penyimpanan database gagal setelah upload.
- Fix: simpan file baru lebih dulu, commit record, baru hapus file lama; jika database gagal, hapus file baru sebagai compensating action. Gunakan filesystem yang melempar exception atau periksa hasil operasi.

### ADM-06 — Medium — Test laporan menulis ke private storage nyata

- Lokasi: `tests/Feature/OperasionalInternalTest.php:53-204`, `config/filesystems.php:33-39`.
- Bukti runtime: tabel `report_exports` berisi 0 baris, tetapi `storage/app/private/reports` memiliki 13 file dengan total 39119 byte. Test memakai `RefreshDatabase` tetapi tidak `Storage::fake('local')`.
- Dampak: test meninggalkan file orphan, mencampur artefak testing dengan storage aplikasi, dan dapat menghapus/menimpa file bernama sama.
- Fix: fake disk local pada setiap test export atau arahkan filesystem testing ke direktori sementara unik; bersihkan pada teardown.
- Catatan: file tidak dihapus selama audit agar tidak mengubah data pengguna tanpa izin.

### ADM-07 — Low — UI logo menerima SVG tetapi validator menolaknya

- Lokasi: `resources/views/admin/settings/edit.blade.php:217`, `app/Http/Requests/Admin/UpdateSiteSettingRequest.php:42`.
- Bukti runtime: fake upload SVG menghasilkan `SVG_RULE_FAIL`; UI `accept` dan rule `mimes` mencantumkan SVG, tetapi rule `image` Laravel tidak mengizinkan SVG secara default.
- Dampak: admin dapat memilih file yang kemudian selalu ditolak.
- Fix: rekomendasi aman adalah hapus SVG dari UI/rule dan gunakan PNG/WebP. Jika SVG wajib, lakukan sanitasi SVG khusus sebelum menyimpannya ke public disk.

### ADM-08 — Low — Beberapa media tidak dapat dihapus dari admin

- Lokasi: form Artikel cover (`resources/views/admin/articles/form.blade.php:80-84`), Agenda (`resources/views/admin/agendas/form.blade.php:68`), SEO (`resources/views/admin/seo/edit.blade.php:59`), dan Logo (`resources/views/admin/settings/edit.blade.php:217`).
- Bukti: hanya Hero mempunyai checkbox `remove_image`/`remove_portrait`.
- Dampak: setelah media diunggah, admin hanya dapat menggantinya, bukan kembali ke fallback/kondisi kosong.
- Fix: sediakan aksi remove eksplisit dan uji penghapusan file serta fallback publik.

## Temuan Operasional dan Data

### ADM-09 — Medium — GET Agenda melakukan penghapusan permanen

- Lokasi: `app/Http/Controllers/Admin/AgendaController.php:16-22`, `app/Support/AgendaPurger.php:17-29`.
- Bukti: membuka index Agenda memanggil purge yang menghapus record selesai dan gambarnya.
- Dampak: GET tidak lagi read-only; prefetch/prerender admin dapat memicu penghapusan tanpa kunjungan sadar. Riwayat agenda selesai juga hilang permanen.
- Fix: jalankan purge hanya dari scheduler/command; jika fallback manual dibutuhkan, jadikan POST berkonfirmasi. Alternatif produk adalah archive/soft delete.
- False-positive note: perilaku ini sengaja dibuat sebagai fallback ketika cron tidak aktif, tetapi interaksinya dengan prefetch membuatnya tetap berisiko.

### ADM-10 — Medium/Low — Perubahan password tidak mengakhiri sesi admin lain

- Lokasi: `app/Http/Controllers/Admin/AccountController.php:20-60`, route hanya memakai `auth:admin` tanpa session-auth middleware tambahan.
- Bukti: sesi saat ini diregenerasi, tetapi tidak ada invalidasi sesi lain setelah password berubah.
- Dampak: sesi yang sebelumnya dicuri atau sesi pada perangkat lama dapat tetap aktif.
- Fix: terapkan revocation versi sesi/password atau invalidasi seluruh sesi admin lain setelah perubahan credential.

### ADM-11 — Low — Dashboard dapat menghitung agenda kedaluwarsa sebagai aktif

- Lokasi: `app/Http/Controllers/Admin/DashboardController.php:36,65`.
- Bukti: dashboard hanya memeriksa `is_active`, sedangkan public menggunakan scope waktu selesai dan purge baru dijalankan scheduler/index Agenda.
- Dampak: angka “Agenda aktif” dapat lebih besar dari agenda yang benar-benar masih berlaku.
- Fix: gunakan scope `upcoming()` yang sama atau agregasi status yang eksplisit.

### ADM-12 — Low — Validasi filter dan display order tidak konsisten

- Lokasi: `BusinessApplicationController.php:14-26`, `ReportController.php:152-160`, `ServiceController.php:47-65`, `FaqController.php:44-53`, `DisplayOrder.php:15-53`.
- Bukti:
  - Filter laporan divalidasi, filter daftar pengajuan hanya mengambil query mentah.
  - Service/FAQ menerima order 0 dan duplicate; modul lain memakai posisi minimal 1 dan normalisasi transactional.
  - Icon Service menerima string bebas, sedangkan Advantage memakai allowlist.
- Dampak: hasil filter/order dapat tidak konsisten dan data invalid dapat masuk melalui crafted admin request.
- Fix: gunakan Form Request/filter rules bersama dan satu strategi DisplayOrder untuk semua modul terurut.

## Accessibility Admin

### ADM-A11Y-01 — Medium/Low — Delete modal belum mengelola fokus

- Lokasi: `resources/views/layouts/admin.blade.php:74-79,284-298`.
- Bukti: modal mempunyai role/aria-modal dan mendukung Escape, tetapi tidak memindahkan fokus awal, tidak melakukan focus trap, tidak membuat background inert, dan tidak mengembalikan fokus ke tombol pemicu.
- Dampak: pengguna keyboard/screen reader dapat kehilangan konteks atau berinteraksi dengan UI di belakang modal.
- Fix: simpan trigger, fokuskan tombol Batal/Hapus, trap Tab, set inert, dan restore focus saat ditutup.

## Kontrol yang Sudah Baik

- 82/82 route admin non-login memakai `auth:admin`; dua route login memakai `guest:admin`.
- Seluruh file form POST admin yang ditemukan memiliki `@csrf`.
- Login memakai error generik, menolak admin nonaktif, meregenerasi session ID, dan tidak menyediakan persistent remember session.
- Logout menghapus guard, menginvalidasi sesi, dan meregenerasi CSRF token.
- Model Admin menyembunyikan password/token dan memakai cast `hashed`.
- Perubahan akun meminta email/password lama dan password baru minimal 10 karakter, mixed case, dan angka.
- Route-model binding berada di balik guard admin; tidak ditemukan IDOR publik pada CRUD.
- Business Application menggunakan transaksi ketika membuat/memperbarui data dan histori status.
- Kategori yang masih dipakai tidak dapat dihapus; kategori nonaktif tetap dipertahankan pada pengajuan lama.
- Upload raster dibatasi tipe dan ukuran serta disimpan dengan nama acak dari storage.
- File report disimpan pada private disk, bukan public disk.
- CSV menetralkan cell yang diawali `=`, `+`, `-`, atau `@`.
- Payload JSON analytics memakai seluruh flag `JSON_HEX_*` dan diparse dari `textContent`.
- Tidak ditemukan raw SQL yang menggabungkan input admin tanpa binding.
- Route dashboard dan sidebar saat ini sinkron: masing-masing memuat 17 route modul yang sama.
- Public storage runtime tidak memiliki orphan upload: logo CMS dan portrait Hero yang tersimpan mempunyai file fisik; Client/Testimonial memakai aset statis yang valid.

## Cakupan Test

- 67 test admin/public terkait dijalankan pada sesi ini: 67 lulus, 480 assertion.
- Full baseline sesi 1: 80 test lulus, 539 assertion, termasuk test export.
- Modul dengan cakupan baik: Hero, Profil, Layanan, Artikel, Agenda, Keunggulan, Pengajuan/Kategori, laporan dasar, analytics, akun admin.
- Gap test penting:
  - login throttling;
  - prefetch/double-request export;
  - collision path export;
  - rollback upload saat storage/DB gagal;
  - invalid file type/oversized/dimensions untuk seluruh modul upload;
  - guest access untuk seluruh route mutasi, bukan sampel;
  - penghapusan media non-Hero;
  - invalidasi sesi lain setelah password berubah;
  - focus behavior modal/drawer.

## Keterbatasan dan Pekerjaan Tersisa

- Browser visual admin belum tersedia; spacing, overflow, drawer, modal, dan responsive belum diklaim valid.
- Pengujian keamanan adversarial dan constraint/index database dilanjutkan sesi 4.
- File orphan private report hanya diinventarisasi, tidak dihapus.
- Tidak ada percobaan login terhadap credential pengguna dan tidak ada CRUD pada database runtime.
