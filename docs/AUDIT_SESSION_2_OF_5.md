# Audit Project PT Zam Zam Khan — Sesi 2/5

## Status

Selesai untuk scope sesi 2: website publik, integrasi CMS ke homepage/artikel, CTA WhatsApp, SEO, media/link, keamanan rendering frontend, dan accessibility dasar.

Audit visual browser belum dapat diselesaikan karena Playwright CLI dan Chrome headless lokal gagal mengembalikan kanal kontrol/render. Temuan sesi ini didasarkan pada source, database, test otomatis, HTML runtime, HTTP endpoint, dan pemeriksaan aset aktual. Validasi visual/responsive tetap harus diulang pada sesi 5.

## Ringkasan Eksekutif

Kondisi runtime publik saat ini sehat: homepage dan artikel merespons 200, setiap halaman mempunyai satu H1, seluruh gambar memiliki atribut alt, semua aset lokal yang dirujuk tersedia, semua anchor navigasi saat ini memiliki target, JSON-LD valid, sitemap valid, slug artikel tidak dikenal menghasilkan 404, dan seluruh CTA WhatsApp yang dirender memakai satu nomor yang sama.

Integrasi CMS belum sepenuhnya final pada keadaan kosong. Nilai contact/profile yang dikosongkan dapat hidup kembali dari config statis. Section yang disembunyikan karena semua data nonaktif tidak ikut mengubah navigasi atau form WhatsApp. Agenda tanpa data tetap menampilkan empty section. Structured data juga masih menggunakan beberapa nilai statis dan encoder JSON-LD yang belum aman untuk konteks script.

## Validasi Runtime

| Pemeriksaan | Hasil |
|---|---|
| Homepage `/` | 200, 198406 byte, 1 H1 |
| Daftar artikel `/artikel` | 200, 56929 byte, 1 H1 |
| Detail artikel aktif | 3/3 merespons 200, masing-masing 1 H1 dan 1 JSON-LD |
| Slug artikel tidak dikenal | 404 |
| Gambar homepage | 25 elemen, 0 tanpa alt |
| Gambar daftar artikel | 3 elemen, 0 tanpa alt |
| Aset lokal | 26 referensi unik diperiksa, 0 hilang |
| Nomor WhatsApp runtime | 1 nomor unik |
| JSON-LD | valid dan dapat diparse |
| Active-scheme URL | 0 `javascript:`, `data:`, atau `vbscript:` pada HTML runtime |
| Link `target=_blank` | 5, seluruhnya memakai `noopener`/`noreferrer` |
| Sitemap | XML valid, 5 URL |
| Robots | 200, melarang `/admin`, mendeklarasikan sitemap |
| Link eksternal utama | Instagram, WhatsApp, dan Google Maps merespons 200 saat diuji |

## Temuan Integrasi dan Fungsional

### PUB-01 — Medium — Field nullable tidak benar-benar dapat dikosongkan dari CMS

- Lokasi: `app/Providers/AppServiceProvider.php:64-95`, `resources/views/admin/settings/edit.blade.php:36-40`.
- Bukti: site content selalu dimulai dari `config('company')`; override database difilter memakai `filled()`. Form admin juga mengisi ulang phone, WhatsApp, email, address, dan operating hours memakai operator fallback ketika nilai database kosong.
- Dampak: mengosongkan WhatsApp, telepon, email, alamat, Maps, tagline, atau sosial dapat menghidupkan kembali nilai statis lama. Admin melihat penyimpanan sukses, tetapi nilai publik tidak benar-benar hilang. Khusus WhatsApp, nomor fallback lama dapat kembali dipakai.
- Cakupan test saat ini: test hanya memastikan nilai custom lama tidak tampil lagi; tidak memastikan nilai config statis juga tidak muncul.
- Perbaikan: jika baris `site_settings` sudah ada, jadikan database authoritative termasuk nilai `null`; gunakan config statis hanya saat baris belum pernah dibuat.

### PUB-02 — Medium — Section dinamis hilang tetapi navigasi dan form tetap statis

- Lokasi: `config/company.php:27-37`, `resources/views/partials/hero.blade.php:71`, `resources/views/partials/layanan.blade.php:75-76`, `resources/views/partials/keunggulan.blade.php:6-7`, `resources/views/partials/artikel.blade.php:8-11`, `resources/views/partials/faq.blade.php:1-2`, `resources/views/partials/whatsapp-lead-form.blade.php:9-17,81-95`, `resources/js/app.js:319-331`.
- Bukti: section Layanan, Keunggulan, Artikel, dan FAQ dirender kondisional, sedangkan nav selalu berisi anchor terkait. Hero selalu menuju `#layanan`. Form WhatsApp selalu mewajibkan layanan walaupun daftar service kosong.
- Dampak: setelah semua item dinonaktifkan, link nav/CTA menjadi dead anchor dan konsultasi WhatsApp tidak dapat disubmit.
- Kondisi runtime: tidak terjadi saat audit karena seluruh target yang ada di nav saat ini tersedia.
- Perbaikan: bangun nav dari daftar section aktif; sembunyikan/ubah CTA hero ketika layanan kosong; sediakan opsi konsultasi umum yang tidak bergantung pada service aktif.

### PUB-03 — Medium — Homepage menampilkan empty Agenda section

- Lokasi: `resources/views/partials/agenda.blade.php:17-67`.
- Bukti: elemen `<section id="agenda">` selalu dirender. Saat database berisi 0 agenda, homepage menampilkan kartu “Belum ada agenda terjadwal”.
- Dampak: bertentangan dengan Definition of Done “homepage tidak menampilkan section kosong” dan menambah section tanpa konten aktual.
- Perbaikan: bungkus seluruh section dengan kondisi `$agendas->isNotEmpty()` atau tetapkan keputusan produk bahwa empty state publik memang diinginkan dan perbarui DoD.

### PUB-04 — Medium — Structured data tidak sepenuhnya mengikuti CMS

- Lokasi: `resources/views/layouts/app.blade.php:25-40`, `resources/views/partials/seo-jsonld.blade.php:14-30`.
- Bukti: canonical homepage dapat berasal dari CMS, tetapi default `og:url` selalu `url('/')`. JSON-LD memakai description, logo, dan street address statis walaupun profil/logo/alamat dapat diubah admin.
- Dampak: metadata mesin pencari dapat berbeda dari konten publik dan data admin.
- Perbaikan: gunakan sumber CMS yang sama untuk canonical/OG/JSON-LD, dengan fallback hanya saat SiteSetting/SEO setting belum tersedia.

### PUB-05 — Low — ID SVG fallback cover artikel duplikat

- Lokasi: `resources/views/partials/article-cover.blade.php:14-20`.
- Bukti runtime: ID `acg` muncul tiga kali pada homepage dan tiga kali pada daftar artikel.
- Dampak: DOM tidak valid dan referensi gradient SVG antar-card dapat bertabrakan. Saat ini gradient identik sehingga dampak visual kemungkinan kecil.
- Perbaikan: buat ID unik berdasarkan ID/slug artikel atau hilangkan kebutuhan ID dengan gradient yang tidak direferensikan lintas SVG.

### PUB-06 — Low — Artikel berstatus Terbit tidak membatasi tanggal publikasi masa depan

- Lokasi: `app/Models/Article.php:42-45`, `resources/views/admin/articles/form.blade.php:61-74`.
- Bukti: scope `published()` hanya memeriksa status. Tanggal publikasi boleh diisi tanggal masa depan, tetapi tidak dipakai sebagai batas visibilitas.
- Dampak: bila admin menganggap field tersebut sebagai jadwal publikasi, artikel akan tampil lebih awal.
- Kondisi runtime: 3 artikel published aktif dan 0 yang bertanggal masa depan.
- Perbaikan: pertegas label sebagai tanggal metadata atau tambahkan kondisi `published_at <= now()` beserta test scheduling.

### PUB-07 — Low — Runtime lokal bergantung pada Vite dev server

- Bukti: `public/hot` menunjuk `http://[::1]:5173`; CSS/JS runtime dimuat dari Vite HMR. Manifest build produksi tersedia dan aset dev merespons 200.
- Dampak: jika working directory dikirim langsung ke production beserta `public/hot`, halaman akan mencoba mengakses dev server dan kehilangan CSS/JS.
- Perbaikan: hapus `public/hot` pada proses deploy dan gunakan hasil `npm run build`. File sudah tercakup `.gitignore`, tetapi deployment berbasis ZIP/copy tetap perlu diperiksa.

## Temuan Keamanan Frontend

### FE-SEC-01 — Medium — JSON-LD raw berpotensi script-breakout

- Lokasi: `resources/views/partials/seo-jsonld.blade.php:38-40`, `resources/views/articles/show.blade.php:39-41`.
- Evidence: output menggunakan `{!! json_encode(...) !!}` tanpa `JSON_HEX_TAG` atau `Js::from()`.
- Impact: data CMS yang mengandung `</script>` dapat memutus elemen JSON-LD dan menghasilkan stored XSS pada halaman publik. Penyerang saat ini memerlukan akses pengelolaan konten.
- Fix: gunakan encoder aman konteks script seperti `Illuminate\Support\Js::from()`.
- Mitigation: CSP ketat setelah inline script/style ditangani.
- False-positive note: analytics JSON memakai seluruh flag `JSON_HEX_*` dan tidak termasuk temuan ini.

### FE-SEC-02 — Medium/Low — Skema URL CMS belum dibatasi konsisten

- Lokasi: `app/Http/Requests/Admin/UpdateSiteSettingRequest.php:35-39`, `app/Http/Controllers/Admin/SeoController.php:20-27`.
- Evidence: Maps, embed, sosial, dan canonical memakai rule `url` generik; Agenda dan Client sudah memakai `url:http,https`.
- Impact: akun admin dapat menyimpan skema non-HTTP ke konteks href/src/canonical.
- Fix: gunakan allowlist HTTP/HTTPS dan batasi iframe Maps ke host yang memang diperlukan.
- False-positive note: HTML runtime saat audit tidak mengandung active-scheme URL.

### FE-SEC-03 — Low — Tidak ada third-party script dan storage sensitif

- Hasil negatif yang penting: tidak ditemukan `eval`, `new Function`, `document.write`, `postMessage`, remote third-party script, token/session pada Web Storage, atau navigasi publik yang berasal dari URL attacker.
- `localStorage` hanya menyimpan preferensi tema.
- `window.open` WhatsApp membangun host tetap `wa.me`, nomor dinormalisasi menjadi digit, dan pesan di-encode.

## Accessibility Dasar

### A11Y-01 — Medium/Low — Focus management dialog belum lengkap

- Lokasi: `resources/js/app.js:31-44,70-75,227-247`, `resources/views/partials/navbar.blade.php:73-95`, `resources/views/partials/whatsapp-lead-form.blade.php:19-41`.
- Bukti: saat drawer/modal dibuka, fokus dipindah ke tombol close/input pertama. Tidak ada focus trap, inert pada background, atau pemulihan fokus ke trigger setelah ditutup.
- Dampak: pengguna keyboard/screen reader dapat berpindah ke konten di belakang dialog atau kehilangan posisi setelah menutup.
- Perbaikan: simpan trigger aktif, trap Tab/Shift+Tab, set background inert, dan kembalikan fokus setelah close.

### A11Y-02 — Low — Tidak ada skip link

- Lokasi: `resources/views/layouts/app.blade.php:59-64`.
- Bukti runtime: tidak ditemukan tautan “Lewati ke konten” dan elemen main tidak memiliki target ID.
- Dampak: pengguna keyboard harus melewati navigasi berulang pada setiap halaman.
- Perbaikan: tambahkan skip link yang terlihat saat fokus dan target `main-content`.

## Kontrol yang Sudah Baik

- Seluruh output teks CMS utama dirender dengan escaping Blade.
- Konten artikel dirender per paragraf menggunakan `e()` sebelum `nl2br`.
- Tidak ada gambar runtime tanpa alt.
- FAQ memakai button, `aria-expanded`, dan `aria-controls`.
- Drawer dan modal mendukung Escape serta memindahkan fokus awal.
- Autoplay testimoni dan animasi memperhatikan `prefers-reduced-motion`; hover/focus menghentikan autoplay.
- Semua link tab baru memakai `noopener`/`noreferrer`.
- Layanan menggunakan pesan WhatsApp khusus dan `is_featured` memengaruhi urutan/badge.
- Layanan/FAQ yang seluruhnya nonaktif tidak dihidupkan ulang sebagai row statis.
- Artikel draft tidak publik dan detail slug tidak dikenal mengembalikan 404.
- Artikel kosong tidak merender section Artikel.
- Klien/testimoni tanpa data valid tidak merender section.
- Navbar dan footer memakai logo CMS dengan fallback aset statis.

## Pengujian yang Dilakukan

- 44 test terkait public/CMS lulus, 291 assertion.
- Inspeksi HTML runtime homepage, daftar artikel, dan tiga detail artikel.
- Pemeriksaan H1, duplicate ID, image alt, active URL scheme, target blank, JSON-LD, canonical, dan anchor target.
- Pemeriksaan keberadaan seluruh aset lokal yang dirujuk.
- Pemeriksaan sitemap, robots, 404 article, dan endpoint eksternal utama.
- Static trace dari field admin → request/controller → model/provider → Blade/JS publik.
- Security scan frontend berdasarkan pedoman vanilla JavaScript.

## Keterbatasan dan Pekerjaan Tersisa

- Playwright CLI dan Chrome headless gagal menyelesaikan snapshot; tidak ada klaim visual desktop/mobile.
- Profile/cookie sementara browser audit sudah dihapus dari workspace.
- Layout overflow, overlap, contrast aktual, drawer mobile, modal WhatsApp, slider, dan focus order perlu diverifikasi ulang dengan browser pada sesi 5.
- Pengujian stored-XSS adversarial dan URL scheme dilakukan lebih dalam pada sesi 4 tanpa mengubah database pengguna.
- Seluruh CRUD admin dan upload menjadi fokus sesi 3.
