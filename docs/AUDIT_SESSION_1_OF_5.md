# Audit Project PT Zam Zam Khan — Sesi 1/5

## Status

Selesai untuk scope sesi 1: inventaris arsitektur, fitur, route, skema/data database, baseline test, dependency audit, dan baseline keamanan.

Audit penuh belum selesai. Audit public website, admin CRUD/upload, pengujian database mendalam, dan browser/security regression dilanjutkan pada sesi 2–5.

## Ringkasan Eksekutif

Project berjalan dan baseline otomatisnya sehat: 89 route terdaftar, seluruh 15 migration pada database aktif sudah dijalankan, 80 test dengan 539 assertion lulus, build Vite berhasil, serta Composer/npm tidak melaporkan advisory pada dependency saat audit.

Namun, project belum layak dinyatakan aman atau siap deploy. Ada tiga risiko prioritas tinggi: endpoint login admin tanpa rate limiting, seeder admin yang dapat mereset akun ke credential fallback yang dapat ditebak, dan file `ck.txt` yang menyimpan cookie sesi aktual di root project. Koneksi database lokal juga memakai akun MySQL `root` dengan akses lintas database; ini wajib diganti dengan akun least-privilege pada production.

## Inventaris Fitur Aktif

| Area | Fitur | Route/UI | Tabel/data runtime |
|---|---|---|---|
| Public | Homepage terkomposisi | Hero, Tentang, Visi–Misi, Layanan, Keunggulan, Artikel, Agenda, Statistik, Klien, Testimoni, FAQ, Kontak | Konten digabung dari config + CMS melalui `AppServiceProvider` |
| Public | Artikel & Insight | `/artikel`, `/artikel/{slug}` | 3 artikel, 7 kategori |
| Public | SEO teknis | sitemap dinamis, robots, meta/OG, JSON-LD | 1 pengaturan SEO |
| Public | WhatsApp lead | modal/form client-side dan floating CTA | sumber nomor dari Site Setting dengan fallback config |
| Admin | Dashboard CMS | `/admin/dashboard` | ringkasan modul aktif |
| Admin | Konten publik | Hero, Profil, Layanan, Keunggulan, Statistik, Klien, Testimoni, Agenda, Artikel, FAQ | 1 hero, 1 site setting, 8 layanan, 6 keunggulan, 4 statistik, 8 klien, 11 testimoni, 0 agenda, 3 artikel, 5 FAQ |
| Admin | Operasional internal | Data Pengajuan, Kategori Bisnis, Laporan | 1 pengajuan, 7 kategori, 1 histori status, 0 histori export |
| Admin | Analytics | tayangan, pengunjung unik, device, halaman/referrer | 116 web visit |
| Admin | Pengaturan | SEO dan Akun Admin | 1 SEO setting, 1 admin |

## Modul Deprecated/Legacy

`galleries`, `messages`, dan `process_steps` masih ada di database, masing-masing berisi 0, 0, dan 6 baris. Ketiganya tidak memiliki route, controller, model, menu admin, atau section homepage aktif pada source saat ini. Kondisi ini konsisten dengan strategi rollback aman, tetapi status deprecated dan kebijakan retensi tabel perlu tetap terdokumentasi.

## Temuan Keamanan

### SEC-01 — High — Login admin tanpa rate limiting

- Lokasi: `routes/web.php:61-64`, `app/Http/Controllers/Admin/AuthController.php:16-39`.
- Bukti: route login hanya memakai middleware `guest:admin`; controller langsung memanggil `Auth::guard('admin')->attempt(...)` tanpa `throttle`, `RateLimiter`, atau pembatas berbasis email/IP.
- Dampak: endpoint login dapat menjadi sasaran brute force atau credential stuffing tanpa pembatas aplikasi.
- Perbaikan: tambahkan limiter khusus login, misalnya kombinasi normalized email + IP, respons 429, dan reset limiter setelah login sukses.
- Catatan: pesan error sudah generik dan regenerasi sesi setelah login sudah benar.

### SEC-02 — High — Seeder dapat memakai dan mereset credential admin ke fallback yang diketahui

- Lokasi: `database/seeders/AdminSeeder.php:12-20`.
- Bukti: `ADMIN_EMAIL` dan `ADMIN_PASSWORD` memiliki fallback literal, lalu `updateOrCreate` selalu menulis ulang password.
- Dampak: menjalankan seeder tanpa env terkait dapat membuat atau mereset admin ke credential yang dapat ditebak.
- Kondisi runtime: `ADMIN_PASSWORD` saat ini tidak diset, tetapi akun database aktif sudah tidak memakai password fallback. Risiko tetap nyata ketika seeder dijalankan ulang.
- Perbaikan: fail fast di luar testing/local bila credential env tidak tersedia; jangan memperbarui password admin existing melalui seeder umum.

### SEC-03 — High — Cookie sesi tersimpan sebagai file project

- Lokasi: `ck.txt:1-6`.
- Bukti: file Netscape cookie jar berisi XSRF token dan cookie sesi Laravel aktual; `ck.txt` tidak diabaikan oleh `.gitignore`.
- Dampak: penyalinan project, backup, atau commit dapat membocorkan sesi dan memungkinkan session replay selama cookie masih valid.
- Perbaikan: invalidasi sesi terkait, hapus file dari project/history, dan tambahkan pola cookie jar ke `.gitignore`.
- Catatan: nilai cookie sengaja tidak direproduksi dalam laporan.

### SEC-04 — High bila production — Database tidak menerapkan least privilege

- Lokasi: `.env.example:24-29`; runtime database lokal.
- Bukti: contoh dan koneksi aktif memakai username MySQL `root`; introspeksi runtime dapat melihat 151 tabel dari beberapa database pada server yang sama.
- Dampak: compromise pada aplikasi dapat meluas ke database lain dan operasi administratif server database.
- Perbaikan: buat user khusus `zzk_web` dengan hak minimum hanya pada schema aplikasi; jangan memakai `root` pada deployment.
- Catatan: konfigurasi saat ini adalah local, sehingga ini dicatat sebagai deploy blocker, bukan bukti production terekspos.

### SEC-05 — Medium — Header hardening tidak ada pada runtime lokal

- Bukti runtime: respons `/` dan `/admin/login` tidak memiliki CSP, `X-Frame-Options`/`frame-ancestors`, `X-Content-Type-Options`, atau `Referrer-Policy`; server juga mengirim `X-Powered-By`.
- Dampak: tidak ada lapisan defense-in-depth terhadap clickjacking, MIME sniffing, dan sebagian dampak XSS.
- Perbaikan: tetapkan header pada middleware/web server; rancang CSP setelah inline script/style dipindah atau diberi nonce/hash.
- Catatan: header dapat ditambahkan di reverse proxy/CDN production; harus diverifikasi kembali pada endpoint production.

### SEC-06 — Medium — JSON-LD raw belum aman dari penutupan tag script

- Lokasi: `resources/views/partials/seo-jsonld.blade.php:38-40`, `resources/views/articles/show.blade.php:39-41`.
- Bukti: data CMS dirender dengan raw `{!! json_encode(...) !!}` tanpa `JSON_HEX_TAG` atau encoder Blade yang aman untuk konteks script.
- Dampak: nilai CMS yang mengandung `</script>` dapat memutus elemen script dan menjadi stored XSS pada halaman publik. Eksploitasi saat ini membutuhkan kemampuan mengubah konten admin.
- Perbaikan: gunakan `Illuminate\Support\Js::from(...)` atau encoding JSON dengan seluruh flag hex yang sesuai.
- Catatan: payload analytics sudah memakai `JSON_HEX_TAG`, `JSON_HEX_AMP`, `JSON_HEX_APOS`, dan `JSON_HEX_QUOT`.

### SEC-07 — Medium/Low — URL CMS belum konsisten membatasi skema

- Lokasi: `app/Http/Requests/Admin/UpdateSiteSettingRequest.php:35-39`, `app/Http/Controllers/Admin/SeoController.php:20-27`.
- Bukti: URL Maps, embed, sosial, dan canonical memakai rule `url` umum; Agenda dan Client sudah lebih tepat memakai `url:http,https`.
- Dampak: skema non-HTTP dapat masuk ke atribut `href`, `src`, atau canonical. Input hanya dapat diubah admin, tetapi tetap memperbesar dampak akun admin yang disalahgunakan.
- Perbaikan: konsisten gunakan allowlist `http,https`; untuk iframe Maps batasi host Google Maps bila requirement produk memang spesifik.

## Temuan Fungsional, Database, dan Operasional

### OPS-01 — Medium — Analytics tumbuh tanpa retensi dan mode overall memuat semua baris

- Lokasi: `app/Http/Middleware/TrackWebVisit.php:12-34`, `app/Http/Controllers/Admin/VisitorAnalyticsController.php:20-27`.
- Bukti: setiap page view publik disimpan, tidak ditemukan purge/retention job, dan query periode `overall` memanggil `get()` sebelum agregasi di PHP.
- Dampak: tabel dan penggunaan memori akan tumbuh seiring trafik; dashboard dapat melambat atau kehabisan memori.
- Perbaikan: agregasi di SQL, pagination untuk rincian, serta retention/rollup terjadwal.

### OPS-02 — Medium — Project Git lokal tidak mandiri

- Bukti: folder `.git` di root project kosong; Git justru memakai `C:\Users\WINDOWS 11\.git`, sehingga status project mencakup seluruh home directory.
- Dampak: audit diff, commit, rollback, dan deteksi file rahasia menjadi tidak andal serta berisiko memasukkan file di luar project.
- Perbaikan: pulihkan metadata repository yang benar atau inisialisasi repository project secara eksplisit setelah backup dan konfirmasi asal history.

### DB-01 — Low — Tabel legacy tetap berada pada schema aktif

- Lokasi migration awal: `database/migrations/2026_07_01_100000_create_cms_tables.php`.
- Bukti runtime: `galleries`, `messages`, dan `process_steps` masih ada walau modul tidak aktif.
- Dampak: bukan bug runtime saat ini, tetapi menambah ambiguity schema dan risiko fitur ghost muncul kembali pada pengembangan berikutnya.
- Perbaikan: pertahankan sebagai deprecated dengan dokumentasi eksplisit atau buat migration archival terpisah hanya setelah keputusan produk final.

## Kontrol yang Sudah Baik

- Semua route admin aktif berada di bawah `auth:admin`; login berada di bawah `guest:admin`.
- Form mutasi admin menggunakan CSRF dan method spoofing Laravel.
- Login/logout meregenerasi atau menginvalidasi sesi dengan benar.
- Password model memakai cast `hashed`; perubahan akun meminta credential lama dan password baru kuat.
- Query dinamis utama memakai Eloquent/binding; raw query Agenda memakai placeholder.
- Upload konten raster dibatasi rule image, MIME extension, dan ukuran; file disimpan dengan nama hasil storage.
- CSV export memitigasi formula injection untuk awalan `=`, `+`, `-`, dan `@`.
- Analytics tidak menyimpan IP mentah; visitor key berupa HMAC session ID.
- `ck.txt` adalah satu-satunya file credential-like non-example yang ditemukan pada scan source sesi ini; `.env` sudah diabaikan.

## Validasi yang Dilakukan

- `php artisan about --only=environment`
- `php artisan route:list -v --except-vendor` — 89 route
- `php artisan migrate:status` — seluruh migration `Ran`
- `php artisan db:show --counts` dan inspeksi schema tabel utama
- `php artisan test` — 80 passed, 539 assertions
- `npm run build` — berhasil
- `composer audit --no-interaction` — 0 advisory
- `npm audit --omit=dev --audit-level=low` — 0 vulnerability
- Static scan untuk raw HTML/JSON, DOM sinks, raw SQL, command execution, storage, URL, dan upload
- Runtime header inspection pada `/` dan `/admin/login`
- Verifikasi aman bahwa akun aktif tidak memakai password fallback seeder

## Batasan Sesi 1

- Belum dilakukan walkthrough browser seluruh halaman desktop/mobile.
- Belum diuji setiap operasi CRUD terhadap database runtime karena audit tidak boleh mengubah data pengguna tanpa kebutuhan.
- Belum dilakukan pengujian upload adversarial, stored-XSS proof, CSRF runtime, IDOR, race condition, atau fuzzing validasi.
- Belum diaudit detail setiap field admin terhadap output public; ini menjadi fokus sesi 2 dan 3.
- Header production/reverse proxy belum tersedia, sehingga temuan header masih berdasarkan server lokal.

## Rencana Sesi Berikutnya

1. Sesi 2/5: audit website publik, integrasi CMS, CTA WhatsApp, SEO, accessibility, responsive, broken link/image, dan stored content rendering.
2. Sesi 3/5: audit semua layar admin, setiap CRUD, upload, auth/account, laporan, analytics, dan otorisasi.
3. Sesi 4/5: audit schema/constraint/index/transaction, pengujian keamanan mendalam yang aman, dependency/config/deploy hardening.
4. Sesi 5/5: regression end-to-end, browser matrix, konsolidasi severity, rekomendasi patch, dan laporan final.
