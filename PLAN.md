# PLAN.md — Finalisasi Website PT Zam Zam Khan

Checklist kerja penyempurnaan CMS. Dibagi per sesi/fase. Centang saat selesai.

## Sesi 1 — Audit (SELESAI)
- [x] Baca `routes/web.php`
- [x] Baca `AppServiceProvider`
- [x] Baca `config/company.php`
- [x] Baca partial homepage (hero, layanan, faq, kontak, footer, navbar, galeri, whatsapp-*)
- [x] Baca controller/model admin (SiteSetting, Hero, Service, Dashboard)
- [x] Baca sidebar & dashboard admin (`layouts/admin.blade.php`, `dashboard.blade.php`, `module-navigation`)
- [x] Baca JS lead form (`resources/js/app.js`)

### Temuan kunci
- **WhatsApp**: Modal lead form membaca `config('company.whatsapp_number')`. Provider hanya set `company.whatsapp` (URL), TIDAK set `whatsapp_number` → semua CTA modal pakai nomor statis `6281256059099`, bukan nomor admin. **BUG kritis.**
- **Pesan Masuk**: form publik `@if(false)` (mati). Menu admin masih tampil (sidebar, dashboard, module-nav). Ghost.
- **Alur Pendampingan**: tidak di-include di `home.blade.php` (sudah tidak tampil publik). Menu admin masih tampil. Ghost.
- **Galeri**: partial sudah guard `@if(! empty)`. Data kosong. Menu admin masih tampil. Redundan.
- **Logo**: navbar & footer pakai `images/logo-zzk.webp` statis; `SiteSetting.logo_path` diabaikan.
- **Hero `primary_button_url`**: disimpan tapi tombol utama selalu WhatsApp modal.
- **Service `whatsapp_message` / `is_featured`**: disimpan tapi tidak berefek.
- **Fallback**: bila semua Service/FAQ nonaktif, provider tetap pakai data statis config.

## Sesi 2 — WhatsApp Single Source of Truth (Prioritas 1) — SELESAI
- [x] `AppServiceProvider`: set `company.whatsapp_number` (digit ternormalisasi) dari admin + tetap set `company.whatsapp` (URL)
- [x] Normalisasi nomor: hanya angka, `08`/`8` → `62` (helper `normalizeWhatsapp`)
- [x] Pastikan modal (`whatsapp-lead-form`) & semua CTA baca nomor sama
- [x] `config/company.php` hanya fallback

## Sesi 3 — Sembunyikan Fitur Ghost (Prioritas 2–3) — SELESAI
- [x] Sidebar (`layouts/admin.blade.php`): hapus item Alur, Galeri, Pesan Masuk
- [x] Dashboard (`DashboardController`): hapus tile & group Alur, Galeri, Pesan Masuk
- [x] `dashboard.blade.php`: rapikan teks sambutan
- [x] `module-navigation`: hapus entry Alur, Galeri, Pesan Masuk (+ Kategori Artikel yang route-nya tak ada)
- [x] Route/controller/model/view aktif dicabut pada audit lanjutan 2026-07-12; migration/tabel tetap dipertahankan agar non-destruktif

## Sesi 4 — Sinkron Field Admin ↔ Public (Prioritas 5) — SELESAI
- [x] Logo: provider `logo_url` dari `logo_path`; navbar & footer pakai logo admin + fallback statis
- [x] Hero: hapus seluruh konfigurasi CTA utama dan toggle aktif yang tidak dirender; pertahankan satu tombol layanan yang nyata
- [x] Service `is_featured`: urutkan unggulan dulu + badge "Unggulan"
- [x] Service `whatsapp_message`: prefill kolom kebutuhan lead form saat CTA layanan
- [x] `brand_name`: field disembunyikan dari form (redundan dgn company_name)

## Sesi 5 — Fallback & Homepage (Prioritas 4 & 6) — SELESAI
- [x] Provider: fallback statis hanya bila tabel Service/FAQ kosong (`::exists()`)
- [x] Guard section Layanan & FAQ agar tak tampil bila kosong
- [x] `home.blade.php`: hapus include galeri
- [x] Galeri/alur tidak muncul di publik dan route/UI aktifnya sudah dicabut

## Sesi 6 — Deploy Readiness & Laporan — SELESAI
- [x] `php artisan optimize:clear`, `route:list`, `view:cache` (semua Blade compile tanpa error)
- [x] Cek tidak ada broken route dari menu admin
- [x] Tulis `CHANGELOG.md` (modul deprecated)
- [x] Laporan akhir sesuai format CLAUDE.md §15

## Sisa (opsional, di luar critical fix)
- [ ] Modul Laporan Internal (export Excel/PDF) — hanya bila diminta client
- [ ] CMS testimoni/klien (kini masih statis di `config/company.php`) — bila perlu sering update
- [ ] Cek `.env` production (APP_ENV/APP_DEBUG/APP_KEY) & `php artisan storage:link` di server
