# Audit Project PT Zam Zam Khan — Sesi 4/5

## Status

Selesai untuk scope sesi 4: konfigurasi dan integritas MySQL, constraint/index/foreign key, konsistensi data aktual, manajemen secret, CSRF, brute-force, XSS reflektif, SQL injection, path traversal, validasi URL/upload, dependency audit, dan hardening deployment.

Tidak ada source code atau data database yang diubah. Pengujian bersifat read-only, kecuali delapan percobaan login gagal terhadap alamat yang tidak terdaftar; seluruhnya memakai sesi sementara yang telah dibersihkan.

## Ringkasan Eksekutif

Kontrol dasar aplikasi cukup baik: CSRF menolak request tanpa token, output pencarian di-escape, query pencarian memakai parameter binding, percobaan traversal ditolak, file PHP palsu gagal sebagai gambar, seluruh migration tercatat `Ran`, foreign key penting sudah memiliki aksi penghapusan yang masuk akal, serta audit dependency Composer dan npm tidak menemukan advisory aktif.

Risiko terbesar adalah konfigurasi database lokal/deployment: aplikasi terhubung sebagai `root` tanpa password, akun tersebut mempunyai hak global beserta `GRANT OPTION`, sementara MySQL mendengarkan semua interface. Kondisi ini belum membuktikan root dapat login dari jaringan karena akun yang diaudit terikat ke `localhost`, tetapi jika konfigurasi aplikasi dipindahkan apa adanya atau aplikasi dikompromikan, dampaknya mencakup seluruh server database. Login admin juga tetap tidak memiliki throttling; delapan percobaan gagal beruntun tidak pernah menghasilkan HTTP 429.

Pada lapisan data, isi database saat ini bersih dari nilai status di luar domain, duplikasi singleton, nomor registrasi ganda, dan urutan ganda. Namun sebagian aturan tersebut hanya dijaga aplikasi, bukan database. `site_settings` dan hero aktif tidak dipaksa singleton, beberapa kolom status bebas menerima string apa pun, dan `report_exports.file_path` tidak unik—yang memperkuat collision export dari Sesi 3.

## Temuan Keamanan dan Deployment

### DB-SEC-01 — High / Critical bila dideploy — Aplikasi memakai superuser MySQL tanpa password

- Lokasi: `.env:28-31` dan konfigurasi server MySQL runtime.
- Bukti terverifikasi:
  - koneksi aplikasi memakai akun `root@localhost` dengan password kosong;
  - akun tersebut memiliki hak global dan `GRANT OPTION`;
  - `bind_address` bernilai `*` dan networking aktif.
- Batas bukti: akun yang diperiksa hanya `root@localhost`; audit ini tidak membuktikan autentikasi root jarak jauh dapat dilakukan. Firewall host dan perimeter production juga belum diperiksa.
- Dampak: SQL injection, command execution, atau kebocoran credential aplikasi dapat berkembang menjadi pembacaan/perubahan seluruh database pada server, pembuatan akun baru, dan perubahan privilege.
- Fix:
  - buat user khusus aplikasi yang hanya memiliki privilege pada database project;
  - pasang password kuat dan rotasi credential root;
  - batasi bind address/firewall sesuai arsitektur;
  - aktifkan koneksi terenkripsi jika database production berada di host berbeda.

### SEC-08 — High — Login admin tidak memiliki rate limit, terkonfirmasi runtime

- Lokasi: `routes/web.php:62-67`, `app/Http/Controllers/Admin/AuthController.php:16-39`.
- Bukti: delapan login gagal beruntun dengan email tidak terdaftar seluruhnya menghasilkan redirect normal; tidak ada HTTP 429. Route tidak memakai middleware `throttle` dan controller tidak memakai `RateLimiter`.
- Dampak: brute force dan credential stuffing dapat dilakukan tanpa pembatasan aplikasi.
- Fix: limiter gabungan email ter-normalisasi dan IP, respons 429 dengan waktu tunggu, reset limiter setelah sukses, logging aman, dan test untuk ambang batas.

### SEC-09 — High — File cookie sesi masih berada di project

- Lokasi: `ck.txt`.
- Bukti: pemindaian nama/marker secret menemukan token sesi dan CSRF aktual dalam file tersebut; file belum tercakup ignore project yang efektif.
- Dampak: siapa pun yang memperoleh file berpotensi mengambil alih sesi yang belum kedaluwarsa.
- Fix: invalidasi sesi terkait, hapus file dengan persetujuan pemilik, tambahkan pola cookie jar ke `.gitignore`, dan jangan menyimpan hasil `curl -c` di root project.
- Catatan: nilai token sengaja tidak direproduksi dalam laporan.

### SEC-10 — Medium — Validasi URL belum menerapkan allowlist HTTP/HTTPS secara konsisten

- Lokasi: `app/Http/Requests/Admin/UpdateSiteSettingRequest.php:35-39` dan request SEO terkait.
- Bukti runtime validator Laravel:
  - `javascript:` ditolak, sehingga dugaan awal bahwa skema tersebut lolos tidak terkonfirmasi;
  - rule generik `url` menerima FTP, `file://`, dan bentuk `data://` tertentu;
  - rule `url:http,https` menolak skema non-web.
- Dampak: link sosial, Maps, embed, atau canonical dapat menyimpan skema yang tidak sesuai tujuan field; risiko konkret bergantung pada konteks render masing-masing.
- Fix: gunakan `url:http,https`; untuk embed Maps tambahkan allowlist host resmi dan validasi ulang sebelum dirender sebagai `iframe src`.

### SEC-11 — Medium bila production — Konfigurasi development masih aktif

- Lokasi: `.env:2-4`, `public/hot`.
- Bukti: runtime lokal menggunakan debug aktif dan Vite dev-server marker.
- Penilaian: wajar untuk development, tetapi menjadi kebocoran informasi dan kegagalan asset jika artefak ini ikut deployment.
- Fix deployment: `APP_ENV=production`, `APP_DEBUG=false`, build asset production, hapus `public/hot`, set cookie `Secure` pada HTTPS, lalu cache config/route/view setelah seluruh env final.

### SEC-12 — Medium — Header hardening belum ada pada respons aplikasi lokal

- Bukti runtime: respons tidak menyertakan CSP, HSTS, `X-Content-Type-Options`, `Referrer-Policy`, maupun kebijakan frame yang eksplisit.
- Dampak: pertahanan berlapis terhadap clickjacking, MIME sniffing, kebocoran referrer, dan sebagian kelas XSS belum tersedia.
- Fix: pasang middleware atau konfigurasi reverse proxy. HSTS hanya diaktifkan setelah HTTPS production konsisten.

## Temuan Integritas Database

### DB-02 — Medium — Domain status dan tipe tidak dijaga constraint database

- Lokasi: migration pengajuan, histori status, laporan, dan `web_visits`.
- Bukti: schema tidak memiliki `CHECK` constraint untuk `applicant_type`, `process_status`, status histori, format laporan, atau `device_type`.
- Kondisi data saat audit: seluruh nilai saat ini valid menurut domain aplikasi; tidak ditemukan nilai menyimpang.
- Dampak: import, query manual, bug, atau jalur tulis baru dapat memasukkan state yang tidak dapat diproses UI secara konsisten.
- Fix: definisikan domain sebagai enum aplikasi yang terpusat dan tambahkan `CHECK` constraint kompatibel dengan versi MySQL production.

### DB-03 — Medium — Singleton pengaturan situs dan hero aktif tidak dipaksa oleh schema

- Lokasi: migration `site_settings` dan `hero_sections`; controller/provider yang mengambil satu record.
- Bukti: tabel dapat memuat banyak setting dan banyak hero aktif tanpa unique/constraint.
- Kondisi data saat audit: satu `site_settings`, satu `hero_sections`, dan tidak ada hero aktif ganda.
- Dampak: hasil `first()`/`latest()` menjadi implisit; edit record berbeda dapat membuat admin dan publik membaca sumber yang tidak sama.
- Fix: gunakan satu primary key tetap untuk setting; untuk hero, tegakkan invariant satu aktif melalui transaksi/locking atau desain pointer active hero yang unik.

### DB-04 — High — Path file export tidak unik

- Lokasi: `database/migrations/2026_07_09_010000_create_business_application_tables.php:31-36` dan generator export.
- Bukti: `report_exports.file_path` nullable dan tidak memiliki unique index, sedangkan nama file hanya presisi detik sebagaimana ADM-02 pada Sesi 3.
- Dampak: dua histori dapat menunjuk file yang sama; penghapusan salah satu histori merusak histori lain.
- Fix: hasilkan nama ULID/UUID, jadikan `file_path` non-null untuk export berhasil dan unique, serta tangani kegagalan file/database secara atomic.

### DB-05 — Low — Index slug artikel redundan

- Bukti metadata: `articles.slug` memiliki unique index sekaligus non-unique index terpisah pada kolom yang sama.
- Dampak: tambahan biaya storage dan write tanpa manfaat query berarti.
- Fix: hapus index non-unique setelah verifikasi nama index dan execution plan production.

### DB-06 — Low — Index daftar aktif/urutan belum konsisten

- Bukti metadata: `advantages`, `statistics`, `clients`, dan `testimonials` memiliki index `(is_active, display_order)`, sedangkan `services` dan `faqs` tidak.
- Dampak: query homepage akan melakukan scan/sort lebih besar saat konten bertambah. Dampak saat dataset kecil rendah.
- Fix: tambahkan composite index yang sama bila query dan `EXPLAIN` production membenarkannya.

### DB-07 — Medium — Artefak export orphan berada di private storage

- Bukti runtime: tabel `report_exports` berisi 0 record, tetapi `storage/app/private/reports` berisi 13 file, total 39.119 byte.
- Akar masalah teridentifikasi: test export memakai storage nyata sementara database di-reset.
- Dampak: storage bertambah, file testing bercampur dengan data aplikasi, dan rekonsiliasi histori menjadi tidak andal.
- Fix: `Storage::fake()`/disk testing khusus, cleanup terkontrol, dan job rekonsiliasi yang hanya menghapus orphan setelah grace period.
- Catatan: tidak ada file yang dihapus dalam audit.

### DB-08 — Low — Schema masih menyimpan tabel fitur deprecated

- Bukti: tabel untuk pesan, galeri, alur proses, dan tabel `users` legacy masih ada; route/menu aktifnya sudah tidak tersedia.
- Dampak: memperluas permukaan maintenance dan membingungkan sumber data, tetapi bukan kerentanan langsung selama tidak ada jalur tulis/baca aktif.
- Fix: dokumentasikan deprecation dan lakukan migration penghapusan hanya setelah backup, verifikasi ketergantungan, dan persetujuan eksplisit.

## Konsistensi Data Aktual

Pemeriksaan read-only menghasilkan:

| Pemeriksaan | Hasil |
|---|---:|
| Migration belum dijalankan | 0 |
| Nilai `applicant_type` di luar domain | 0 |
| Nilai status pengajuan di luar domain | 0 |
| Nilai status histori di luar domain | 0 |
| Format report di luar domain | 0 |
| Device type di luar domain | 0 |
| Duplikasi nomor registrasi non-null | 0 |
| Duplikasi display order pada 7 modul terurut | 0 |
| Duplikasi singleton setting | 0 |
| Hero aktif ganda | 0 |

Foreign key yang ada juga konsisten secara desain: kategori/admin menggunakan `SET NULL`, histori pengajuan menggunakan `CASCADE`, dan record report mempertahankan histori dengan `generated_by` menjadi null saat admin dihapus.

## Pengujian Keamanan yang Dilakukan

| Probe | Hasil |
|---|---|
| POST login tanpa CSRF | Ditolak HTTP 419 |
| Delapan login gagal beruntun | Tidak pernah 429 — rate limit tidak ada |
| Payload XSS pada pencarian artikel | Tidak tampil raw; ter-escape |
| Payload SQL injection pada pencarian | HTTP 200 normal; source memakai binding |
| Traversal `.env` via path biasa/encoded | HTTP 403/404 |
| PHP palsu bernama `.php`, MIME gambar | Ditolak validator |
| PNG valid | Diterima validator |
| SVG pada rule logo saat ini | Ditolak; mismatch UI/validator tetap berlaku |
| Composer audit | 0 advisory |
| npm production audit | 0 vulnerability |
| Composer platform requirements | Lulus |

## Kontrol yang Sudah Baik

- `APP_KEY` tersedia; nilainya tidak dicantumkan.
- Query Eloquent/query builder menggunakan parameter binding pada jalur yang diaudit.
- Form POST admin memakai CSRF dan route admin terlindungi guard sebagaimana divalidasi pada Sesi 3.
- Upload raster memverifikasi bahwa konten benar-benar gambar; file PHP palsu tidak lolos.
- Public storage link mengarah ke target storage project yang benar.
- Seluruh tabel memakai InnoDB dan foreign key utama memiliki delete rule eksplisit.
- Data aktual tidak menunjukkan pelanggaran domain/duplikasi yang diuji.
- Dependency PHP dan JavaScript tidak memiliki advisory aktif pada waktu audit.

## Keterbatasan

- Tidak dilakukan exploit destruktif, penulisan payload ke database, load test, atau pemindaian jaringan aktif.
- Firewall, reverse proxy, TLS, dan konfigurasi MySQL production belum tersedia; temuan deployment menilai konfigurasi lokal jika dipindahkan apa adanya.
- Audit tidak membuktikan akun database dapat digunakan dari host jarak jauh.
- Tidak ada klaim validasi visual browser pada sesi ini.

## Prioritas Remediasi

1. Ganti koneksi aplikasi dari root tanpa password ke user least-privilege dan batasi exposure MySQL.
2. Terapkan rate limiter login admin dan test respons 429.
3. Invalidasi serta keluarkan `ck.txt` dari project/riwayat distribusi.
4. Perbaiki export POST/idempotency/nama file unik/unique `file_path` sebagai satu paket.
5. Terapkan allowlist HTTP/HTTPS dan host embed.
6. Tambahkan constraint domain serta invariant singleton secara bertahap setelah audit data production.
7. Finalisasi hardening production: debug off, asset build, secure cookie, header keamanan, dan cache.

## Scope Sesi 5/5

Sesi terakhir akan melakukan regresi lintas modul, deploy-readiness checklist, konsolidasi seluruh temuan 1–4, prioritas patch final, dan keputusan objektif apakah project siap deploy.
