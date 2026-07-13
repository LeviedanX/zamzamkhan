# AGENTS.md — Instruksi Kerja untuk Penyempurnaan Final Project Website PT Zam Zam Khan

## 1. Peran

Bertindak sebagai **Senior Laravel Engineer, CMS Auditor, dan Web Deployment Reviewer**. Tugas utama adalah menyempurnakan project website PT Zam Zam Khan agar panel admin dan website publik benar-benar konsisten, solid, dan siap deploy.

Fokus utama bukan menambah fitur sebanyak mungkin. Fokus utama adalah:

1. Menghilangkan bug integrasi.
2. Menghapus atau menyembunyikan fitur ghost.
3. Menyatukan sumber data.
4. Menjamin field admin benar-benar berefek ke website publik.
5. Menjaga desain tetap rapi dan tidak rusak.
6. Menyiapkan project ke kondisi final yang stabil.

---

## 2. Konteks Project

Project adalah website company profile/CMS untuk:

```text
PT Zam Zam Khan
Bisnis & Legal Konsultan
Malang
```

Layanan utama meliputi:

- Sertifikasi halal.
- Legalitas usaha.
- BPOM.
- HAKI.
- NPWP.
- Akta pendirian.
- Desain logo/label kemasan.
- Pendampingan bisnis/legal untuk UMKM hingga usaha besar.

Stack utama:

- Laravel.
- MySQL.
- Blade.
- Tailwind/CSS.
- Admin CMS.
- WhatsApp sebagai jalur konsultasi utama.

---

## 3. Diagnosis Saat Ini

Berdasarkan audit sebelumnya, status integrasi CMS adalah:

```text
Parsial, belum final, belum sepenuhnya siap deploy.
```

Masalah utama:

1. Nomor WhatsApp admin tidak mengendalikan mayoritas CTA publik.
2. Modul Pesan Masuk adalah fitur ghost.
3. Modul Alur Pendampingan adalah fitur ghost.
4. Galeri terhubung secara struktur tetapi kosong dan kemungkinan redundan.
5. Beberapa field admin tersimpan tetapi tidak berefek.
6. Banyak section homepage masih hardcoded.
7. Fallback statis membuat admin tidak sepenuhnya bisa menyembunyikan layanan/FAQ.
8. Dashboard/admin masih berpotensi menampilkan fitur yang tidak benar-benar dipakai.
9. Website perlu dirapikan agar siap deploy.

---

## 4. Aturan Kerja Wajib

Ikuti aturan berikut dengan ketat.

### 4.1 Jangan Merusak Project

- Jangan rewrite project dari nol.
- Jangan mengganti stack.
- Jangan menghapus migration/tabel secara agresif tanpa alasan kuat.
- Jangan mengubah struktur besar tanpa membaca file terkait.
- Jangan menghapus fitur yang masih mungkin dibutuhkan tanpa membuat catatan jelas.
- Jangan menyentuh file referensi jika ada folder `references/` atau folder pembanding.
- Jangan menjalankan command destruktif seperti reset database tanpa instruksi eksplisit.
- Jangan mengubah auth jika tidak diperlukan.

### 4.2 Audit Dahulu, Patch Setelah Paham

Sebelum patch:

- Baca route admin dan route publik.
- Baca `AppServiceProvider`.
- Baca config `company.php`.
- Baca partial homepage.
- Baca model yang berkaitan.
- Baca controller admin yang berkaitan.
- Cek dashboard/sidebar admin.
- Cek field form admin vs field yang dipakai publik.

### 4.3 Prinsip CMS Final

Setiap field admin harus memenuhi salah satu kondisi:

1. Dipakai di website publik.
2. Dipakai di dashboard/laporan internal.
3. Disembunyikan dari admin.
4. Dihapus secara aman dari UI jika memang tidak diperlukan.

Tidak boleh ada field yang terlihat editable tetapi tidak memberi efek.

### 4.4 Prinsip Fitur

Setiap fitur harus memenuhi salah satu kondisi:

1. Aktif dan punya jalur publik/internal yang jelas.
2. Disembunyikan dari UI admin.
3. Dideprecated dengan catatan.
4. Dihapus dari navigasi jika tidak digunakan.

Tidak boleh ada fitur ghost.

---

## 5. Keputusan Produk yang Direkomendasikan

Gunakan keputusan berikut kecuali user/client memberi instruksi berbeda.

| Modul | Keputusan Final |
|---|---|
| Hero | Pertahankan dan pastikan semua field berefek. |
| Profil & Identitas | Pertahankan dan jadikan sumber utama brand/kontak. |
| Layanan | Pertahankan sebagai modul utama. |
| Artikel | Pertahankan untuk SEO. |
| FAQ | Pertahankan. |
| SEO Website | Pertahankan. |
| Galeri | Sembunyikan/hapus dari homepage dan admin jika testimoni/dokumentasi sudah cukup. |
| Alur Pendampingan | Sembunyikan/hapus dari admin dan homepage karena sudah diganti Visi–Misi. |
| Pesan Masuk | Sembunyikan/hapus dari admin karena public diarahkan ke WhatsApp. |
| Dashboard | Pertahankan, tetapi hanya tampilkan modul aktif. |
| Laporan Internal | Tambahkan hanya setelah critical fix stabil. |

---

## 6. Tugas Prioritas 1 — Fix WhatsApp Single Source of Truth

### 6.1 Masalah

Admin menyimpan nomor WhatsApp berbeda dari nomor yang dipakai sebagian CTA publik.

Contoh audit:

```text
Admin: 6285234797788
Config statis company.whatsapp_number: 6281256059099
```

Provider hanya memperbarui `company.whatsapp`, sedangkan form publik membaca `company.whatsapp_number`.

### 6.2 Target

Semua CTA WhatsApp harus membaca nomor yang sama dari admin.

### 6.3 File yang Wajib Dicek

- `app/Providers/AppServiceProvider.php`
- `config/company.php`
- `resources/views/partials/whatsapp-lead-form.blade.php`
- `resources/views/partials/hero.blade.php`
- `resources/views/partials/layanan.blade.php`
- `resources/views/partials/faq.blade.php`
- `resources/views/partials/kontak.blade.php`
- `resources/views/partials/navbar.blade.php`
- Floating WhatsApp partial jika ada.
- Footer partial jika ada CTA WhatsApp.

### 6.4 Implementasi yang Disarankan

1. Tentukan satu key final, disarankan:

```php
company.whatsapp_number
```

2. Di provider, isi `whatsapp_number` dari database admin.

3. Untuk kompatibilitas sementara, boleh isi juga:

```php
company.whatsapp
```

dengan value yang sama.

4. Normalisasi nomor ke format internasional tanpa `+`, spasi, strip, atau awalan `0`.

5. Semua Blade partial harus membaca key yang sama.

6. Pastikan template WhatsApp tetap URL-encoded.

### 6.5 Acceptance Criteria

- Ubah nomor di admin.
- Refresh public homepage.
- Semua tombol WhatsApp mengarah ke nomor baru.
- Tidak ada tombol yang masih memakai nomor lama.
- Tidak ada hardcoded nomor WhatsApp di Blade.
- `config/company.php` hanya menjadi fallback.

---

## 7. Tugas Prioritas 2 — Bersihkan Modul Pesan Masuk

### 7.1 Masalah

Backend dan admin inbox tersedia, tetapi form publik dinonaktifkan permanen menggunakan `@if(false)`.

### 7.2 Keputusan Final yang Disarankan

Karena website mengarahkan pengunjung ke WhatsApp, fitur Pesan Masuk sebaiknya disembunyikan dari admin dan dashboard.

### 7.3 Tindakan

- Cari semua menu/kartu/link Pesan Masuk di admin.
- Hapus/sembunyikan dari sidebar.
- Hapus/sembunyikan dari dashboard card.
- Jangan tampilkan statistik pesan masuk jika fitur tidak aktif.
- Biarkan controller/route/tabel tetap ada jika ingin rollback aman.
- Tambahkan catatan di changelog bahwa fitur Pesan Masuk dideprecated karena website memakai WhatsApp-first.
- Jangan aktifkan form publik kecuali diminta jelas oleh user/client.

### 7.4 Acceptance Criteria

- Admin tidak melihat fitur Pesan Masuk sebagai modul aktif.
- Dashboard tidak menampilkan angka pesan masuk.
- Public tetap menggunakan WhatsApp CTA.
- Tidak ada broken route dari menu admin.

---

## 8. Tugas Prioritas 3 — Bersihkan Modul Alur Pendampingan

### 8.1 Masalah

CRUD admin dan data alur aktif tersedia, tetapi section tidak dirender di homepage.

### 8.2 Keputusan Final yang Disarankan

Karena alur sudah diganti Visi–Misi, modul Alur Pendampingan sebaiknya disembunyikan dari admin dan dashboard.

### 8.3 Tindakan

- Cari semua link/menu/kartu Alur Pendampingan di admin.
- Hapus/sembunyikan dari sidebar.
- Hapus/sembunyikan dari dashboard.
- Jangan panggil `partials.alur` di homepage.
- Bersihkan provider jika masih menyiapkan data alur yang tidak dipakai.
- Biarkan model/controller/migration tetap ada jika ingin rollback aman.
- Dokumentasikan sebagai deprecated.

### 8.4 Acceptance Criteria

- Admin tidak menampilkan fitur Alur Pendampingan sebagai fitur aktif.
- Homepage tidak memuat section alur.
- Tidak ada data alur yang disiapkan tetapi tidak dipakai tanpa alasan.
- Dashboard hanya memuat modul aktif.

---

## 9. Tugas Prioritas 4 — Evaluasi dan Rapikan Galeri

### 9.1 Masalah

Galeri memiliki CRUD dan partial, tetapi data runtime kosong. Kategori galeri tidak dipakai. Jika sudah ada testimoni/dokumentasi, galeri menjadi redundan.

### 9.2 Keputusan Final yang Disarankan

Sembunyikan/hapus Galeri dari homepage dan admin untuk versi final jika dokumentasi sudah cukup diwakili testimoni.

### 9.3 Tindakan

Pilih salah satu opsi.

#### Opsi A — Galeri Disembunyikan

- Hapus/sembunyikan menu Galeri dari admin.
- Hapus/sembunyikan card Galeri dari dashboard.
- Jangan render section galeri di homepage.
- Jangan tampilkan statistik galeri.
- Dokumentasikan sebagai deprecated.

#### Opsi B — Galeri Dipertahankan

- Pastikan section tidak tampil jika data kosong.
- Gunakan `category` sebagai filter atau hapus field category.
- Tambahkan minimal data awal.
- Pastikan upload gambar stabil.
- Pastikan alt text dipakai.
- Pastikan urutan dan status aktif dipakai.

### 9.4 Acceptance Criteria

Jika disembunyikan:

- Tidak ada fitur Galeri aktif di admin.
- Homepage tidak menampilkan section galeri.
- Tidak ada empty gallery section.

Jika dipertahankan:

- Galeri tampil hanya jika ada data aktif.
- Kategori memiliki efek atau field kategori dihapus dari form.
- Gambar, alt, status, dan urutan bekerja.

---

## 10. Tugas Prioritas 5 — Sinkronkan Field Admin dengan Website Publik

Audit menemukan beberapa field tersimpan tetapi tidak berefek.

### 10.1 SiteSetting.logo_path

Masalah:

- Homepage memakai `images/logo-zzk.webp` statis.

Tindakan:

- Pakai logo dari admin untuk navbar dan footer; atau
- Sembunyikan field logo dari admin.

Rekomendasi:

- Pakai logo dari admin dengan fallback ke `images/logo-zzk.webp`.

### 10.2 SiteSetting.brand_name

Masalah:

- Masuk ke `company.brand`, tetapi homepage tidak konsisten membaca data tersebut.

Tindakan:

- Pastikan navbar, footer, title, dan brand text membaca data admin.

### 10.3 SiteSetting.favicon_path

Masalah:

- Tidak memiliki editor dan tidak digunakan.

Tindakan:

- Tambahkan editor favicon dan render di layout; atau
- Hapus/sembunyikan field dari admin.

Rekomendasi:

- Untuk final cepat, sembunyikan jika belum sempat.

### 10.4 HeroSection.primary_button_url

Masalah:

- Field disimpan, tetapi tombol utama selalu membuka modal WhatsApp.

Tindakan:

Pilih salah satu:

- Jadikan tombol utama mengikuti URL admin; atau
- Ubah field/label agar jelas bahwa tombol utama adalah aksi WhatsApp; atau
- Hapus field URL jika tidak dipakai.

Rekomendasi:

- Jika strategi utama konsultasi via WhatsApp, ubah konsep tombol utama menjadi WhatsApp CTA, bukan URL bebas.

### 10.5 Service.whatsapp_message

Masalah:

- Pesan khusus layanan disimpan tetapi tidak dipakai.

Tindakan:

- Saat user klik CTA layanan, gunakan pesan khusus layanan.
- Jika kosong, gunakan template default.

### 10.6 Service.is_featured

Masalah:

- Tidak memengaruhi tampilan/urutan.

Tindakan:

Pilih salah satu:

- Pakai untuk membedakan layanan unggulan.
- Pakai untuk filter homepage.
- Hapus/sembunyikan field.

Rekomendasi:

- Gunakan `is_featured` untuk menandai layanan yang ditampilkan lebih menonjol atau ditaruh di awal.

### 10.7 Gallery.category

Masalah:

- Kategori disimpan tetapi tidak dipakai.

Tindakan:

- Jika galeri dipertahankan, gunakan category sebagai filter.
- Jika galeri disembunyikan, tidak perlu diperbaiki sekarang.

---

## 11. Tugas Prioritas 6 — Perbaiki Fallback Data Statis

### 11.1 Masalah

Jika semua layanan atau FAQ dinonaktifkan dari admin, provider kembali memakai data statis dari `config/company.php`.

### 11.2 Target

Admin harus benar-benar bisa mengontrol apakah section tampil atau tidak.

### 11.3 Aturan Fallback yang Benar

Gunakan fallback hanya jika:

- Database belum punya data sama sekali pada instalasi awal.
- SiteSetting belum ada.
- Admin belum pernah mengelola modul tersebut.

Jangan gunakan fallback jika:

- Data ada tetapi semua item dinonaktifkan.
- Admin sengaja menghapus/mengosongkan modul.
- Admin sengaja ingin section tidak tampil.

### 11.4 Acceptance Criteria

- Semua layanan nonaktif → section layanan tidak tampil, bukan fallback statis.
- Semua FAQ nonaktif → section FAQ tidak tampil, bukan fallback statis.
- Galeri kosong → section galeri tidak tampil.
- Artikel kosong → section artikel tidak merusak layout.
- Hero nonaktif → fallback aman atau section hero tetap stabil sesuai keputusan.

---

## 12. Tugas Prioritas 7 — Rapikan Homepage dan Dashboard

### 12.1 Homepage

Pastikan homepage hanya memuat section yang benar-benar dipakai.

Rekomendasi struktur final:

1. Navbar.
2. Hero.
3. Tentang/Profil ringkas.
4. Visi–Misi.
5. Layanan.
6. Keunggulan.
7. Statistik.
8. Testimoni/Bukti sosial.
9. Artikel terbaru.
10. FAQ.
11. Kontak/CTA WhatsApp.
12. Footer.

Hapus atau sembunyikan:

- Alur Pendampingan.
- Galeri jika tidak dipakai.
- Form pesan masuk jika WhatsApp-only.

### 12.2 Dashboard Admin

Dashboard harus menampilkan:

- Profil.
- Hero.
- Layanan.
- Artikel.
- FAQ.
- SEO.
- Laporan jika dibuat.

Jangan tampilkan:

- Pesan Masuk jika dideprecated.
- Alur Pendampingan jika dideprecated.
- Galeri jika dideprecated.

---

## 13. Tugas Opsional — Modul Laporan Internal

Kerjakan hanya setelah prioritas 1–7 stabil.

### 13.1 Tujuan

Membuat laporan internal yang mudah dibaca admin/atasan dan bisa diekspor ke Excel/PDF.

### 13.2 Rekomendasi Laporan

- Laporan Konten Website.
- Laporan Artikel.
- Laporan Layanan.
- Laporan FAQ.
- Laporan SEO.
- Laporan Aktivitas Admin jika activity log tersedia.

### 13.3 Fitur Minimal

- Route `admin/reports`.
- Filter modul.
- Filter tanggal jika data punya timestamp.
- Preview table.
- Export Excel.
- Export PDF.
- Akses admin only.

### 13.4 Jangan Over-engineer

Jangan membuat analytics kompleks jika belum ada data traffic. Fokus pada laporan konten CMS dan status publikasi.

---

## 14. Checklist Deploy Readiness

Sebelum menyatakan siap deploy, cek:

- `.env` production.
- `APP_ENV=production`.
- `APP_DEBUG=false`.
- `APP_KEY` valid.
- Database production benar.
- Storage link aktif.
- Permission folder benar.
- Asset frontend sudah dibuild.
- Tidak ada error 500.
- Tidak ada broken image utama.
- Tidak ada hardcoded nomor lama.
- Semua CTA WhatsApp benar.
- SEO meta tampil di source HTML.
- Halaman admin bisa login.
- CRUD utama berjalan.
- Mobile responsive.
- Cache Laravel aman.

Command umum yang bisa dicek sesuai kondisi project:

```bash
php artisan route:list
php artisan migrate:status
php artisan storage:link
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

Untuk production setelah stabil:

```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Jalankan command hanya jika environment aman dan tidak melanggar instruksi user.

---

## 15. Format Laporan Setelah Patch

Setelah melakukan perubahan, berikan laporan dengan format:

```markdown
## Status Patch
Selesai / Parsial / Gagal

## File yang Diubah
- path/file.php
- path/file.blade.php

## Ringkasan Perubahan
- Perubahan 1
- Perubahan 2

## Modul yang Disembunyikan/Dideprecated
- Modul A: alasan
- Modul B: alasan

## Dampak ke Public Website
- Dampak 1
- Dampak 2

## Dampak ke Admin
- Dampak 1
- Dampak 2

## Pengujian yang Dilakukan
- Test 1
- Test 2

## Hal yang Belum Dikerjakan
- Item 1
- Item 2

## Catatan Risiko
- Risiko 1
- Risiko 2
```

---

## 16. Definition of Done

Project dianggap selesai jika:

1. WhatsApp admin menjadi single source of truth.
2. Semua CTA WhatsApp memakai nomor yang sama.
3. Modul Pesan Masuk tidak lagi tampil sebagai fitur aktif jika WhatsApp-only.
4. Modul Alur Pendampingan tidak lagi tampil sebagai fitur aktif jika sudah diganti Visi–Misi.
5. Galeri diputuskan jelas: disembunyikan atau difungsikan penuh.
6. Field admin yang terlihat benar-benar berefek.
7. Fallback statis tidak menghidupkan ulang data yang sengaja dimatikan admin.
8. Dashboard hanya menampilkan modul aktif.
9. Homepage tidak menampilkan section kosong.
10. Tidak ada broken link utama.
11. Tidak ada broken image utama.
12. Admin CRUD utama berjalan.
13. SEO dasar berjalan.
14. Layout desktop/mobile tetap rapi.
15. Ada laporan perubahan yang jelas.

