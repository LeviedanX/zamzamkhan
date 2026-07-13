# CHANGELOG — Finalisasi CMS PT Zam Zam Khan

## [Penghapusan Permanen Modul Deprecated] — 2026-07-13

### Removed
- Modul Alur Pendampingan, Galeri, dan Pesan Masuk dihapus dari sidebar, dashboard, route, controller, model, view, provider, seeder, serta stylesheet khusus admin.
- Endpoint form inbox publik `POST /kontak` beserta request validation dan aturan pesannya dihapus; konsultasi publik tetap melalui formulir WhatsApp.
- Tabel `process_steps`, `galleries`, dan `messages` beserta seluruh datanya dihapus melalui migration final.
- Konfigurasi fallback Alur/Galeri dan test CRUD lama dihapus agar modul tidak dapat hidup kembali secara tidak sengaja.

### Preserved
- Agenda, Maps, Testimoni, Layanan, FAQ, Artikel, Analitik, Data Pengajuan, dan fitur aktif lain tidak diubah.
- Enam berkas gambar dokumentasi tetap dipertahankan karena juga digunakan oleh modul Testimoni.

## [Penyederhanaan Homepage] — 2026-07-13

### Changed
- Section publik Alur Pendampingan dan Galeri dihapus dari homepage beserta tautan navigasinya.
- Form publik “Pesan Masuk/Kirim pesan kepada tim kami” dihapus; konsultasi publik tetap melalui WhatsApp.
- Pada tahap ini data dan CRUD admin sempat dipertahankan; keputusan tersebut kemudian digantikan oleh penghapusan permanen di atas.

## [Pemulihan Fitur Lengkap & Penyederhanaan Analitik] — 2026-07-13

### Restored
- CRUD Alur Pendampingan, Galeri, dan Pesan Masuk beserta route, model, controller, view admin, sidebar, dan dashboard.
- Section publik Alur dan Galeri, enam gambar dokumentasi aktif, serta form pesan publik yang tersambung ke inbox admin.
- Data awal Alur dan Galeri melalui migration non-destruktif; data admin yang sudah ada tidak ditimpa.

### Changed
- Istilah analitik dibuat lebih mudah dipahami: `Pengunjung unik` menjadi `Sesi pengunjung`, `Tayangan` menjadi `Halaman dibuka`, dan statistik perangkat dijelaskan sebagai pembukaan halaman menurut perangkat.
- Ditambahkan penjelasan bahwa satu orang dapat dihitung lagi jika memakai sesi, browser, atau perangkat berbeda.

### Security
- Form pesan dibatasi lima kiriman per menit, memakai validasi server, CSRF, penyaring kata, dan honeypot spam.
- Penghapusan otomatis Agenda tetap dinonaktifkan agar histori konten tidak hilang.

## [Perbaikan Live Maps & Kredensial Admin] — 2026-07-13

### Fixed
- **Peta lokasi tidak muncul.** Google kini me-redirect (301) URL embed gaya lama
  `https://www.google.com/maps?q=...&output=embed` ke `/maps/embed`, dan respons redirect
  tersebut membawa header `X-Frame-Options: SAMEORIGIN` sehingga browser memblokir iframe.
  Endpoint `/maps/embed` sendiri tidak memakai header itu.
- `SafeUrl::googleMapsEmbed()` menormalkan URL peta ke endpoint embed final sehingga tidak
  lagi melewati redirect. URL hasil tombol **Bagikan → Sematkan peta** dipakai apa adanya,
  tautan Google Maps biasa dikonversi otomatis, dan host non-Google tetap ditolak.
- Bila URL embed diisi tetapi tidak dapat diurai (mis. shortlink `maps.app.goo.gl`), peta tetap
  tampil memakai alamat perusahaan. Field yang dikosongkan tetap menyembunyikan peta.

### Changed
- Kredensial admin lokal diperbarui sesuai nilai yang diberikan pengguna tanpa menyimpan
  password ke source. `ADMIN_EMAIL`/`ADMIN_PASSWORD` tetap dibaca dari environment oleh `AdminSeeder`.
- Bantuan field "URL embed Google Maps" di panel admin diperjelas.

### Validation
- PHPUnit: 103 test lulus, 709 assertion.
- Login admin via HTTP dengan kredensial baru berhasil dan dashboard merespons 200.
- Homepage merender iframe ke endpoint embed final; tidak ada lagi `output=embed`.

## [Perbaikan Error 500 Homepage & Pemulihan Status Final] — 2026-07-13

### Fixed (kritis)
- **Homepage error 500 (`foreach() argument must be of type array|object, null given`).**
  Modul ghost (Alur Pendampingan, Galeri, Pesan Masuk) ternyata kembali aktif di kode,
  sedangkan cache konten publik dibangun oleh versi kode yang sudah membuang modul tersebut.
  Akibatnya `partials/alur.blade.php` melakukan `foreach` pada `config('company.process')`
  yang bernilai `null`. Status final sesuai CHANGELOG 2026-07-12 dipulihkan.
- **Cache konten dinaikkan ke `site_content_v4`.** Payload cache lama yang bentuknya berbeda
  tidak dapat dipakai ulang dan memicu error yang sama.

### Removed (memulihkan keputusan final 2026-07-12)
- Section `partials.alur` dan `partials.galeri` dicabut kembali dari homepage. Keduanya hanya
  menampilkan data statis `config/company.php` (tabel `process_steps` dan `galleries` kosong),
  sehingga tidak dapat dikendalikan admin. Dokumentasi tetap diwakili Testimoni (11 item aktif).
- Menu sidebar dan kartu dashboard Alur Pendampingan, Galeri, serta Pesan Masuk dicabut kembali.
- Route `admin.process-steps.*`, `admin.galleries.*`, `admin.messages.*`, dan `contact.store`
  dicabut kembali. Formulir Pesan Masuk pada section Kontak dihapus (website tetap WhatsApp-first).
- Anchor navigasi `#alur` dan `#galeri` dihapus dari `config/company.php` agar tidak ada tautan mati.

### Catatan data lama
Model, controller, view admin, migration, dan tabel modul deprecated **tidak dihapus** agar
rollback tetap aman. Data statis `process`/`gallery` pada `config/company.php` juga dipertahankan
dan ditandai DEPRECATED, tetapi tidak lagi dibaca oleh `AppServiceProvider`.

### Hardening
- Nomor WhatsApp pada `partials/kontak.blade.php` dan `partials/whatsapp-lead-form.blade.php`
  aman terhadap nilai `null` (admin mengosongkan field), tidak lagi memicu deprecation PHP 8.4.

### Validation
- PHPUnit: 103 test lulus, 707 assertion.
- Homepage, artikel, sitemap, robots, dan login admin merespons HTTP 200.
- Seluruh halaman GET admin merender tanpa error untuk admin yang login.
- Route modul deprecated merespons 404, bukan 500.
- Seluruh anchor navbar merujuk ke section yang benar-benar dirender.

## [Pemulihan Section Publik] — 2026-07-13

### Restored
- Section Tentang dan Visi–Misi dikembalikan dengan konten resmi dari referensi project.
- Section dan navigasi Agenda tetap tampil ketika belum ada jadwal, menggunakan empty state yang jelas.
- URL Maps dan embed lokasi dikembalikan melalui sumber data CMS.

### Data Safety
- Penghapusan otomatis agenda selesai setiap 15 menit dihentikan. Agenda selesai tetap tersimpan
  sebagai histori admin dan hanya disaring dari homepage berdasarkan waktu selesai.
- Migration pemulihan hanya mengisi field profil yang masih `null` dan tidak menimpa perubahan admin.

## [Pembersihan Fitur Ghost Admin] — 2026-07-12

### Removed
- Route, controller, model, view admin, dan aset frontend aktif untuk Galeri, Alur Pendampingan,
  serta Pesan Masuk dicabut. Tabel lama tetap dipertahankan agar data historis tidak hilang.
- Form pesan publik yang sebelumnya hanya disembunyikan dengan `@if(false)` beserta route,
  validator, JavaScript, dan CSS terkait dihapus; konsultasi publik tetap WhatsApp-first.
- Field CTA utama dan toggle aktif Hero dihapus karena tidak mempunyai keluaran publik.
- Field `brand_name` dan deskripsi panjang Agenda dihapus dari editor karena redundan.

### Fixed
- Deskripsi Statistik kini tampil di homepage.
- Industri dan tautan website Klien kini tampil/berfungsi pada kartu logo.
- Alt gambar Testimoni kini memakai nilai dari admin.
- Waktu selesai Agenda kini tampil sebagai rentang waktu publik.
- Seluruh tautan sosial dari Profil & Identitas kini tampil di footer, tidak hanya Instagram.
- Hero selalu disimpan sebagai hero aktif sehingga setiap perubahan editor langsung digunakan homepage.

### Audit
- Seluruh modul pada kategori Konten Website mempunyai representasi publik yang nyata.
- Modul Operasional Internal dan Pengaturan tetap dipertahankan karena mempunyai fungsi internal
  eksplisit, bukan editor konten publik.

## [Analitik Pengunjung & Keamanan Akun Admin] — 2026-07-12

### Added
- **Analitik Pengunjung** pada kategori Operasional Internal dengan filter hari, minggu, bulan,
  tahun, dan keseluruhan; metrik tayangan, pengunjung unik, halaman per pengunjung, halaman
  teratas, sumber trafik, dan komposisi perangkat.
- Visualisasi tren tersedia dalam grafik garis, batang, dan area; komposisi perangkat memakai
  doughnut chart, serta tersedia tabel data sebagai representasi aksesibel.
- Pencatatan first-party untuk halaman publik dengan identifier sesi anonim yang di-hash.
  Alamat IP mentah, bot, halaman admin, sitemap, dan robots tidak dicatat.
- **Akun Admin** pada kategori Pengaturan untuk mengganti email login dan/atau password.

### Security
- Email baru wajib memakai domain `@gmail.com` dan tetap unik pada tabel admin.
- Perubahan kredensial wajib memverifikasi email serta password akun lama; password baru
  minimal 10 karakter, memuat huruf besar-kecil dan angka, lalu disimpan sebagai hash.
- Sesi diregenerasi setelah perubahan kredensial berhasil.
- Kegagalan penyimpanan telemetri tidak dapat menjatuhkan website publik.

### Validation
- Migration `web_visits` diterapkan tanpa menghapus tabel atau data lama.
- PHPUnit: 39 test lulus, 231 assertion.
- Vite production build berhasil; browser desktop/mobile tidak mengalami overflow dan tidak
  menghasilkan error JavaScript pada halaman analitik maupun akun admin.

## [Finalisasi] — 2026-07-09

### Fixed (kritis)
- **WhatsApp single source of truth.** `AppServiceProvider` kini mengisi `company.whatsapp_number`
  (dan `company.whatsapp`) dari nomor WhatsApp admin, dinormalisasi ke format internasional
  (`08…`/`8…` → `62…`). Sebelumnya modal konsultasi & seluruh CTA membaca nomor statis dari
  `config/company.php`. Sekarang mengubah nomor di admin langsung mengubah semua CTA publik.

### Changed
- **Logo dinamis.** Navbar & footer memakai logo dari admin (`SiteSetting.logo_path`) dengan
  fallback ke `images/logo-zzk.webp`.
- **Layanan unggulan.** `Service.is_featured` kini berefek: layanan unggulan tampil lebih dulu
  dan mendapat badge "Unggulan" di card.
- **Pesan layanan.** `Service.whatsapp_message` kini berefek: mengisi otomatis kolom "Kebutuhan"
  pada form konsultasi saat tombol Konsultasikan pada layanan ditekan. Label field admin
  diperjelas menjadi "Kebutuhan awal (prefill konsultasi)".
- **Fallback statis diperbaiki.** Section Layanan & FAQ tidak lagi memakai data statis
  `config/company.php` bila admin sudah pernah mengelola modul tersebut (tabel berisi data).
  Bila semua item dinonaktifkan → section otomatis disembunyikan, bukan menampilkan data lama.
  Fallback config hanya berlaku pada instalasi awal (tabel kosong).

### Deprecated (awalnya disembunyikan; route/UI aktif kemudian dicabut pada 2026-07-12)
- **Pesan Masuk** — website mengarahkan pengunjung ke WhatsApp (WhatsApp-first); form publik
  memang dinonaktifkan (`@if(false)`). Dihapus dari sidebar, dashboard, dan navigasi antar-modul.
- **Alur Pendampingan** — sudah digantikan section Visi–Misi dan tidak dirender di homepage.
  Dihapus dari sidebar, dashboard, dan navigasi antar-modul.
- **Galeri Dokumentasi** — data kosong dan redundan dengan Testimoni. Dihapus dari sidebar,
  dashboard, navigasi antar-modul, dan tidak lagi di-include di homepage.
- **Field "Nama brand" (`brand_name`)** — disembunyikan dari form Profil karena tidak dirender
  di publik; teks brand memakai "Nama perusahaan" (`company_name`).
- **Field "URL tombol utama" hero (`primary_button_url`)** — dihapus dari form karena tombol
  utama hero selalu membuka form konsultasi WhatsApp.

### Catatan data lama
Migration dan tabel deprecated tidak dihapus agar deployment tetap non-destruktif. Modul tersebut
tidak lagi mempunyai route atau UI aktif.

## [Integrasi fitur Web1] — 2026-07-09

### Added
- Kategori Artikel dan Kategori Bisnis dengan proteksi penghapusan ketika masih digunakan.
- Agenda, Keunggulan, Statistik, Klien, dan Testimoni beserta CRUD admin dan kontrol status aktif.
- Data Pengajuan dengan relasi kategori, histori perubahan status, audit pembuat/pengubah, filter, dan pagination.
- Laporan berbasis data pengajuan nyata dengan filter, ringkasan, export CSV ber-BOM agar aman dibuka di Excel, histori export, dan tampilan cetak/simpan PDF.
- Migration non-destruktif untuk tabel baru serta data awal konten publik agar homepage tidak kosong sesudah migration.

### Integration
- Keunggulan, Statistik, Klien, dan Testimoni kini membaca database melalui `AppServiceProvider`.
- Jika seluruh item dinonaktifkan admin, section publik terkait disembunyikan dan tidak kembali ke data statis.
- Agenda tetap admin-only karena belum ada kebutuhan produk yang membenarkan section homepage baru.
- CTA WhatsApp artikel tidak lagi memakai nomor hardcoded dan membaca `company.whatsapp_number`.

## [Hotfix integrasi runtime] — 2026-07-09

### Fixed
- Kontrak data fallback Keunggulan, Statistik, Klien, dan Testimoni dinormalisasi sebelum dikirim ke Blade.
- Partial homepage memfilter payload malformed sehingga string atau key lama tidak dapat memicu `TypeError`.
- Cache konten dinaikkan ke `site_content_v2` agar payload versi lama tidak digunakan kembali.
- Nama foreign key Data Pengajuan dipendekkan agar kompatibel dengan batas identifier MySQL.
- Migration fitur baru berhasil dijalankan dan seluruh cache Laravel dibersihkan.

### Validation
- Homepage, artikel, sitemap, robots, dan login admin merespons HTTP 200.
- Snapshot browser nyata berhasil merender seluruh section homepage.
- PHPUnit: 29 test lulus, 114 assertion.

## [Penyederhanaan Admin & Sinkronisasi Agenda] — 2026-07-09

### Changed
- Menu admin dikelompokkan menjadi `Konten Website`, `Operasional`, dan `Pengaturan` dalam panel collapsible.
- Dashboard mempertahankan kategori modul dan tidak lagi meratakan seluruh fitur menjadi satu daftar panjang.
- Navigasi antar-modul disederhanakan menjadi Dashboard, kategori aktif, dan tombol modul berikutnya; tautan publik tetap tersedia di halaman modul yang membutuhkannya.
- View admin baru memakai token permukaan, border, teks, input, tabel, dan muted text yang konsisten pada light/dark mode.

### Added
- Agenda aktif yang belum lewat dikirim dari database ke homepage melalui cache konten publik.
- Section Agenda, anchor navbar/footer, kartu agenda, CTA pendaftaran/WhatsApp, dan empty state publik.
- Cache Agenda otomatis dibersihkan ketika data disimpan atau dihapus dari panel admin.

### Validation
- Build Vite production berhasil.
- PHPUnit: 30 test lulus, 124 assertion.
- Browser smoke test: Agenda tampil, dark mode aktif, tidak ada horizontal overflow, dan tidak ada broken image.
