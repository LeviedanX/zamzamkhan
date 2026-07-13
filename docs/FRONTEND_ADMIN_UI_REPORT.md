# Frontend Admin UI Report

## 1. Ringkasan pekerjaan

Tampilan admin `zzk-final` diperbarui dengan acuan desain admin dari `zzk5`: warna utama maroon/dark red, hitam, putih, abu lembut, card bersih, border halus, shadow ringan, topbar compact, drawer kanan overlay, badge status, tombol aksi, modal hapus, flash message, validation summary, dan empty state reusable.

Perubahan dibatasi pada frontend/admin UI. Tampilan publik tetap memakai struktur dan stylesheet existing.

`zzk5` dipakai sebagai acuan penuh UI/UX admin. Admin lama `zzk-web` tidak dipakai sebagai referensi visual; route, controller, auth, binding form, dan data backend lama tetap dipertahankan.

## 2. File pada zzk-final yang diubah

- `resources/views/layouts/admin.blade.php`
- `resources/views/admin/auth/login.blade.php`
- `resources/views/admin/dashboard.blade.php`
- `resources/views/admin/articles/index.blade.php`
- `resources/views/admin/faqs/index.blade.php`
- `resources/views/admin/galleries/index.blade.php`
- `resources/views/admin/messages/index.blade.php`
- `resources/views/admin/process-steps/index.blade.php`
- `resources/views/admin/services/index.blade.php`
- `resources/views/admin/partials/row-actions.blade.php`
- `resources/views/admin/partials/status-badge.blade.php`
- `resources/views/admin/partials/flash.blade.php`
- `resources/views/admin/partials/validation-errors.blade.php`
- `resources/views/admin/partials/empty-state.blade.php`
- `resources/css/app.css`
- `public/build/manifest.json`
- `public/build/assets/app-CLLVdn-h.css`

## 3. Referensi zzk5 yang digunakan

- `zzk5/resources/views/admin/layouts/app.blade.php`
- `zzk5/resources/views/admin/components/topbar.blade.php`
- `zzk5/resources/views/admin/components/drawer.blade.php`
- `zzk5/resources/views/admin/components/page-header.blade.php`
- `zzk5/resources/views/admin/components/flash.blade.php`
- `zzk5/resources/views/admin/components/errors.blade.php`
- `zzk5/resources/views/admin/components/empty-state.blade.php`
- `zzk5/resources/views/admin/components/delete-modal.blade.php`
- `zzk5/resources/views/admin/components/stat-card.blade.php`
- `zzk5/resources/views/admin/dashboard/index.blade.php`
- `zzk5/resources/css/app.css`

Referensi dipakai sebagai arahan visual, bukan sebagai project utama dan bukan disalin mentah.

## 4. Halaman admin yang diperbarui

- Dashboard melalui layout/sidebar/topbar dan design system global admin.
- Login Admin dengan light card clean seperti `zzk5`.
- Hero melalui layout, button, form card, modal, dan scoped admin CSS.
- Layanan index dan form styling inherited.
- Alur Pendampingan index dan form styling inherited.
- FAQ index dan form styling inherited.
- Galeri index dan form styling inherited.
- Artikel index dan form styling inherited.
- SEO styling inherited.
- Pengaturan Situs styling inherited.
- Pesan Masuk index dan detail styling inherited.

## 4A. Peta Menu ke Route Existing

- UTAMA / Dashboard: `admin.dashboard`
- KONTEN WEBSITE / Hero Utama: `admin.hero.edit`
- KONTEN WEBSITE / Profil & Identitas: `admin.settings.edit`
- KONTEN WEBSITE / Layanan: `admin.services.index`
- KONTEN WEBSITE / Alur Pendampingan: `admin.process-steps.index`
- KONTEN WEBSITE / Artikel & Insight: `admin.articles.index`
- KONTEN WEBSITE / FAQ: `admin.faqs.index`
- KONTEN WEBSITE / Galeri Dokumentasi: `admin.galleries.index`
- INTERAKSI / Pesan Masuk: `admin.messages.index`
- PENGATURAN / SEO Website: `admin.seo.edit`

## 4B. Menu/Fitur Tidak Ditampilkan

- Menu `Informasi Kontak` dari `zzk5` tidak ditampilkan sebagai menu terpisah karena route backend final tidak tersedia. Data kontak tetap berada di `Profil & Identitas` sesuai route existing.
- Menu `Company Profile` dan `SEO Settings` dari `zzk5` dipetakan ke route final yang setara: `admin.settings.edit` dan `admin.seo.edit`.
- Kategori Artikel tidak dibuat sebagai halaman/menu terpisah karena tidak ada route/controller admin kategori artikel pada project final.

## 5. Komponen UI reusable yang dibuat

- `admin.partials.flash` untuk alert sukses, warning, dan error.
- `admin.partials.validation-errors` untuk summary validasi.
- `admin.partials.empty-state` untuk empty state profesional.
- `admin.partials.row-actions` untuk tombol edit/hapus.
- `admin.partials.status-badge` untuk status aktif/nonaktif.
- Modal konfirmasi hapus reusable berada di `layouts/admin.blade.php` dan tetap memakai event `open-delete-modal` existing.

## 6. Isolasi CSS admin

Design system baru dibatasi dengan root `.admin-shell`. Selector generik seperti input, textarea, select, tabel, tombol, alert, sidebar, topbar, badge, empty state, dan modal dipasang di bawah `.admin-shell` atau class admin spesifik.

Public website tidak diberi selector global baru. Styling publik yang sudah ada tetap berada pada aturan existing.

## 7. Bagian yang butuh backend/database

- Dashboard hanya memakai statistik dan kelompok modul yang sudah tersedia dari controller existing.
- Empty state ditampilkan jika koleksi data kosong; tidak ada data dummy.
- Ringkasan aktivitas lanjutan tidak dibuat karena tidak ada sumber data aktivitas khusus dari backend.
- Halaman admin Kategori Artikel terpisah tidak ditemukan pada route/view existing. UI kategori yang tersedia tetap dipertahankan pada form Artikel melalui select kategori existing, tanpa membuat route/controller baru.

## 8. Area yang sengaja tidak diubah

- Database.
- Migration.
- Seeder.
- Factory.
- Model.
- Controller.
- Form Request.
- Route.
- Middleware.
- Auth guard.
- Login logic.
- Query database.
- Relasi database.
- Upload logic.
- Backend CRUD.
- `.env`.
- `config/`.
- `composer.json`.
- `package.json`.
- Backend public website.

## 9. Catatan responsive design

- Tidak ada sidebar kiri permanen pada layout aktif.
- Topbar putih compact selalu berada di atas halaman.
- Tombol hamburger tiga garis berada di sisi kiri topbar, sebelum judul halaman aktif, pada desktop, tablet, dan mobile.
- Drawer navigasi muncul dari kanan sebagai overlay pada semua ukuran layar.
- Drawer memakai `width: min(88vw, 26rem)` agar proporsional pada 360px, 390px, tablet, dan desktop.
- Konten admin memakai container full-width terkontrol seperti `zzk5`, tanpa ruang kosong bekas sidebar.
- Tabel tetap memakai horizontal scroll wrapper.
- Modal hapus dibatasi `max-width` dan padding layar kecil.

## 10. Known issue visual tersisa

- Beberapa form kompleks seperti Hero, Profil/Situs, dan Layanan masih mempertahankan preview custom existing. Styling sudah diselaraskan lewat CSS admin global, tetapi detail micro-layout preview mengikuti implementasi awal agar tidak mengganggu binding dan Alpine state existing.
- Ada warning PHP lokal saat menjalankan Artisan karena beberapa ekstensi PHP ter-load ganda. Ini bukan perubahan project dan tidak memengaruhi Blade/Vite build.

## Admin Navigation Refactor: zzk5 Right Drawer Pattern

### File yang diubah

- `resources/views/layouts/admin.blade.php`
- `resources/css/app.css`
- `public/build/manifest.json`
- `public/build/assets/app-CLLVdn-h.css`

### Sidebar kiri lama yang dihapus

Layout aktif tidak lagi merender sidebar kiri permanen, grid kiri `18rem`, tombol collapse kiri, atau drawer kiri. Admin sekarang memakai topbar full-width dan right drawer overlay seperti `zzk5`.

### Struktur topbar baru

- Kiri: logo PT Zam Zam Khan, teks brand singkat, hamburger tiga garis, dan judul halaman aktif.
- Kanan: toggle tema, identitas admin, dan tombol logout existing.
- Hamburger selalu terlihat dan membuka drawer kanan.

### Cara drawer kanan bekerja

- State Alpine yang digunakan: `menuOpen`.
- Saat `menuOpen = true`, overlay gelap transparan muncul dan drawer slide masuk dari kanan.
- Tombol `X` di header drawer menjalankan `menuOpen = false`.
- Klik overlay menjalankan `menuOpen = false`.
- Escape menjalankan `menuOpen = false` dan juga menutup modal hapus bila terbuka.
- Saat drawer terbuka, class `admin-body-locked` ditambahkan ke body agar halaman belakang tidak scroll.
- Saat menu dipilih, drawer ditutup melalui `@click="menuOpen = false"`.

### Breakpoint responsive

- Drawer kanan digunakan pada semua viewport, termasuk desktop.
- Lebar drawer: `min(88vw, 26rem)`.
- Pada mobile kecil, drawer tetap maksimal `88vw` dan topbar diringkas.

### Halaman admin yang memakai layout baru

- Dashboard.
- Hero Utama.
- Profil & Identitas.
- Layanan.
- Alur Pendampingan.
- Artikel & Insight.
- FAQ.
- Galeri Dokumentasi.
- Pesan Masuk.
- SEO Website.
- Semua form tambah/edit admin yang extend `layouts.admin`.

### Area backend yang tidak diubah

- Database.
- Migration.
- Seeder.
- Model.
- Controller.
- Route.
- Auth.
- Backend CRUD.
- Public website.

## Hamburger, Unified Menu Grid, and Theme Toggle Revision

### File yang diubah

- `resources/views/layouts/admin.blade.php`
- `resources/views/admin/dashboard.blade.php`
- `resources/css/app.css`
- `public/build/manifest.json`
- `public/build/assets/app-CLLVdn-h.css`
- `docs/FRONTEND_ADMIN_UI_REPORT.md`

### Tombol kosong yang diganti

Tombol bulat kosong pada topbar diganti menjadi hamburger tiga garis horizontal. Penyebab visual kosong sebelumnya adalah mismatch antara markup tiga `span` langsung dan selector CSS yang mengharapkan struktur nested. CSS tombol sekarang menargetkan tiga garis langsung pada `.admin-menu-button > span`.

### Posisi hamburger baru

Hamburger dipindahkan ke sisi kiri topbar, setelah logo/brand dan sebelum judul halaman aktif. Tombol tetap terlihat pada desktop, tablet, dan mobile, serta tetap membuka right drawer admin.

### Cara drawer dibuka dan ditutup

- State Alpine tetap memakai `menuOpen`.
- Klik hamburger menjalankan `menuOpen = true`.
- Drawer tetap slide dari kanan sesuai pola `zzk5`.
- Tombol `X`, klik overlay, tombol Escape, dan klik menu menjalankan `menuOpen = false`.
- Tidak ada sidebar kiri permanen atau area kosong bekas sidebar.

### Kategori card yang dihapus

Area card dashboard tidak lagi memisahkan `Interaksi` dan `Pengaturan`. Heading kategori khusus tersebut dihilangkan dari grid dashboard.

### Menu card yang digabung

Grid dashboard sekarang menampilkan satu kesatuan menu:

- Hero Utama.
- Profil & Identitas.
- Layanan.
- Alur Pendampingan.
- Artikel & Insight.
- FAQ.
- Galeri Dokumentasi.
- Pesan Masuk.
- SEO Website.

Semua card tetap memakai route existing dari data backend/controller yang sudah ada. Tidak ada route baru.

### Lokasi toggle tema

Toggle Light/Dark Mode diletakkan di area kanan topbar, sebelum identitas admin dan tombol logout. Icon yang dipakai adalah sun untuk light mode dan moon untuk dark mode.

### Cara localStorage dipakai

Admin mengikuti pola tema public website: key `theme` di `localStorage` berisi `dark` atau `light`. Script kecil di head membaca key tersebut sebelum paint dan menambahkan class `.dark` pada `<html>` bila perlu. Toggle memakai `$store.theme.toggle()` dari `resources/js/app.js`, sehingga pilihan tema bertahan setelah refresh.

### Cakupan light mode dan dark mode

Theme admin di-scope pada body admin melalui `data-admin-theme`. Dark mode mencakup topbar, drawer, card dashboard, form card, input, textarea, select, tabel, modal hapus, alert/flash, empty state, dan tombol. Public website tidak dipaksa berubah oleh selector admin.

### Halaman yang sudah diuji

- Dashboard admin.
- Layout admin yang dipakai semua halaman admin yang extend `layouts.admin`.
- Screenshot/check responsive dilakukan pada target 360px, 390px, 768px, 1024px, dan 1440px. Chrome headless Windows memiliki minimum CSS viewport sekitar 474px untuk request 360/390, sehingga guard CSS mobile `<=640px` tetap dipakai untuk cakupan layar kecil.

### Area backend yang tidak diubah

- Database.
- Migration.
- Seeder.
- Model.
- Controller.
- Route.
- Auth.
- Backend CRUD.
- Public website.

## Module Previous, Next, and Home Navigation

### File yang diubah

- `resources/views/layouts/admin.blade.php`
- `resources/views/admin/components/module-navigation.blade.php`
- `resources/css/app.css`
- `public/build/manifest.json`
- `public/build/assets/app-CLLVdn-h.css`
- `docs/FRONTEND_ADMIN_UI_REPORT.md`

### Urutan fitur yang dipakai

Navigasi modul memakai urutan frontend berikut:

- Dashboard.
- Hero Utama.
- Profil & Identitas.
- Layanan.
- Alur Pendampingan.
- Artikel & Insight.
- Kategori Artikel.
- FAQ.
- Galeri Dokumentasi.
- Pesan Masuk.
- SEO Website.

### Komponen reusable yang dibuat

Komponen reusable dibuat di `resources/views/admin/components/module-navigation.blade.php` dan dipanggil sekali dari `resources/views/layouts/admin.blade.php`. Karena dipanggil dari layout, navigation otomatis tampil pada dashboard, halaman index, create, edit, dan detail modul admin yang extend `layouts.admin`.

### Route existing yang dipakai

- `admin.dashboard`
- `admin.hero.edit`
- `admin.settings.edit`
- `admin.services.index`
- `admin.process-steps.index`
- `admin.articles.index`
- `admin.faqs.index`
- `admin.galleries.index`
- `admin.messages.index`
- `admin.seo.edit`

Route `admin.article-categories.index` dicek dengan `Route::has()`. Karena route kategori artikel tidak tersedia pada project final, item tersebut menjadi state disabled ketika menjadi previous/next, bukan link palsu.

### Halaman yang menggunakan navigation

- Dashboard.
- Hero Utama.
- Profil & Identitas.
- Layanan.
- Alur Pendampingan.
- Artikel & Insight.
- FAQ.
- Galeri Dokumentasi.
- Pesan Masuk.
- SEO Website.
- Form create/edit dan detail dalam modul yang memakai layout admin.

### Behavior mobile

Pada desktop, bar memakai tiga bagian: previous di kiri, Home di tengah, next di kanan. Pada mobile, Home pill naik menjadi baris utama, sedangkan previous dan next berada di baris bawah agar tidak menimbulkan horizontal overflow.

### State disabled

Jika previous/next tidak tersedia, atau route modul tidak ada, komponen menampilkan state disabled visual dengan `aria-disabled="true"` dan tanpa link.

### Lokasi shortcut Home square

Shortcut Home berbentuk rounded square maroon kini menjadi floating action button fixed di kanan bawah halaman admin. Shortcut ini memakai route `admin.dashboard`, icon home SVG putih, tooltip `Kembali ke Beranda Admin`, safe spacing mobile, dan z-index di bawah modal.

### Area backend yang tidak diubah

- Database.
- Migration.
- Seeder.
- Model.
- Controller.
- Route.
- Auth.
- Backend CRUD.
- Public website.

## Floating Home Action and Dark Mode Hardening

### File yang diubah

- `resources/views/layouts/admin.blade.php`
- `resources/views/admin/components/module-navigation.blade.php`
- `resources/css/app.css`
- `public/build/manifest.json`
- `public/build/assets/app-CLLVdn-h.css`
- `docs/FRONTEND_ADMIN_UI_REPORT.md`

### Floating Home icon

Shortcut Home lama yang berbentuk card di area konten dihapus dari `module-navigation.blade.php`. Penggantinya adalah satu floating action button fixed di kanan bawah layout admin, dibuat di `resources/views/layouts/admin.blade.php`.

Behavior floating Home:

- Mengarah ke `admin.dashboard`.
- Hanya berisi icon Home SVG putih, tanpa teks visible.
- Background maroon/dark red dengan rounded-square dan shadow halus.
- Memiliki hover, focus state, `aria-label`, `title`, dan tooltip CSS.
- Z-index berada di bawah modal hapus, sehingga tidak tampil di atas backdrop/modal.
- Mobile memakai safe spacing dari tepi layar.

### Theme approach

Admin tetap memakai store tema public yang sudah ada: `$store.theme` dan localStorage key `theme`. Scope visual admin memakai `data-admin-theme="dark"` pada body admin, sehingga hardening dark mode tidak memaksa tampilan public website.

### Komponen dark mode yang diperbaiki

Hardening dark mode ditambahkan untuk:

- Layout utama dan topbar.
- Floating page navigation previous/Home/next.
- Drawer kanan.
- Dashboard, stat card, dan menu card.
- Form card, savebar, detail card, gallery card.
- Input, textarea, select, checkbox, disabled state, placeholder, dan focus state.
- Table, header table, row, hover row, dan pagination.
- Badge status aktif/nonaktif.
- Button primary, outline, dan danger.
- Alert sukses/warning/error, validation error, empty state.
- Modal, backdrop, tooltip, icon, border, divider, dan muted text.
- Floating Home button.

### Hasil uji responsive

Screenshot/check dilakukan pada target 360px, 390px, 768px, 1024px, dan 1440px. Chrome headless Windows memiliki minimum CSS viewport sekitar 474px untuk request 360/390, sehingga CSS mobile `<=640px` tetap dipakai untuk memastikan tidak ada horizontal overflow.

### Area backend yang tidak diubah

- Database.
- Migration.
- Seeder.
- Model.
- Controller.
- Route.
- Auth.
- Backend CRUD.
- Public website.
