# RUN_STATE — Admin CMS & Integrasi Public (zzk-web)

Status per checkpoint implementasi penyempurnaan Admin CMS + integrasi data public.

## Patch Admin CMS 2026-07-06
- Layout admin dipoles sebagai workspace operasional: topbar dark premium, tombol `Buka Website Publik`, navigasi desktop terkelompok, drawer mobile/tablet berbasis Alpine, dan modal konfirmasi hapus reusable.
- Dashboard menjadi control center: greeting waktu lokal, tanggal Indonesia, statistik ringkas, dan feature card klik-penuh dengan konteks tiap modul.
- List Layanan, Alur Pendampingan, Artikel & Insight, FAQ, Galeri Dokumentasi, dan Pesan Masuk mendapat header deskriptif, empty state lebih jelas, status label manusiawi, dan delete modal tanpa `window.confirm`.
- Integrasi public tervalidasi dari source: Hero, Profil, Layanan aktif, Alur aktif, FAQ aktif, Galeri aktif, Pesan Masuk, SEO global, dan cache invalidation `site_content_v1`.
- Detail modal Layanan kini memprioritaskan deskripsi database jika tersedia, dengan mapping ikon tetap sebagai fallback.
- Responsive/mobile drawer aktif; tombol Escape, overlay, dan tombol close menutup drawer/modal.
- Perlu manual bila gambar storage belum tampil: `php artisan storage:link`.
- Validasi command: `php artisan test` dan `npm run build` dicoba, tetapi gagal karena `php` dan `npm` tidak tersedia di PATH environment ini.
- Known issue tersisa: sebagian modul masih memakai validasi inline controller; test runtime browser untuk drawer/modal/responsive belum dilakukan.

## Patch kecil 2026-07-06
- Section Alur Pendampingan kini aktif di homepage setelah Layanan melalui `resources/views/home.blade.php`.
- Upload gambar Hero dari admin kini dipakai di public sebagai layer background opsional melalui `hero.image_url`.
- Fallback hero aman: jika `HeroSection.image_path` kosong, hero tetap memakai visual bawaan tema.
- File berubah: `app/Providers/AppServiceProvider.php`, `resources/views/home.blade.php`, `resources/views/partials/hero.blade.php`, `resources/views/admin/hero/edit.blade.php`, `docs/RUN_STATE.md`, `docs/TODO.md`, `docs/CHANGELOG.md`.
- Belum dikerjakan: metadata modal layanan, polish UX admin, mobile drawer, dan test runtime.

## Admin UI remake 2026-07-06
- Form/list admin utama kini memakai design system internal: `admin-form-card`, `admin-table-card`, `admin-side-panel`, `admin-savebar`, `admin-toggle-card`, dan `admin-page-kicker`.
- Halaman yang dipoles: Hero, Profil & Identitas, Layanan, Alur Pendampingan, Artikel, FAQ, Galeri, Pesan Masuk/Detail, dan SEO.
- Tidak ada perubahan schema, route, controller, auth, database command, package, migration, atau logic public.
- Header admin kini memiliki Light/Dark theme toggle berbasis `localStorage`; preview Hero mendukung mode Handphone dan Windows.
- Hero CMS kini full-editable untuk badge, trust line, chip layanan, background hero, gambar figur direktur, alt, role, dan nama figur. Perlu menjalankan migration additive field Hero sebelum menyimpan field baru di database existing.

## Ringkasan Arsitektur (terverifikasi)
- Aplikasi aktif tunggal: `zzk-web/` (Laravel 13, PHP 8.3, Tailwind 4, Vite 8, Alpine 3).
- Auth admin: guard `auth:admin`, model `App\Models\Admin`, tabel `admins` (terpisah dari `users` bawaan).
- **Jembatan data public** sudah ada di `app/Providers/AppServiceProvider.php`: konten DB
  (SiteSetting, HeroSection, Service, ProcessStep, Faq, Gallery, SeoSetting) di-*merge* ke
  `config('company')` saat runtime, di-cache 6 jam, dan cache otomatis di-flush saat model konten
  disimpan/dihapus. Semua partial public membaca `config('company.*')` → otomatis CMS-driven dengan
  fallback aman ke config statis `config/company.php`.

## Status Checkpoint
| CP | Lingkup | Status |
|----|---------|--------|
| 0  | `.gitignore` root project | Sudah ada & sesuai — tidak diubah |
| 1  | Site Settings control center + kolom baru + wiring public | Selesai |
| 2  | Integrasi Layanan/Alur/FAQ/Galeri/SEO ke public | Sudah terhubung via bridge (diverifikasi) |
| 3  | Polish dashboard + navigasi admin | Selesai |
| 4  | Hardening validasi/upload + test + dokumentasi | Selesai (lihat known issue) |

## Perubahan Kunci CP1
- Migration additive `2026_07_06_140000_add_profile_fields_to_site_settings.php` menambah kolom
  nullable: `consultant_name, company_description, vision, mission, operating_hours, maps_url,
  maps_embed_url`. Tidak menghapus/rename kolom lama.
- `AppServiceProvider::buildSiteContent()` kini juga mem-bridge: `brand, consultant_name, about,
  vision, mission, operating_hours, maps_url, maps_embed`.
- Form admin **Profil & Identitas** dikelompokkan 5 section (Identitas / Tentang / Kontak &
  Operasional / Lokasi & Sosial / Media) + `UpdateSiteSettingRequest` (WhatsApp = string, URL valid,
  field baru nullable — tidak menolak record lama).
- Partial public diberi sumber CMS + fallback: `navbar` (nama/tagline), `hero` (nama konsultan pada
  caption), `tentang` (deskripsi), `visi-misi` (visi + misi per-baris), `kontak` & `footer` (jam
  operasional), maps via bridge.

## Known Issue / Batas yang Disengaja
1. **Bridge dilewati saat PHPUnit.** `AppServiceProvider::boot()` guard `runningInConsole()` →
   selama test HTTP (CLI), merge DB→config tidak jalan. Jadi test tidak memverifikasi "homepage
   memakai SiteSetting"; verifikasi itu dilakukan manual di browser. Test yang ditulis fokus pada
   resiliensi homepage, auth, persistensi field, artikel draft, dan form kontak.
2. **Git root = folder home user**, bukan folder project. Repo `references/adminpage` adalah nested
   git repo. `.gitignore` project sudah menyiapkan ignore yang benar untuk saat project dijadikan
   repo sendiri; tidak ada git command yang dijalankan.
3. Section `edukasi` masih memakai array statis `$eduCards` (belum ada tabel khusus) — di luar scope
   patch ini; galeri utama sudah CMS-driven.

## Perintah Manual (dijalankan pengguna)
```bash
cd zzk-web
php artisan migrate            # jalankan migration additive site_settings
php artisan storage:link       # sekali saja, agar asset('storage/..') tampil
php artisan test               # jalankan test (opsional)
npm run build                  # bila ada perubahan asset/CSS
```
