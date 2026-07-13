# AUDITCLAUDE.md — Laporan Audit & Uji Coba Penuh

**Project:** PT Zam Zam Khan — Website Company Profile / CMS
**Tanggal audit:** 13 Juli 2026
**Auditor:** Claude (Senior Laravel Engineer / CMS Auditor / Web Deployment Reviewer)
**Sifat audit:** **READ-ONLY.** Tidak ada satu baris kode, migration, konfigurasi, atau data yang diubah.
**Metode:** Static analysis + eksekusi test suite (SQLite in-memory) + crawl HTTP live (server `php artisan serve` sementara di port 8199, sudah dimatikan) + inspeksi skema MySQL langsung.

---

## 0. Status Perbaikan (pasca-audit, 13 Juli 2026)

Audit di bawah ini adalah **snapshot kondisi saat audit dijalankan**. Setelah laporan dibuat, 3 dari 4 temuan sudah **dikerjakan dan diverifikasi**:

| Temuan | Status | Bukti verifikasi |
|---|---|---|
| **BLOCKER-1** — `public/hot` | ✅ **SELESAI** | File dihapus + `npm run build`. Homepage & admin kini memuat `/build/assets/app-DVj93QKa.css` (200) dan `/build/assets/app-BM7Z-EYI.js` (200). **0 referensi** ke `:5173`. `php artisan deploy:check` → **"Deployment check lulus"** (14/14 PASS). |
| **BLOCKER-2** — `.env` production | ⏳ **DITUNDA** (atas permintaan) | Belum dikerjakan. Lihat §2 BLOCKER-3 + rekomendasi §8. |
| **Temuan-2** — Section Agenda kosong | ✅ **SELESAI** | `section_visibility['agenda']` kini kondisional. Dengan `agendas = 0`: section, menu navbar `#agenda`, dan empty state **semuanya hilang**. Muncul otomatis saat admin menjadwalkan agenda. |
| **Temuan-1** — `$svcMeta` hardcoded | ✅ **SELESAI** | Konten hardcoded dihapus; `$svcMeta` diciutkan jadi peta modifier CSS (`$serviceCardMods`). Modal murni dari DB, blok kosong disembunyikan via `x-if`. |

**Berkas yang diubah:** `app/Providers/AppServiceProvider.php`, `resources/views/partials/agenda.blade.php`, `resources/views/partials/layanan.blade.php`, `tests/Feature/PublicCmsConsistencyTest.php`, `tests/Feature/ServiceManagementTest.php`, `public/build/*` (hasil build).

**Test:** **105/105 lulus** (naik dari 104 — ada 1 regression test baru: `test_detail_layanan_yang_dikosongkan_admin_tidak_diisi_teks_hardcoded`). Pint: bersih.

> Konsekuensi: **DoD #9 dan #14 (§7) kini LULUS PENUH**, dan Temuan-1 & Temuan-2 (§5) sudah tidak berlaku. Satu-satunya blocker deploy yang tersisa adalah **konfigurasi `.env` production (BLOCKER-3)**.

---

## 1. Ringkasan Eksekutif

Kondisi project **jauh lebih matang** dibanding diagnosis awal di `CLAUDE.md` §3. Seluruh **Tugas Prioritas 1–7 sudah dikerjakan dan terverifikasi lulus** dalam audit ini. Fitur ghost (Pesan Masuk, Alur Pendampingan, Galeri) benar-benar sudah dihapus sampai ke level tabel database, dan WhatsApp sudah menjadi *single source of truth* yang sesungguhnya.

| Aspek | Status |
|---|---|
| Test suite | **104/104 lulus** (716 assertions, 35 detik) |
| Migration | **21/21 applied**, tidak ada yang tertunda |
| Halaman admin | **28/28 render HTTP 200** |
| Halaman publik | **Semua 200** (home, artikel, detail artikel, sitemap, robots) |
| Gambar homepage | **22/22 resolve HTTP 200**, tidak ada broken image |
| Integritas database | **Bersih** — FK benar, 0 orphan record |
| WhatsApp SSoT | **LULUS** — hanya nomor admin yang tampil |
| Fitur ghost | **BERSIH** — tabel sudah di-drop |
| Error aplikasi | **0** sejak migration terakhir (09:33 hari ini) |

**Vonis:** Aplikasi **sehat secara fungsional**, tetapi **BELUM SIAP DEPLOY** karena 3 blocker di lapisan konfigurasi/rilis (bukan di kode). Ketiganya bisa diselesaikan tanpa menyentuh kode aplikasi.

> **Catatan penting soal `CLAUDE.md`:** dokumen instruksi masih memuat diagnosis lama (nomor WA tidak sinkron, modul Pesan Masuk/Alur/Galeri masih ada). **Diagnosis itu sudah usang.** Audit ini membuktikan semuanya sudah beres. Bagian §3 `CLAUDE.md` sebaiknya diperbarui agar tidak menyesatkan pengerjaan berikutnya.

---

## 2. Blocker Deploy (WAJIB diselesaikan sebelum rilis)

### 🔴 BLOCKER-1 — File `public/hot` masih ada (paling kritis)

**Bukti:**
```
$ ls -la public/hot
-rw-r--r-- 1 ... 17 bytes  public/hot
$ cat public/hot
http://[::1]:5173
```

Selama file ini ada, direktif `@vite` **mengabaikan hasil build** dan menarik aset dari Vite dev server:

```html
<!-- HTML yang benar-benar dirender saat ini, di public DAN admin -->
<link rel="stylesheet" href="http://[::1]:5173/resources/css/app.css" ...>
<script type="module" src="http://[::1]:5173/resources/js/app.js"></script>
<script type="module" src="http://[::1]:5173/@vite/client"></script>
```

**Kenapa sekarang tidak terlihat rusak:** kebetulan Vite dev server sedang berjalan di mesin ini (saya verifikasi: `http://[::1]:5173/resources/css/app.css` → **200**). Jadi di lokal semuanya tampak normal.

**Kenapa ini fatal di production:** `[::1]` adalah *localhost milik browser pengunjung*, bukan server. Begitu di-deploy (atau begitu `npm run dev` dimatikan), **seluruh CSS dan JS gagal dimuat** di website publik **dan** di admin panel:

- Halaman tampil tanpa styling sama sekali (Tailwind hilang total).
- **Alpine.js tidak dimuat** → modal Detail Layanan mati, form lead WhatsApp mati, theme toggle mati, menu mobile mati, semua `x-data` mati.
- Admin panel ikut rusak (`layouts/admin.blade.php:71` memakai `@vite` yang sama).

**Fakta pendukung:** hasil build **sudah tersedia** di `public/build/assets/` (`app-B-MPWyDQ.css`, `app-BM7Z-EYI.js`, dll) beserta `manifest.json`. Jadi begitu `public/hot` hilang, aset langsung dilayani dengan benar.

**Perbaikan:** hapus `public/hot` (dihasilkan otomatis oleh `npm run dev`, dihapus otomatis saat Vite dimatikan dengan benar, atau jalankan `npm run build`).

**Mitigasi yang sudah ada:** `public/hot` **sudah masuk `.gitignore`**, jadi tidak akan ikut ter-commit. Risiko nyata hanya jika deploy dilakukan dengan **copy folder / FTP / zip** — bukan lewat `git`.

Command `php artisan deploy:check` bawaan project **sudah mendeteksi ini** dengan benar:
```
| FAIL | public/hot tidak ada | Hapus marker Vite development sebelum release. |
```

---

### 🔴 BLOCKER-2 — Kredensial admin default masih aktif

`.env` saat ini:
```
ADMIN_EMAIL=admin@gmail.com
ADMIN_PASSWORD=admin123
```

Saya berhasil **login ke admin panel** memakai kredensial ini lewat HTTP (`POST /admin/login` → `302` ke dashboard → `GET /admin/dashboard` → `200`). Kredensial tebakan-mudah ini **tidak boleh** ikut ke production.

**Perbaikan:** project sudah menyediakan command yang tepat — `php artisan admin:rotate-credentials --email=...` (password diminta interaktif; sudah ada test yang memastikan password **tidak** bisa dikirim lewat argumen CLI, dan rotasi mencabut sesi lain).

**Catatan positif:** `.env.example` sudah benar mengosongkan `ADMIN_EMAIL`/`ADMIN_PASSWORD` dengan komentar *"Wajib diisi sebelum menjalankan AdminSeeder. Tidak ada credential fallback."* — dan ada test (`AdminSeederSecurityTest`) yang memastikan seeder **gagal** bila kredensial kosong. Desainnya sudah aman; yang bermasalah hanya isi `.env` lokal.

---

### 🔴 BLOCKER-3 — `.env` masih konfigurasi lokal

`php artisan deploy:check --production` melaporkan **10 FAIL**:

| Pemeriksaan | Status |
|---|---|
| APP_ENV=production | FAIL (`local`) |
| APP_DEBUG=false | FAIL (`true`) |
| APP_URL memakai HTTPS | FAIL (`http://127.0.0.1:8000`) |
| Session terenkripsi | FAIL |
| Cookie session secure | FAIL |
| Session persistent server-side | FAIL (driver `file`) |
| CSP enforcement aktif | FAIL |
| HSTS aktif | FAIL |
| User database bukan root | FAIL (`root`, tanpa password) |
| public/hot tidak ada | FAIL (lihat BLOCKER-1) |

**Perbaikan:** `.env.production.example` **sudah menutup ke-9 item** di atas dengan benar (`APP_ENV=production`, `APP_DEBUG=false`, `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true`, `SESSION_DRIVER=database`, `SECURITY_CSP_ENABLED=true`, `SECURITY_HSTS_ENABLED=true`, `DB_USERNAME=zzk_app`, `LOG_LEVEL=warning`). Tinggal disalin dan diisi saat deploy. **Tidak perlu ubah kode.**

---

## 3. Hasil Verifikasi Tugas Prioritas `CLAUDE.md` (§6–§12)

### ✅ Prioritas 1 — WhatsApp Single Source of Truth: **LULUS PENUH**

Ini masalah utama di diagnosis lama, dan sekarang benar-benar beres.

**Data uji:**
- Nomor di database admin (`site_settings.whatsapp`): `6285234797788`
- Nomor statis lama di `config/company.php`: `6281256059099`

**Hasil crawl HTML homepage yang dirender:**
```
Kemunculan nomor ADMIN  (6285234797788) : 3
Kemunculan nomor LEGACY (6281256059099) : 0   ← nol
Seluruh link wa.me unik yang dirender    : wa.me/6285234797788   ← hanya satu
Semua angka mirip nomor 62xxxx di halaman: 6285234797788 (3x), tidak ada lainnya
```

**Grep hardcoded number di seluruh Blade:** `NONE — clean` (tidak ada satu pun nomor WA hardcoded di `resources/views/`).

**Implementasi (`app/Providers/AppServiceProvider.php`):**
- `normalizeWhatsapp()` menormalkan `08xxx`/`8xxx` → `62xxx`, membuang `+`, spasi, dan strip. ✅ sesuai §6.4 poin 4.
- Provider mengisi **kedua** key dari satu sumber DB: `company.whatsapp_number` (nomor mentah) dan `company.whatsapp` (URL `wa.me` lengkap). ✅ sesuai §6.4 poin 2–3.
- Pesan template di-`rawurlencode()`. ✅ sesuai §6.4 poin 6.
- `config/company.php` kini murni **fallback** saja. ✅ sesuai §6.5.

Semua konsumen membaca key yang sama:
`layouts/app.blade.php:71`, `partials/agenda.blade.php:54`, `partials/kontak.blade.php:3`, `partials/layanan.blade.php:74`, `partials/whatsapp-float.blade.php:21`, `partials/whatsapp-lead-form.blade.php:2`.

Dikunci oleh test: `ProfilIdentitasTest::test_nomor_whatsapp_dinormalisasi_dan_dipakai_semua_cta`.

---

### ✅ Prioritas 2, 3, 4 — Modul Ghost (Pesan Masuk / Alur Pendampingan / Galeri): **BERSIH TOTAL**

Tidak sekadar "disembunyikan" — modul-modul ini **dihapus sampai ke tabel database** lewat migration `2026_07_13_110000_remove_deprecated_cms_modules.php`:

```php
Schema::dropIfExists('messages');        // Pesan Masuk
Schema::dropIfExists('galleries');       // Galeri
Schema::dropIfExists('process_steps');   // Alur Pendampingan
```

Migration ini punya `down()` lengkap yang membuat ulang ketiga tabel → **rollback tetap aman** sesuai §7.3/§8.3.

**Verifikasi menyeluruh:**
- Grep `gallery|galeri|alur|pesan.masuk|contactmessage|inbox` di `app/ routes/ resources/views/ config/ database/` → **tidak ada sisa**. (Kata "alur" yang muncul hanya milik *workflow_steps* layanan — konsep berbeda dan sah.)
- Tidak ada Model, Controller, Route, view, menu sidebar, maupun kartu dashboard untuk ketiganya.
- Referensi mati (`route('contact.store')`, `view('admin.hero.index')`) **sudah hilang** dari codebase — saya cek langsung: `NONE — dead references fully removed`.
- Dikunci oleh test: `IntegratedCmsFeaturesTest::test_deprecated_modules_are_fully_removed` dan `::test_removed_public_sections_and_contact_inbox_form_stay_absent`.

---

### ✅ Prioritas 5 — Sinkronisasi Field Admin ↔ Website Publik: **LULUS**

Aturan §4.3 (*"Tidak boleh ada field yang terlihat editable tetapi tidak memberi efek"*) **terpenuhi**.

Form `admin/settings` mengekspos **tepat 14 field**, dan **semuanya** divalidasi di `UpdateSiteSettingRequest` **dan** dikonsumsi provider → tampil di publik:
`company_name, tagline, phone, whatsapp, email, address, operating_hours, company_description, vision, mission, maps_url, maps_embed_url, logo (+remove_logo), social_links`

Status item spesifik yang dulu bermasalah:

| Field (§10) | Status Sekarang | Bukti |
|---|---|---|
| `SiteSetting.logo_path` | ✅ **Dipakai** | Provider `publicAssetUrl()` → `company.logo_url`; DB berisi `images/logo-zzk.png`, render 200. Dikunci test upload logo → navbar & footer. |
| `SiteSetting.brand_name` | ⚠️ **Kolom mati** (lihat Temuan-4) | Tidak ada di form, tidak dibaca di mana pun. Tersembunyi dari admin → **tidak melanggar** §4.3. |
| `SiteSetting.favicon_path` | ⚠️ **Kolom mati** (lihat Temuan-4) | Tidak ada editor, tidak dibaca. Layout hardcode `images/favicon.png`. Sesuai rekomendasi §10.3 (*"sembunyikan jika belum sempat"*). |
| `HeroSection.primary_button_url` | ✅ **Diselesaikan** | Kolom masih ada tapi bernilai `null` dan **tidak diekspos**. Ada test eksplisit: `HeroSectionTest::test_tombol_hero_selalu_mengarah_ke_layanan_tanpa_field_tujuan_tombol` → sesuai rekomendasi §10.4. |
| `Service.whatsapp_message` | ✅ **Dipakai** | 8/8 layanan punya pesan khusus; `layanan.blade.php` mengirimnya via `'waMessage' => $service['whatsapp_message']`. |
| `Service.is_featured` | ✅ **Dipakai (2 efek)** | (1) **Urutan**: `orderByDesc('is_featured')->orderBy('display_order')` di provider. (2) **Visual**: badge "Unggulan" dirender di `layanan.blade.php`. Sesuai rekomendasi §10.6. |
| `Gallery.category` | ✅ **N/A** | Modul Galeri sudah dihapus total. |

---

### ✅ Prioritas 6 — Fallback Data Statis: **LULUS**

Logika di provider persis mengikuti aturan §11.3 — fallback hanya saat **tabel benar-benar kosong** (instalasi awal), **bukan** saat admin sengaja menonaktifkan semua item:

```php
$services = Service::where('is_active', true)->orderByDesc('is_featured')->orderBy('display_order')->get();
if (Service::exists()) {                 // ← ada baris? maka DB yang berkuasa,
    $company['services'] = $services...; //    meski hasilnya array kosong
}                                        //    → section disembunyikan, BUKAN fallback statis
```

Pola identik diterapkan pada FAQ (`if (Faq::exists())`). Dikunci oleh test:
`IntegratedCmsFeaturesTest::test_public_content_can_be_disabled_without_static_row_replacement` dan `PublicCmsConsistencyTest::test_section_dan_navigasi_hilang_saat_data_dinonaktifkan`.

Bonus yang melampaui spec: array `section_visibility` juga **memfilter menu navbar**, sehingga anchor link ke section yang disembunyikan ikut hilang — tidak ada broken anchor.

---

### ✅ Prioritas 7 — Homepage & Dashboard: **LULUS**

**Struktur homepage** (`home.blade.php`) persis mengikuti rekomendasi §12.1:
```
navbar → hero → tentang → visi-misi → layanan → keunggulan
      → artikel → agenda → statistik → klien → testimoni → faq → kontak → footer
```
Section yang dirender saat audit: `hero, tentang, visi-misi, layanan, keunggulan, artikel, agenda, statistik, klien, testimoni, faq, kontak`. Tidak ada `alur`, tidak ada `galeri`, tidak ada form pesan masuk. ✅

**Dashboard admin** hanya memuat modul aktif, dikelompokkan rapi:
- **Konten Website:** Hero, Profil & Identitas, Layanan, Keunggulan, Statistik, Klien, Testimoni, Agenda, Artikel, Kategori Artikel, FAQ
- **Operasional Internal:** Data Pengajuan, Kategori Bisnis, Laporan, Analitik Pengunjung
- **Pengaturan:** SEO Website, Akun Admin

Tidak ada kartu/statistik untuk Pesan Masuk, Alur, maupun Galeri. ✅ sesuai §12.2.

---

## 4. Hasil Uji Coba

### 4.1 Test Suite Otomatis — **104/104 LULUS**

```
{"tool":"phpunit","result":"passed","tests":104,"passed":104,"assertions":716,"duration_ms":35042}
```

Cakupannya memetakan langsung ke *acceptance criteria* `CLAUDE.md` — ini kualitas yang tinggi, bukan test asal lewat:

| Berkas Test | Fokus |
|---|---|
| `ProfilIdentitasTest` (11) | WA dinormalisasi & dipakai semua CTA, upload logo, CRUD misi/sosmed, cache auto-clear, validasi URL |
| `IntegratedCmsFeaturesTest` (9) | Modul deprecated benar-benar hilang, konten bisa dimatikan tanpa fallback statis |
| `AgendaScheduleTest` (11) | Agenda lampau/berlangsung, zona waktu WIB, purge otomatis |
| `OperasionalInternalTest` (11) | Export CSV/XLSX, riwayat export, integritas kategori |
| `AdminSecurityAndOperationsTest` (8) | Sesi dicabut saat admin dinonaktifkan, SVG logo ditolak, rotasi password |
| `ArticleTest` (13) | Draft tidak bocor ke publik, slug otomatis & anti-duplikat, search/filter |
| `SiteSettingTest` (10) | Guard login, rate-limit 5x gagal, homepage tetap hidup tanpa SiteSetting |
| `PublicCmsConsistencyTest` (4) | Section & nav hilang saat data dimatikan, JSON-LD aman dari script breakout |
| `SecurityHeadersTest` (3) | Security header + CSP nonce, admin no-cache |
| `VisitorAnalyticsAndAdminAccountTest` (5) | Kunjungan dicatat tanpa IP mentah, admin & bot tidak dicatat |
| `HeroSectionTest`, `HeroMediaUploadTest`, `AdvantageIconTest`, `ServiceManagementTest`, `AdminSeederSecurityTest`, `AdminModuleNavigationTest` | Hero, media, ikon, layanan, seeder, navigasi |

### 4.2 Uji Website Publik (HTTP live)

| Route | Status | Catatan |
|---|---|---|
| `GET /` | **200** | 202.650 bytes, 0,68 dtk |
| `GET /artikel` | **200** | |
| `GET /artikel/{slug}` ×3 | **200** ×3 | Ketiga artikel published |
| `GET /sitemap.xml` | **200** | XML valid, memuat 3 artikel dinamis |
| `GET /robots.txt` | **200** | `Disallow: /admin` ✅ |
| `GET /tidak-ada` | **404** | Error page bersih |

**Gambar: 22/22 → HTTP 200.** Tidak ada broken image. (8 logo klien, 11 testimoni, logo, kolase, portrait direktur.)

**SEO — semua tampil di source HTML** (§14):
```html
<html lang="id">
<title>Konsultan Halal &amp; Legalitas Usaha di Malang | PT Zam Zam Khan</title>
<meta name="description" content="PT Zam Zam Khan melayani konsultasi halal, legalitas usaha, NIB...">
<meta name="keywords" ...>
<link rel="canonical" href="...">
<meta property="og:type|og:locale|og:title|og:description|og:image|og:url" ...>
<script type="application/ld+json"> ... </script>   ← JSON-LD ada
<meta name="viewport" content="width=device-width, initial-scale=1">   ← responsive ✅
```

**Security header** (dari `SecurityHeaders` middleware):
```
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=()
```
CSP nonce sudah di-*emit* di tag `<link>`/`<script>`; header CSP sendiri baru aktif saat `SECURITY_CSP_ENABLED=true` (default di `.env.production.example`). HSTS menyusul saat HTTPS aktif. Perilaku ini benar.

### 4.3 Uji Admin Panel (HTTP live, sesi ter-autentikasi)

Login sungguhan: `GET /admin/login` (ambil CSRF) → `POST /admin/login` → **302** ke dashboard → sesi valid.

**Kontrol akses:** `GET /admin/dashboard` tanpa login → **302** (redirect ke login). ✅

**28/28 halaman admin → HTTP 200:**

| | | |
|---|---|---|
| dashboard ✅ | analytics ✅ | account ✅ |
| articles ✅ / create ✅ | article-categories ✅ | services ✅ / create ✅ |
| faqs ✅ / create ✅ | advantages ✅ / create ✅ | statistics ✅ / create ✅ |
| clients ✅ / create ✅ | testimonials ✅ / create ✅ | agendas ✅ / create ✅ |
| applications ✅ / create ✅ | business-categories ✅ | reports ✅ / print ✅ |
| hero ✅ | settings ✅ | seo ✅ |

**Halaman edit record nyata → semua 200:** services, faqs, advantages, statistics, clients, testimonials, applications/14 (+ show), articles ×3.

> **Catatan investigasi:** awalnya `/admin/articles/1/edit` mengembalikan **404** dan sempat saya curigai sebagai bug. **Bukan bug** — `Article::getRouteKeyName()` mengembalikan `'slug'`, jadi route admin artikel memang di-*key* dengan slug. Diverifikasi ulang dengan slug asli → ketiganya **200**. View `admin/articles/index` juga sudah mengoper objek model ke `route()`, sehingga link yang dihasilkan otomatis benar.

**Ketahanan input:** parameter query tak dikenal (`?module=...`) diabaikan dengan aman tanpa error — tidak ada kebocoran/500.

### 4.4 Uji Integrasi Database

**Koneksi:** MySQL 8.4.3 @ `127.0.0.1:3308`, database `zzk_web` — **terhubung**.
**Migration:** **21/21 Ran**, 0 tertunda.

**Isi tabel:**
```
admins 1                                articles 3
advantages 6                            article_categories 7
agendas 0            ← kosong           business_applications 1
clients 8                               business_categories 8
faqs 5                                  business_application_status_histories 1
hero_sections 1      ← singleton        report_exports 5
services 8                              seo_settings 1
site_settings 1      ← singleton        statistics 4
testimonials 11                         web_visits 75
users 0              ← tabel legacy tak terpakai (auth memakai `admins`)
```

**Foreign key — semua terpasang dengan aturan hapus yang benar:**
```
articles.article_category_id                        -> article_categories       SET NULL
business_applications.business_category_id          -> business_categories      SET NULL
business_applications.created_by / updated_by       -> admins                   SET NULL
business_application_status_histories.business_application_id -> business_applications  CASCADE
business_application_status_histories.changed_by    -> admins                   SET NULL
report_exports.generated_by                         -> admins                   SET NULL
```
Pilihan `SET NULL` vs `CASCADE` sudah tepat: menghapus admin **tidak** ikut menghapus data pengajuan (audit trail terjaga), tetapi riwayat status ikut terhapus bersama pengajuan induknya.

**Unique constraint / pengaman singleton:**
```
site_settings   unique: id, singleton_key   ← dijamin 1 baris di level DB
hero_sections   unique: id, singleton_key   ← dijamin 1 baris di level DB
seo_settings    unique: id, page_key
articles        unique: id, slug
services        unique: id, slug
article_categories unique: id, slug
business_categories unique: id, name
admins          unique: id, email
```
Pola `singleton_key` ini bagus — integritas ditegakkan database, bukan cuma kode aplikasi.

**Orphan record:** **0** di ketiga relasi (artikel→kategori, pengajuan→kategori, riwayat→pengajuan).

**Konsistensi file ↔ DB:** 5 baris `report_exports`, **5/5 berkasnya ada di disk** — tidak ada *dangling reference*.
**Symlink storage:** `public/storage → storage/app/public` **aktif**. ✅

**Cache konten:** store `file`, key `site_content_v5` ter-cache (TTL 6 jam) dan **otomatis di-flush lewat model event** (`saved`/`deleted`) untuk 11 model konten. Jadi edit dari admin **langsung tampil** tanpa `cache:clear` manual — dikunci test `test_perubahan_profil_langsung_tampil_tanpa_perlu_clear_cache_manual`.

### 4.5 Log Aplikasi

`storage/logs/laravel.log` memuat 584 baris ERROR — **tetapi seluruhnya historis** (jejak proses development). Contoh: `Route [contact.store] not defined` (sisa form Pesan Masuk lama), `View [admin.hero.index] not found`, `Table zzk_web.web_visits doesn't exist` (sebelum migration jalan), sejumlah syntax error Blade saat refactor.

**Sejak migration terakhir (13 Juli 09:33): 0 error aplikasi.** Saya verifikasi ulang bahwa semua referensi mati tersebut memang sudah lenyap dari codebase.

---

## 5. Temuan Non-Blocking (perlu keputusan / perbaikan ringan)

### ⚠️ Temuan-1 — Fallback konten hardcoded di `partials/layanan.blade.php`

`resources/views/partials/layanan.blade.php:1-60` memuat array `$svcMeta` berisi **salinan lengkap** deskripsi panjang, "cocok untuk", alur, dan pesan WA untuk tiap ikon layanan — hardcoded di dalam Blade.

Array ini di-*override* oleh data DB, jadi **saat ini tidak ada dampak** (semua 8 layanan punya data lengkap). Risikonya muncul di skenario ini:

```php
'long'  => filled($service['detail']) ? $service['detail'] : $meta['long'],   // ← fallback hardcoded
'alur'  => ! empty($service['workflow_steps']) ? $service['workflow_steps'] : $meta['alur'],
'cocok' => filled($service['suitable_for']) ? $service['suitable_for'] : $meta['cocok'],
```

Bila admin **sengaja mengosongkan** detail/alur sebuah layanan, teks hardcoded lama akan **muncul kembali**. Ini bertentangan dengan `CLAUDE.md` §11.3: *"Jangan gunakan fallback jika admin sengaja menghapus/mengosongkan modul."*

Konten yang sama juga terduplikasi di `config/service-details.php` **dan** di database → tiga sumber kebenaran untuk data yang sama.

**Saran:** jadikan field kosong = benar-benar kosong (sembunyikan baris terkait di modal), dan pensiunkan `$svcMeta` sebagai sumber konten. **Tidak mendesak** — tidak ada gejala saat ini.

### ⚠️ Temuan-2 — Section Agenda selalu tampil meski kosong

`agendas` = **0 baris**, tetapi provider menetapkan `'agenda' => true` secara permanen di `section_visibility`, sehingga homepage tetap merender section Agenda berisi *empty state* ("Belum ada agenda terjadwal").

Ini **keputusan sadar** — ada komentar eksplisit di `AppServiceProvider`:
> *"Agenda adalah modul aktif. Section tetap tersedia sebagai empty state ketika belum ada jadwal, sehingga fitur dan navigasinya tidak lenyap."*

Namun ini **menyimpang dari** `CLAUDE.md` §16 poin 9 (*"Homepage tidak menampilkan section kosong"*). Semua section lain (layanan, FAQ, testimoni, klien, statistik) mengikuti aturan sembunyi-saat-kosong.

**Perlu keputusan produk:** empty state Agenda memang tampil rapi dan terdesain baik. Tapi bila website dirilis tanpa agenda apa pun, pengunjung melihat satu section kosong permanen. **Rekomendasi:** isi minimal 1 agenda sebelum rilis, **atau** ubah `'agenda' => true` menjadi kondisional seperti section lain.

### ⚠️ Temuan-3 — Modul Laporan lebih sempit dari spec §13

Laporan yang ada **hanya mencakup Data Pengajuan** (`BusinessApplication`) — filter, preview tabel, export CSV/XLSX, dan cetak PDF semuanya khusus pengajuan.

`CLAUDE.md` §13.2 merekomendasikan juga: Laporan Konten Website, Artikel, Layanan, FAQ, dan SEO. Ini **belum ada**.

Export PDF diimplementasikan sebagai **cetak lewat browser** (`window.print()` pada `reports/print`), bukan generator PDF sisi server. Pendekatan ini sah dan bebas dependensi — sesuai semangat §13.4 (*"Jangan over-engineer"*).

**Status:** Prioritas 13 adalah tugas **opsional**, jadi ini **bukan pelanggaran**. Catat saja sebagai gap sadar terhadap spec.

### ⚠️ Temuan-4 — Kolom database mati

Kolom di `site_settings` yang **tidak ada di form admin dan tidak dibaca di mana pun**:
- `brand_name`
- `consultant_name`
- `favicon_path` — favicon di-hardcode `images/favicon.png` di 3 tempat (`layouts/app.blade.php:49`, `layouts/admin.blade.php:9`, `admin/auth/login.blade.php:9`)
- `facebook_url`, `instagram_url`, `tiktok_url` — masih dibaca provider, tapi hanya sebagai **fallback legacy** bila `social_links` (JSON, mekanisme aktif) kosong

Tabel `users` juga kosong dan tak terpakai (autentikasi memakai tabel `admins`).

**Ini TIDAK melanggar §4.3** — aturan melarang field yang *terlihat editable tapi tidak berefek*. Kolom-kolom ini tersembunyi dari admin, jadi memenuhi kondisi 3 (*"Disembunyikan dari admin"*), dan untuk favicon persis mengikuti rekomendasi §10.3. Murni catatan kebersihan skema.

### ⚠️ Temuan-5 — Housekeeping log & cache

- `storage/logs/laravel.log` = **5,3 MB** dengan `LOG_LEVEL=debug` dan channel `single` (satu berkas yang tumbuh selamanya). `.env.production.example` sudah benar memakai `LOG_LEVEL=warning`; **pertimbangkan `LOG_STACK=daily`** agar log ter-rotasi. Kosongkan log sebelum rilis.
- Cache konten di-flush lewat **model event**. Perubahan data **di luar Eloquent** (migration yang memakai `DB::table()`, seeder, SQL manual) **tidak** memicu flush. Setelah perubahan data semacam itu, jalankan `php artisan cache:clear`.
- Drift kecil: `.env` memakai `CACHE_STORE=file`, sedangkan `.env.example` memakai `database`. Kosmetik saja.

---

## 6. Checklist Deploy Readiness (`CLAUDE.md` §14)

| Item | Status |
|---|---|
| `.env` production | ❌ Masih `.env` lokal — salin dari `.env.production.example` |
| `APP_ENV=production` | ❌ saat ini `local` |
| `APP_DEBUG=false` | ❌ saat ini `true` |
| `APP_KEY` valid | ✅ ada dan valid |
| Database production benar | ⚠️ saat ini `root` tanpa password |
| Storage link aktif | ✅ symlink terpasang |
| Permission folder benar | ✅ `storage` & `bootstrap/cache` writable |
| Asset frontend sudah dibuild | ⚠️ build **ada**, tapi `public/hot` menimpanya (BLOCKER-1) |
| Tidak ada error 500 | ✅ 0 error sejak migration terakhir |
| Tidak ada broken image utama | ✅ 22/22 gambar → 200 |
| Tidak ada hardcoded nomor lama | ✅ 0 kemunculan nomor legacy |
| Semua CTA WhatsApp benar | ✅ satu nomor, dari admin |
| SEO meta tampil di source HTML | ✅ title, description, canonical, OG, JSON-LD |
| Halaman admin bisa login | ✅ terverifikasi (tapi rotasi kredensial dulu — BLOCKER-2) |
| CRUD utama berjalan | ✅ 28/28 halaman 200; CRUD tertutup 104 test |
| Mobile responsive | ✅ viewport meta ada, Tailwind responsive (⚠️ hanya bisa benar-benar diuji visual setelah BLOCKER-1 beres) |
| Cache Laravel aman | ✅ auto-flush lewat model event |

---

## 7. Definition of Done (`CLAUDE.md` §16)

| # | Kriteria | Status |
|---|---|---|
| 1 | WhatsApp admin jadi single source of truth | ✅ **LULUS** |
| 2 | Semua CTA WhatsApp memakai nomor sama | ✅ **LULUS** — 1 nomor, 0 legacy |
| 3 | Modul Pesan Masuk tidak tampil sebagai fitur aktif | ✅ **LULUS** — tabel di-drop |
| 4 | Modul Alur Pendampingan tidak tampil sebagai fitur aktif | ✅ **LULUS** — tabel di-drop |
| 5 | Galeri diputuskan jelas | ✅ **LULUS** — dihapus (Opsi A) |
| 6 | Field admin yang terlihat benar-benar berefek | ✅ **LULUS** — 14/14 field berefek |
| 7 | Fallback statis tidak menghidupkan data yang dimatikan admin | ✅ **LULUS** untuk Layanan & FAQ (⚠️ lihat Temuan-1 untuk detail layanan) |
| 8 | Dashboard hanya menampilkan modul aktif | ✅ **LULUS** |
| 9 | Homepage tidak menampilkan section kosong | ⚠️ **SEBAGIAN** — section Agenda tampil kosong (Temuan-2) |
| 10 | Tidak ada broken link utama | ✅ **LULUS** |
| 11 | Tidak ada broken image utama | ✅ **LULUS** — 22/22 |
| 12 | Admin CRUD utama berjalan | ✅ **LULUS** |
| 13 | SEO dasar berjalan | ✅ **LULUS** |
| 14 | Layout desktop/mobile tetap rapi | ⚠️ **Terblokir** oleh BLOCKER-1 untuk verifikasi visual final |
| 15 | Ada laporan perubahan yang jelas | ✅ **LULUS** — `CHANGELOG.md`, `AUDIT.md`, `INTEGRATION_LOG.md` + dokumen ini |

**Skor: 12 LULUS PENUH, 3 perlu tindakan ringan.**

---

## 8. Rekomendasi Tindakan (berurutan)

**Sebelum deploy (wajib):**
1. Hapus `public/hot` (atau jalankan `npm run build` dan pastikan Vite dev mati). — *BLOCKER-1*
2. Rotasi kredensial admin: `php artisan admin:rotate-credentials --email=<email-asli>`. — *BLOCKER-2*
3. Salin `.env.production.example` → `.env`, isi `APP_KEY`, kredensial DB (user **non-root**), dan mail. — *BLOCKER-3*
4. Buat user MySQL khusus aplikasi (`zzk_app`) dengan hak terbatas, bukan `root`.
5. Kosongkan `storage/logs/laravel.log` (5,3 MB berisi jejak error development).
6. Jalankan ulang `php artisan deploy:check --production` sampai **semua PASS**.
7. Baru kemudian: `composer install --no-dev --optimize-autoloader` lalu `php artisan config:cache route:cache view:cache`.

**Keputusan produk (sebelum rilis):**
8. Putuskan section Agenda: isi minimal 1 agenda, atau sembunyikan saat kosong. — *Temuan-2*

**Perbaikan setelah rilis (tidak mendesak):**
9. Pensiunkan array hardcoded `$svcMeta` di `partials/layanan.blade.php`. — *Temuan-1*
10. Perbarui `CLAUDE.md` §3 — diagnosis di sana sudah usang dan bisa menyesatkan pengerjaan berikutnya.
11. Pertimbangkan `LOG_STACK=daily` untuk rotasi log.
12. Opsional: perluas modul Laporan ke konten CMS (§13.2), atau turunkan/hapus item itu dari spec.
13. Opsional: bersihkan kolom mati (`brand_name`, `consultant_name`, `favicon_path`, tabel `users`) lewat satu migration khusus.

---

## 9. Pernyataan Integritas Audit

Audit ini **sepenuhnya read-only**. Yang dilakukan:

- Membaca berkas sumber, konfigurasi, migration, dan view.
- Menjalankan `php artisan test` — memakai **SQLite in-memory** (`phpunit.xml`), **tidak menyentuh** database MySQL.
- Menjalankan perintah artisan **read-only**: `route:list`, `migrate:status`, `db:show`, `deploy:check`, dan query `SELECT` lewat `tinker`.
- Menjalankan server `php artisan serve` sementara di **port 8199** untuk crawl HTTP, lalu **dimatikan**.
- Melakukan **GET** ke seluruh halaman + **satu POST login** (hanya membuat sesi; tidak mengubah data).
- Berkas sementara dibuat di direktori scratchpad/temp, lalu **dihapus**.

Yang **TIDAK** dilakukan: tidak ada `Edit`/`Write` ke berkas project, tidak ada `migrate`/`seed`/`rollback`, tidak ada `INSERT`/`UPDATE`/`DELETE`, tidak ada `cache:clear`/`optimize`, tidak ada `git` write. Verifikasi: `git diff --stat` **kosong** — tidak ada berkas ter-track yang berubah.

Satu-satunya berkas baru yang dibuat adalah **`AUDITCLAUDE.md`** ini, sesuai permintaan.
