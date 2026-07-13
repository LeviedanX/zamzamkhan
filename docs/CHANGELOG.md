# CHANGELOG — Admin CMS & Public Integration

## 2026-07-06 — Site Settings sebagai control center + polish admin

### Ditambahkan
- Migration additive `add_profile_fields_to_site_settings`: kolom nullable `consultant_name`,
  `company_description`, `vision`, `mission`, `operating_hours`, `maps_url`, `maps_embed_url`.
- `App\Http\Requests\Admin\UpdateSiteSettingRequest` (validasi terstruktur Site Settings).
- Dashboard admin baru: welcome hero premium gelap + statistik (artikel total/terbit/draft, layanan
  aktif, FAQ aktif, galeri aktif, pesan baru) + feature card klik-penuh berkelompok
  (Konten Website / Interaksi / Pengaturan).
- Navigasi admin dikelompokkan dengan pemisah visual + label grup.
- Jam operasional pada section Kontak & Footer (tampil bila diisi).
- `tests/Feature/SiteSettingTest.php` (auth, resiliensi homepage, persistensi field, artikel draft,
  form kontak).
- `docs/RUN_STATE.md`, `docs/TODO.md`, `docs/CHANGELOG.md`.

### Diubah
- `AppServiceProvider::buildSiteContent()` mem-bridge field profil baru ke `config('company')`.
- `SiteSettingController::update()` memakai `UpdateSiteSettingRequest`.
- `resources/views/admin/settings/edit.blade.php` dirombak menjadi 5 section berkelompok + field baru.
- Partial public wiring + fallback: `navbar`, `hero` (caption konsultan), `tentang` (deskripsi),
  `visi-misi` (visi & misi), `kontak` & `footer` (jam operasional), footer brand dari config.
- `DashboardController` menambah statistik artikel/aktif.

### Tidak diubah (dijaga)
- Status artikel tetap `draft`/`published`. Skema `articles` (cover_image, cover_alt,
  article_category_id) tetap.
- Form kontak tetap ke `Message`/`messages` (status `new`).
- Profil/kontak tetap di `SiteSetting`/`site_settings` (tanpa tabel `company_profiles` /
  `contact_settings` / `contact_messages`).
- Mekanisme upload (disk `public`, hapus file lama) tidak diubah — sudah benar.
- Urutan navbar public, komposisi visual hero/kontak/footer tidak diredesain.

## 2026-07-06 - Patch kecil Alur homepage + Hero image CMS

### Ditambahkan
- Section Alur Pendampingan kini dipanggil dari homepage setelah Layanan.
- `hero.image_url` pada bridge public untuk memakai `HeroSection.image_path` dari storage public.
- Layer background hero opsional dari upload admin dengan overlay gelap-merah agar headline, CTA, trust chips, dan visual direktur tetap terbaca.

### Diubah
- Catatan form admin Hero menjelaskan perilaku fallback gambar: upload menjadi layer tambahan; jika kosong, hero bawaan tetap dipakai.
- Dokumentasi RUN_STATE/TODO/CHANGELOG mencatat file yang berubah dan batasan yang belum dikerjakan.

### Tidak diubah
- Tidak ada migration, schema, package, artikel, layanan, FAQ, galeri, pesan masuk, SEO, auth, dashboard, atau layout admin yang diubah.
- Belum mengerjakan metadata modal layanan, polish UX admin, mobile drawer, dan test runtime.
## 2026-07-06 - Admin visual design upgrade

### Diubah
- Admin layout mendapat visual polish terpusat agar lebih konsisten dengan public website: background grid halus, maroon glow, glass surface, shadow, hover state, dan border tone merah-hitam.
- Topbar, navigasi, drawer, dashboard hero, statistik, feature card, table, form card, gallery card, badge status, dan row action dibuat lebih premium tanpa mengubah logic CRUD.
- Spacing mobile dan surface responsif diperhalus agar admin page lebih rapi pada desktop, tablet, dan mobile.

## 2026-07-06 - Admin form dan list UI remake

### Diubah
- Form Hero, Layanan, Alur Pendampingan, FAQ, Galeri, SEO, Profil & Identitas, Artikel, dan Detail Pesan dipoles dengan card bertingkat, header modul, savebar, toggle card, preview panel, dan surface merah-hitam yang selaras dengan website utama.
- Tabel Layanan, Alur, FAQ, Artikel, dan Pesan Masuk memakai table surface global yang lebih rapi dan konsisten.
- Empty state Galeri dan panel preview media/SEO/pesan diperkuat secara visual tanpa mengubah route, controller, schema, atau workflow CRUD.

## 2026-07-06 - Admin header dan preview device

### Ditambahkan
- Toggle Light/Dark theme pada admin header dan drawer mobile dengan state tersimpan di `localStorage`.
- Preview Hero memiliki mode Handphone dan Windows yang bisa dipilih tanpa menyimpan preferensi ke database.

### Diubah
- Header admin dipoles dengan glow merah-hitam, brand mark lebih kuat, action button lebih premium, dan avatar admin lebih konsisten dengan visual website utama.
- Surface dark theme diterapkan pada navigasi, form, table, card, drawer, input, dan empty state.

## 2026-07-06 - Full Hero editor

### Ditambahkan
- Field Hero CMS untuk badge atas, trust line, chip layanan, gambar figur direktur, alt figur, role figur, dan nama figur.
- Migration additive `2026_07_06_150000_add_full_edit_fields_to_hero_sections.php` agar database existing bisa menyimpan field Hero baru.
- Preview Hero admin menampilkan figur direktur dan chip layanan, dengan transisi smooth saat berpindah mode Phone/Windows.

### Diubah
- Public Hero membaca seluruh field baru dari `HeroSection` dengan fallback visual dan teks lama bila field kosong.

## 2026-07-06 - Admin CMS workspace polish

### Ditambahkan
- Tombol `Buka Website Publik` pada topbar admin.
- Drawer mobile/tablet untuk navigasi admin dengan close button, overlay click, Escape, dan `aria-expanded`.
- Modal konfirmasi hapus reusable berbasis Alpine untuk aksi delete tanpa `window.confirm`.
- Konteks operasional pada feature card dashboard: status Hero, Profil, Layanan, Alur, Artikel, FAQ, Galeri, Pesan, dan SEO.

### Diubah
- Dashboard admin dipoles sebagai control center dengan greeting waktu lokal, tanggal Indonesia, statistik responsif, dan card klik-penuh.
- Istilah admin diseragamkan: Hero Utama, Profil & Identitas, Alur Pendampingan, Artikel & Insight, Galeri Dokumentasi, Pesan Masuk, SEO Website.
- List admin mendapat header deskriptif, empty state lebih informatif, status `published` ditampilkan sebagai `Terbit`, dan action delete lebih aman.
- Detail modal Layanan public memprioritaskan deskripsi dari database jika tersedia, dengan fallback mapping ikon existing.
- Action bar Profil tidak lagi sticky agar tidak menutupi field saat scroll.

### Validasi integrasi
- Cache content public tetap terpusat pada `site_content_v1` dan invalidasi otomatis masih melalui observer model konten di `AppServiceProvider`.
- Public tetap memakai `is_active` dan `display_order` untuk Layanan, Alur, FAQ, dan Galeri.

### Belum selesai
- FormRequest khusus untuk Hero/Service/FAQ/Gallery/SEO belum dibuat.
- `php artisan test` dan `npm run build` belum berhasil dijalankan karena binary `php` dan `npm` tidak tersedia di PATH environment ini.
- Test runtime browser untuk drawer/modal/responsive belum dilakukan.
