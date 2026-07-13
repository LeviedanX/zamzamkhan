# Audit Project PT Zam Zam Khan — Sesi 5/5 dan Laporan Final

## Status

Audit 5/5 selesai. Keputusan akhir: **BELUM SIAP DEPLOY KE PRODUCTION**.

Codebase dapat dibangun dan seluruh regression test lulus, tetapi masih ada enam blocker High serta beberapa ketidakkonsistenan CMS/operasional. Status ini bukan berarti aplikasi tidak dapat dijalankan; aplikasi berjalan sehat pada environment lokal, tetapi belum memenuhi batas keamanan, konsistensi, dan deployment yang layak untuk production.

Tidak ada source code aplikasi atau data MySQL yang diubah. Build frontend production dijalankan sehingga artefak `public/build` diregenerasi. Tiga file report baru yang dibuat regression test telah dibersihkan secara terkontrol; 13 artefak lama tidak disentuh.

## Ringkasan Validasi Akhir

| Pemeriksaan | Hasil |
|---|---|
| PHPUnit penuh | **80 test, 539 assertion, seluruhnya lulus** |
| Vite production build | Lulus |
| Composer validate strict | Lulus |
| Composer platform requirements | Lulus |
| Composer security audit | 0 advisory |
| npm production audit | 0 vulnerability |
| Migration | 15/15 `Ran` |
| Route aplikasi | 89 route terdaftar |
| Config cache | Lulus |
| Route cache | Lulus |
| Blade view cache | Lulus |
| Homepage, artikel, login, sitemap, robots runtime | HTTP 200 |
| Browser desktop 1280×720 | Tidak terlihat overlap/overflow pada viewport utama |
| Browser mobile 390×844 | Tidak ada horizontal overflow; drawer berfungsi |
| Gambar homepage pada browser | 0 gambar rusak |
| Struktur heading homepage | 1 H1 |
| Console browser | 0 error, 0 warning |
| WhatsApp runtime | Satu nomor konsisten dari konfigurasi CMS saat ini |

Cache yang dibuat untuk pengujian telah dibersihkan kembali dengan `optimize:clear`.

## Keputusan Deploy

### Blocker wajib selesai sebelum production

| ID final | Severity | Temuan | Sumber audit |
|---|---|---|---|
| BLK-01 | High/Critical bila deploy | Aplikasi memakai MySQL `root` tanpa password dan privilege global; server mendengarkan semua interface | SEC-04, DB-SEC-01 |
| BLK-02 | High | Export laporan memakai GET dan dapat dipicu prefetch/partial navigation, termasuk request ganda | ADM-01 |
| BLK-03 | High | Nama/path export dapat bertabrakan; `file_path` tidak unik dan penghapusan satu histori dapat merusak histori lain | ADM-02, DB-04 |
| BLK-04 | High | Login admin tidak memiliki throttling; delapan percobaan gagal runtime tidak menghasilkan 429 | SEC-01, ADM-03, SEC-08 |
| BLK-05 | High | AdminSeeder mempunyai credential fallback yang dapat ditebak dan dapat mereset password akun existing | SEC-02, ADM-04 |
| BLK-06 | High | `ck.txt` menyimpan cookie sesi/CSRF aktual di dalam project | SEC-03, SEC-09 |

### Blocker konfigurasi deployment

Environment aktif masih development:

- `APP_ENV` bukan production;
- debug aktif;
- `public/hot` masih ada;
- template `.env.example` juga memberi default debug aktif serta akun database root tanpa password;
- secure-cookie production belum dikonfigurasi;
- header CSP/clickjacking/MIME/referrer belum terlihat pada respons aplikasi lokal maupun konfigurasi repo.

Kondisi lokal tersebut wajar untuk development, tetapi konfigurasi tidak boleh dipindahkan apa adanya ke server production.

## Register Temuan Terkonsolidasi

Temuan berulang antar-sesi digabung agar jumlah risiko tidak terduplikasi.

### High — 6 temuan terbuka

1. **Database superuser tanpa password** — dampak kompromi aplikasi dapat mencakup seluruh server MySQL.
2. **Export GET/prefetch/double request** — aksi yang menulis file dan histori dapat terjadi tanpa klik eksplisit atau dua kali dalam satu klik.
3. **Collision file export** — nama hanya presisi detik dan schema tidak memaksa path unik.
4. **Login tanpa rate limiter** — brute force dan credential stuffing tidak dibatasi aplikasi.
5. **Credential fallback pada seeder** — seeding ulang dapat menghasilkan/reset akun ke password yang diketahui.
6. **Cookie jar berada di project** — sesi yang belum kedaluwarsa dapat diambil alih bila file bocor.

### Medium — 17 kelompok temuan terbuka

| ID | Temuan | Dampak utama |
|---|---|---|
| MED-01 | Header security dan CSP belum tersedia/terverifikasi di edge | Pertahanan clickjacking, MIME sniffing, referrer, dan XSS berkurang |
| MED-02 | JSON-LD dirender raw tanpa perlindungan penutupan `script` | Nilai CMS tertentu dapat memecah konteks script |
| MED-03 | Field URL memakai rule generik, belum allowlist HTTP/HTTPS/host | FTP, `file://`, dan bentuk `data://` tertentu dapat tersimpan |
| MED-04 | Analytics tidak memiliki retensi dan mode overall memuat data ke memori | Tabel dan penggunaan memori tumbuh terus |
| MED-05 | Root Git project tidak mandiri; metadata Git efektif berada di direktori user | Review perubahan dan release dapat mencakup scope yang salah |
| MED-06 | Field kontak nullable di CMS dapat hidup kembali dari fallback config | Admin tidak benar-benar dapat mengosongkan nilai |
| MED-07 | Navigasi/form statis masih menunjuk section dinamis yang dapat disembunyikan | Anchor mati dan opsi layanan tidak sinkron |
| MED-08 | Agenda selalu merender section kosong/empty-state | Tidak sesuai prinsip homepage hanya memuat section aktif |
| MED-09 | Structured data masih memiliki nilai hardcoded/tidak sepenuhnya mengikuti CMS | SEO dapat menyajikan identitas berbeda dari halaman |
| MED-10 | Dialog publik dan modal delete belum sepenuhnya mengelola focus return/trap | Pengguna keyboard/screen reader dapat kehilangan konteks |
| MED-11 | Penggantian file media tidak atomic | Record dapat menunjuk file hilang atau meninggalkan orphan |
| MED-12 | Test export memakai private disk nyata | Artefak testing bercampur dengan storage aplikasi |
| MED-13 | GET index Agenda menjalankan purge permanen | Request read-only/prefetch dapat menghapus data |
| MED-14 | Perubahan password tidak mencabut sesi admin lain | Sesi lama tetap aktif setelah rotasi credential |
| MED-15 | Domain status/tipe/format tidak dijaga CHECK constraint database | Jalur tulis non-UI dapat menghasilkan state ilegal |
| MED-16 | Singleton setting dan satu hero aktif tidak ditegakkan schema | Provider/admin dapat membaca record yang berbeda |
| MED-17 | Konfigurasi development belum memiliki template production-safe | Risiko debug/dev asset/credential terbawa saat deployment |

### Low — 12 kelompok temuan terbuka

| ID | Temuan |
|---|---|
| LOW-01 | Tabel fitur deprecated/legacy masih berada di schema aktif |
| LOW-02 | SVG fallback artikel memakai ID gradient yang sama berulang kali |
| LOW-03 | Artikel `published` tidak membatasi tanggal publikasi masa depan |
| LOW-04 | Tidak ada skip link ke konten utama |
| LOW-05 | UI logo menawarkan SVG tetapi validator menolaknya |
| LOW-06 | Beberapa media existing tidak dapat dihapus dari form admin |
| LOW-07 | Dashboard dapat menghitung agenda kedaluwarsa sebagai aktif |
| LOW-08 | Validasi filter/display order belum konsisten antar-CRUD |
| LOW-09 | Index slug artikel redundan |
| LOW-10 | Index `(is_active, display_order)` belum konsisten pada service/FAQ |
| LOW-11 | Counter server-rendered mulai dari `0`; tanpa JS atau sebelum intersection nilai pencapaian menyesatkan |
| LOW-12 | Runtime lokal bergantung pada Vite dev marker walaupun build production tersedia |

## Verifikasi Browser Nyata

### Homepage desktop

- Hero, navbar, CTA, portrait, dan floating WhatsApp terlihat rapi pada 1280×720.
- Tidak ditemukan error/warning console.
- Gambar yang selesai dimuat seluruhnya memiliki dimensi valid.
- Nomor WhatsApp yang ditemukan browser konsisten dengan data CMS saat audit.

### Homepage mobile

- Viewport 390 px memiliki `scrollWidth` 390 px: tidak ada overflow horizontal.
- Drawer menutup hampir seluruh layar tanpa keluar viewport.
- Tombol buka/tutup mempunyai accessible name dan state expanded.
- Link Login Admin tersedia di bagian bawah drawer.
- Focus berpindah ke tombol tutup saat drawer dibuka.

### Login admin

- Layout desktop terpusat dan tidak overlap.
- Email, password, toggle password, tombol submit, dan link kembali memiliki label yang dapat dikenali browser accessibility tree.
- Halaman admin setelah login tidak diuji visual pada sesi ini karena audit tidak menerima credential dan tidak membuat akun sementara pada database runtime.
- Cakupan route, authorization, CRUD, serta dashboard setelah login tetap tervalidasi melalui Feature test pada Sesi 3 dan regresi penuh Sesi 5.

Artefak browser tersimpan di `output/playwright/session5/.playwright-cli/`.

## Definition of Done Project

| Kriteria | Status | Bukti/catatan |
|---|---|---|
| WhatsApp admin menjadi single source of truth | Lulus untuk runtime saat ini | Nomor CMS sama pada CTA yang diuji |
| Seluruh CTA memakai nomor sama | Lulus untuk data saat ini | Test integrasi dan browser lulus |
| Pesan Masuk tidak tampil sebagai fitur aktif | Lulus | Tidak ada route/menu admin aktif |
| Alur Pendampingan tidak tampil sebagai fitur aktif | Lulus | Tidak ada route/menu/home section aktif |
| Galeri diputuskan jelas | Lulus | Disembunyikan/deprecated dari fitur aktif |
| Field admin yang terlihat benar-benar berefek | **Parsial** | Nullable contact dan URL policy masih bermasalah |
| Fallback tidak menghidupkan data yang sengaja dimatikan | **Parsial** | Data utama dapat kosong, tetapi nav/form statis tetap muncul |
| Dashboard hanya menampilkan modul aktif | Lulus | Inventaris route/sidebar sinkron |
| Homepage tidak menampilkan section kosong | **Gagal** | Agenda tetap menampilkan empty-state |
| Tidak ada broken link utama | Lulus pada scope runtime yang diuji | Home, artikel, login, sitemap, robots HTTP 200 |
| Tidak ada broken image utama | Lulus | Browser menemukan 0 broken image homepage |
| CRUD utama berjalan | Lulus melalui automated test | Belum divalidasi manual dengan credential production |
| SEO dasar berjalan | **Parsial** | Meta/sitemap tersedia; structured data belum konsisten/aman penuh |
| Desktop/mobile rapi | Lulus untuk public + login | Authenticated admin visual belum diuji |
| Laporan perubahan tersedia | Lulus | Lima laporan audit tersedia |

Hasil: **11 lulus, 3 parsial, 1 gagal**. Blocker keamanan tetap membuat status keseluruhan belum siap deploy.

## Urutan Patch yang Direkomendasikan

### Batch 1 — Security dan data safety

1. Pindahkan aplikasi ke user MySQL least-privilege dengan password kuat.
2. Invalidasi sesi terkait dan keluarkan `ck.txt` dari project/distribusi.
3. Ubah export generation menjadi POST ber-CSRF dan opt-out dari seluruh prefetch.
4. Gunakan ULID/UUID untuk file export dan unique constraint `file_path`.
5. Tambahkan limiter login berbasis email ter-normalisasi + IP.
6. Hapus credential fallback seeder dan fail fast jika env admin tidak tersedia.

### Batch 2 — CMS correctness

1. Bedakan nilai `null` yang disengaja dari fallback instalasi awal.
2. Bangun navigasi dan opsi form dari section/service aktif.
3. Jangan render Agenda jika tidak ada item, sesuai keputusan produk.
4. Sinkronkan seluruh JSON-LD dengan CMS dan encode aman untuk konteks script.
5. Batasi URL ke HTTP/HTTPS dan host Maps yang disetujui.

### Batch 3 — Operasional dan UX

1. Buat penggantian media atomic dan dukung remove-media eksplisit.
2. Pindahkan filesystem test ke disk sementara/fake lalu rekonsiliasi orphan lama.
3. Pindahkan purge Agenda dari GET index ke scheduler/command saja.
4. Revoke sesi lain saat password berubah.
5. Perbaiki focus trap/return, skip link, counter progressive enhancement, index, dan constraint database.

### Batch 4 — Production hardening

1. Siapkan env production terpisah: debug off, URL final, credential unik, cookie secure pada HTTPS.
2. Deploy hasil `npm run build` dan pastikan `public/hot` tidak ikut.
3. Terapkan header keamanan di aplikasi/reverse proxy; CSP diuji bertahap sebelum enforcement.
4. Jalankan migration backup-aware, storage link, permission, cache, smoke test, dan rollback check.

## Checklist Deployment Setelah Patch

- [ ] Enam blocker High ditutup dan memiliki regression test.
- [ ] `APP_ENV=production` dan `APP_DEBUG=false`.
- [ ] `APP_KEY` unik production, tidak disalin dari development.
- [ ] User database dedicated dan least-privilege.
- [ ] Credential admin disuplai aman; seeder tidak memiliki fallback.
- [ ] `SESSION_SECURE_COOKIE=true` hanya setelah HTTPS aktif.
- [ ] `public/hot` tidak berada pada release.
- [ ] Build production dan manifest tersedia.
- [ ] Storage link dan permission tervalidasi pada server.
- [ ] Seluruh migration sukses setelah backup.
- [ ] Config, route, dan view cache sukses.
- [ ] Header keamanan diverifikasi dari domain production.
- [ ] Login throttle menghasilkan 429 pada ambang yang ditentukan.
- [ ] Export tidak dapat dipicu GET/prefetch dan tidak collision.
- [ ] Semua CTA WhatsApp memakai nomor production yang sama.
- [ ] Smoke test home, artikel, login, CRUD inti, upload, sitemap, robots, dan 404.
- [ ] Test desktop/mobile pada browser production.
- [ ] Log production tidak mengandung error atau data sensitif.
- [ ] Backup dan prosedur rollback diuji.

## File Laporan Audit

- `docs/AUDIT_SESSION_1_OF_5.md` — arsitektur, konfigurasi, route, baseline keamanan/database.
- `docs/AUDIT_SESSION_2_OF_5.md` — website publik, CMS integration, SEO, accessibility.
- `docs/AUDIT_SESSION_3_OF_5.md` — admin, CRUD, auth, upload, report, analytics.
- `docs/AUDIT_SESSION_4_OF_5.md` — security probe, dependency, database integrity, hardening.
- `docs/AUDIT_SESSION_5_OF_5.md` — regresi, browser, deploy-readiness, konsolidasi final.

## Batas Audit

- Tidak dilakukan destructive exploit, load/stress test, pentest jaringan, maupun perubahan data production.
- Reverse proxy, firewall, TLS, DNS, hosting, mail delivery, backup production, dan credential production tidak tersedia untuk diverifikasi.
- Browser authenticated admin tidak diuji manual tanpa credential; automated coverage lulus.
- Advisory dependency adalah snapshot saat audit dan harus diperiksa ulang menjelang release.
- Keputusan siap deploy harus dievaluasi kembali setelah patch dan retest, bukan hanya setelah mengganti `.env`.
