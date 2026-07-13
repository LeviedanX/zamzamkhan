# AUDIT.md — Audit Integrasi CMS dan Kesiapan Final Website PT Zam Zam Khan

> **Status pembaruan 12 Juli 2026:** temuan dalam dokumen ini adalah baseline historis.
> WhatsApp single source of truth sudah diperbaiki. Galeri, Alur Pendampingan, Pesan Masuk,
> dan form pesan lama sudah dicabut dari route/UI aktif. Field admin yang dipertahankan telah
> disambungkan ke keluaran publik atau diklasifikasikan jelas sebagai Operasional Internal/Pengaturan.

## 1. Ringkasan Eksekutif

Audit ini menyimpulkan bahwa panel admin baru **belum sepenuhnya mengendalikan website publik**. Integrasi CMS sudah berjalan untuk beberapa modul inti, tetapi statusnya masih **parsial**, bukan final. Beberapa data admin memang tersimpan di database dan tampil di dashboard, namun tidak semuanya benar-benar dipakai oleh homepage.

Masalah paling serius adalah **ketidakkonsistenan nomor WhatsApp**. Admin menyimpan nomor `6285234797788`, tetapi sebagian besar CTA WhatsApp publik masih membaca konfigurasi statis dari `config/company.php`, yaitu `6281256059099`. Ini berisiko membuat tombol konsultasi mengarah ke nomor yang salah, walaupun admin merasa sudah mengubah nomor.

Selain itu, ada dua modul admin yang secara praktis menjadi **fitur ghost**:

1. **Pesan Masuk**  
   Infrastruktur backend, route, controller, validasi, tabel, dan inbox admin tersedia, tetapi form publik dinonaktifkan permanen dengan `@if(false)`. Akibatnya, pengunjung tidak punya jalur aktif untuk mengirim pesan ke inbox admin.

2. **Alur Pendampingan**  
   CRUD admin dan data aktif tersedia, tetapi section tidak dipanggil di homepage. Artinya admin dapat mengelola data alur, tetapi data tersebut tidak terlihat di website publik.

Banyak elemen lain masih **hardcoded** di Blade/config, misalnya logo navbar/footer, section keunggulan, statistik, klien, testimoni, heading beberapa section, deskripsi footer, label navigasi, dan dekorasi visual homepage.

Secara teknis, website sudah mendekati bentuk CMS sederhana, tetapi belum layak disebut “final siap deploy” sebelum masalah integrasi, fitur ghost, konsistensi data, dan hardcoded content dibereskan.

---

## 2. Ruang Lingkup Audit

Audit ini berfokus pada hubungan antara:

- Panel admin.
- Database/content model.
- Provider/bridge data.
- Blade partials homepage.
- Jalur publik yang dilihat pengunjung.
- Field admin yang benar-benar berefek ke website publik.
- Modul yang masih statis, parsial, atau ghost.

Audit ini **tidak** menilai:

- Keamanan server produksi secara penuh.
- Performa hosting.
- Audit penetrasi.
- Kualitas SEO eksternal.
- Kualitas copywriting final bisnis.
- Legal compliance perusahaan.
- Validasi seluruh route satu per satu di browser produksi.

Mode audit: **read-only**.  
Tidak ada file, database, migration, seeder, atau konfigurasi yang diubah dalam audit ini.

---

## 3. Definisi Status Integrasi

| Status | Definisi |
|---|---|
| Terhubung | Data admin tersimpan, dibaca oleh provider/controller, dan tampil aktif di website publik. |
| Parsial | Sebagian field/modul admin tampil di publik, tetapi sebagian field lain diabaikan, fallback statis masih aktif, atau perilakunya belum konsisten. |
| Tidak terhubung | Data admin tersedia tetapi tidak dipakai oleh website publik. |
| Ghost | Fitur tampak tersedia di admin/backend, tetapi tidak punya jalur publik aktif atau tidak memberi dampak nyata ke pengguna website. |
| Monitoring | Modul hanya menampilkan ringkasan/indikator, bukan editor konten publik. |
| Hardcoded | Konten dikunci langsung di Blade/config, bukan dikelola dari admin. |

---

## 4. Data Runtime Saat Audit

| Item | Jumlah/Kondisi |
|---|---:|
| Layanan aktif | 8 |
| Tahap alur aktif | 6 |
| FAQ aktif | 5 |
| Artikel published | 3 |
| Galeri | 0 |
| Pesan masuk | 0 |
| Hero aktif | 1 |
| Konfigurasi SEO aktif | 1 |

Catatan penting:

- Enam tahap alur aktif **tidak tampil** di homepage.
- Galeri sudah memiliki CRUD dan section, tetapi data runtime kosong.
- Pesan masuk kosong karena form publik tidak aktif.
- Hero dan SEO sudah memiliki data utama, tetapi beberapa field masih tidak dimanfaatkan sempurna.

---

## 5. Matriks Integrasi Modul Admin

| Modul Admin | Status | Yang Berfungsi | Kekurangan Utama |
|---|---|---|---|
| Profil & Identitas | Parsial | Nama perusahaan, tagline, deskripsi, visi, misi, telepon, email, alamat, jam operasional, peta, dan sosial media sudah disimpan dan sebagian dipakai. | Logo dan nama brand tidak konsisten dipakai di homepage. Beberapa bagian masih membaca aset/teks statis. |
| Hero Utama | Parsial | Judul, subtitle, label tombol, tombol sekunder, badge, trust text, chips, foto, dan caption sudah tersambung. | URL tombol utama disimpan tetapi diabaikan karena tombol utama selalu diarahkan ke modal/WhatsApp. |
| Layanan | Parsial | CRUD, status aktif, urutan, judul, ikon, ringkasan, deskripsi, manfaat, target pengguna, dan alur layanan tersedia. | `whatsapp_message` dan `is_featured` tersimpan tetapi belum memberi efek nyata pada tampilan/perilaku publik. |
| Alur Pendampingan | Ghost / Tidak terhubung | CRUD admin dan data aktif tersedia. | Section `partials.alur` tidak dirender dari homepage. Jika alur sudah diganti oleh visi-misi, modul ini sebaiknya dihapus atau disembunyikan dari admin. |
| Artikel | Terhubung | Tiga artikel published terbaru tampil otomatis. | Heading section masih statis. Kategori artikel tidak memiliki CRUD admin. |
| FAQ | Terhubung | Pertanyaan, jawaban, status aktif, dan urutan dipakai. | Heading dan CTA section masih statis. |
| Galeri | Terhubung secara struktur, tetapi kosong | CRUD gambar, judul, alt, status, dan urutan tersedia. | Data runtime kosong. Kategori galeri tidak digunakan. Jika sudah ada testimoni/dokumentasi, modul galeri berpotensi redundan. |
| SEO Website | Terhubung | Meta title, meta description, keywords, OG, dan canonical sudah tersedia. | Tidak ditemukan gap utama dari data audit, tetapi tetap perlu verifikasi di source HTML final. |
| Pesan Masuk | Ghost | Inbox, status, dan hapus pesan tersedia. Route/controller/tabel tersedia. | Form publik dinonaktifkan permanen, sehingga pengunjung tidak dapat mengirim pesan ke inbox. |
| Dashboard | Monitoring | Menampilkan ringkasan data. | Bukan editor konten. Perlu memastikan angka dashboard membaca data yang relevan setelah modul ghost dihapus/disembunyikan. |

---

## 6. Temuan Kritis

### 6.1 Nomor WhatsApp Admin Tidak Menjadi Single Source of Truth

**Severity:** Critical  
**Jenis masalah:** Data mismatch / konfigurasi tidak konsisten  
**Dampak:** CTA konsultasi publik dapat mengarah ke nomor yang salah.

#### Kondisi Saat Audit

Admin menyimpan nomor WhatsApp:

```text
6285234797788
```

Namun form konsultasi WhatsApp membaca:

```text
company.whatsapp_number = 6281256059099
```

Sementara provider hanya memperbarui:

```text
company.whatsapp
```

bukan:

```text
company.whatsapp_number
```

Akibatnya, tombol hero, layanan, FAQ, floating WhatsApp, dan kontak cepat berpotensi membaca nomor berbeda dari nomor yang sudah diatur admin.

#### Bukti File

- `resources/views/partials/whatsapp-lead-form.blade.php` line 2
- `app/Providers/AppServiceProvider.php` line 80
- `config/company.php` line 4

#### Analisis Akar Masalah

Masalah ini muncul karena ada dua key yang mirip tetapi tidak disatukan:

- `company.whatsapp`
- `company.whatsapp_number`

Satu key diperbarui dari database, sementara key lain masih berasal dari config statis. Blade partial menggunakan key yang berbeda dari key yang diperbarui provider.

#### Risiko Jika Tidak Diperbaiki

- Pengunjung menghubungi nomor lama/salah.
- Admin merasa sudah mengubah nomor, tetapi perubahan tidak berdampak.
- Client bisa menganggap sistem admin tidak berfungsi.
- Data lead/konsultasi bisa masuk ke pihak yang tidak seharusnya.
- Fatal untuk website bisnis karena WhatsApp adalah jalur konversi utama.

#### Rekomendasi Perbaikan

Gunakan satu sumber data utama untuk WhatsApp. Rekomendasi paling aman:

1. Jadikan `SiteSetting.whatsapp` atau field sejenis di database sebagai sumber utama.
2. Di `AppServiceProvider`, map data admin ke **dua key sementara** untuk kompatibilitas:

```php
'whatsapp' => $siteSetting->whatsapp,
'whatsapp_number' => $siteSetting->whatsapp,
```

3. Setelah semua Blade diperiksa, pilih satu key final, misalnya:

```php
company.whatsapp_number
```

4. Update semua partial agar memakai key yang sama.
5. Tambahkan fallback hanya jika database kosong, bukan untuk menimpa data admin.
6. Normalisasi nomor ke format internasional Indonesia tanpa `+`, spasi, atau strip.

#### Acceptance Criteria

- Mengubah nomor WhatsApp di admin langsung mengubah semua CTA publik.
- Tidak ada lagi CTA publik yang membaca nomor statis berbeda.
- `config/company.php` hanya menjadi fallback, bukan sumber utama.
- Tombol hero, layanan, FAQ, kontak, navbar, dan floating WhatsApp mengarah ke nomor yang sama.

---

### 6.2 Modul Pesan Masuk Adalah Fitur Ghost

**Severity:** High  
**Jenis masalah:** Modul admin tidak punya jalur publik aktif  
**Dampak:** Admin memiliki inbox, tetapi pengunjung tidak bisa mengisi pesan dari website.

#### Kondisi Saat Audit

Fitur pesan masuk memiliki:

- Route.
- Controller publik.
- Controller admin.
- Validasi.
- Tabel database.
- Inbox admin.
- Fitur status dan hapus pesan.

Namun form publik dibungkus:

```blade
@if(false)
```

Artinya form tidak akan pernah dirender.

#### Bukti File

- `resources/views/partials/kontak.blade.php` line 102
- `app/Http/Controllers/ContactController.php`
- `app/Http/Controllers/Admin/MessageController.php`

#### Analisis

Secara UI/UX, website saat ini mengarahkan user ke WhatsApp. Jika keputusan produk memang “semua konsultasi langsung ke WhatsApp”, maka modul pesan masuk tidak diperlukan. Mempertahankan modul ini di admin hanya menambah kebingungan.

#### Rekomendasi Produk

Ada dua opsi:

##### Opsi A — Hapus/Sembunyikan Pesan Masuk dari Admin

Ini opsi yang lebih disarankan jika strategi website adalah WhatsApp-first.

Tindakan:

- Hapus item menu “Pesan Masuk” dari sidebar/dashboard admin.
- Hapus kartu monitoring pesan masuk dari dashboard jika tidak dipakai.
- Biarkan tabel/controller tetap ada sementara untuk keamanan rollback, tetapi jangan tampilkan sebagai fitur aktif.
- Hapus dokumentasi fitur pesan masuk dari README final.
- Pastikan CTA publik semuanya ke WhatsApp.

##### Opsi B — Aktifkan Form Publik

Dipilih hanya jika client benar-benar ingin inbox internal.

Tindakan:

- Hilangkan `@if(false)`.
- Aktifkan form kontak.
- Tambahkan proteksi spam.
- Tambahkan validasi server-side.
- Tambahkan status unread/read.
- Pastikan notifikasi admin jelas.
- Pastikan privasi data user dijelaskan.

#### Putusan yang Direkomendasikan

Untuk proyek ini, **hapus/sembunyikan Pesan Masuk dari admin**. Alasannya:

- Homepage sudah memakai WhatsApp sebagai jalur utama.
- User langsung diarahkan ke WhatsApp.
- Inbox internal menambah maintenance.
- Client kemungkinan lebih butuh lead cepat daripada inbox CMS.
- Modul ghost membuat sistem terlihat belum rapi.

---

### 6.3 Modul Alur Pendampingan Adalah Fitur Ghost

**Severity:** High  
**Jenis masalah:** CRUD aktif tetapi section tidak ditampilkan  
**Dampak:** Admin bisa mengedit data yang tidak pernah terlihat publik.

#### Kondisi Saat Audit

- Enam tahap alur aktif tersedia.
- CRUD admin tersedia.
- Bridge ke `company.process` tersedia.
- Partial `partials.alur` ada.

Namun `home.blade.php` tidak memanggil section alur.

#### Bukti File

- `resources/views/home.blade.php` line 6
- `resources/views/partials/alur.blade.php` line 14
- `app/Http/Controllers/Admin/ProcessStepController.php`

#### Analisis

Modul ini tampaknya tertinggal dari struktur website lama. Jika struktur baru sudah mengganti “Alur Pendampingan” dengan “Visi–Misi”, maka fitur alur sebaiknya tidak dipertahankan di admin.

Masalah utamanya bukan karena CRUD rusak, tetapi karena **fitur tidak memiliki tujuan produk yang jelas**.

#### Rekomendasi

Jika keputusan final adalah “Alur Pendampingan tidak dipakai lagi”:

- Hapus/sembunyikan menu Alur Pendampingan dari admin.
- Hapus kartu dashboard terkait alur.
- Jangan panggil `partials.alur` di homepage.
- Jangan hapus migration/tabel secara agresif jika proyek masih dalam fase finalisasi.
- Tandai modul sebagai deprecated di dokumentasi internal.
- Bersihkan provider jika masih mengirim data alur yang tidak dipakai.

Jika client berubah pikiran dan ingin alur tampil:

- Render kembali `partials.alur` di homepage.
- Pastikan desainnya konsisten dengan section lain.
- Pastikan admin benar-benar dapat mengatur urutan, status aktif, judul, dan deskripsi.
- Hilangkan fallback statis yang membuat admin tidak bisa menyembunyikan section.

#### Putusan yang Direkomendasikan

Karena alur sudah diganti oleh visi–misi, **hapus/sembunyikan modul Alur Pendampingan dari admin dan dashboard** agar CMS tidak membingungkan.

---

## 7. Field Admin yang Tersimpan tetapi Tidak Berefek

Berikut field yang tersedia atau tersimpan, tetapi tidak memberi dampak nyata pada website publik sesuai audit.

| Field | Kondisi | Dampak | Rekomendasi |
|---|---|---|---|
| `SiteSetting.logo_path` | Homepage memakai `images/logo-zzk.webp` statis. | Admin merasa dapat mengubah logo, tetapi publik tetap memakai logo statis. | Bind logo admin ke navbar/footer, atau hapus field logo dari admin. |
| `SiteSetting.brand_name` | Masuk ke `company.brand`, tetapi homepage tidak konsisten membacanya. | Nama brand di admin tidak selalu mengubah tampilan publik. | Standarkan semua brand text membaca data admin. |
| `SiteSetting.favicon_path` | Tidak memiliki editor dan tidak digunakan. | Field mati. | Tambahkan editor favicon atau hapus dari model/admin. |
| `HeroSection.primary_button_url` | Disimpan tetapi tombol utama selalu membuka modal WhatsApp. | Field membingungkan. | Jadikan tombol mengikuti URL admin, atau ubah label field menjadi aksi WhatsApp. |
| `Service.whatsapp_message` | Disimpan dan diteruskan provider, tetapi tidak digunakan tombol layanan. | Pesan khusus per layanan tidak berefek. | Gunakan saat tombol layanan diklik, atau hapus field. |
| `Service.is_featured` | Disimpan tetapi tidak memengaruhi urutan/tampilan. | Field tidak berguna. | Jadikan filter “layanan unggulan” atau hapus field. |
| `Gallery.category` | Tersimpan tetapi tidak dipakai sebagai filter/tampilan. | Kategori tidak memberi nilai. | Tambahkan filter kategori atau hapus field. |

Prinsip finalisasi:

> Jangan ada field admin yang terlihat bisa diedit tetapi tidak memengaruhi website. Field semacam itu harus dipakai, diubah labelnya, atau dihapus/disembunyikan.

---

## 8. Section yang Masih Statis/Hardcoded

Masih belum tersedia editor admin untuk beberapa elemen berikut.

### 8.1 Navbar

Belum tersedia editor untuk:

- Menu navbar.
- Urutan menu.
- Label menu.
- Logo navbar.
- Link CTA utama.
- Brand name yang sepenuhnya dinamis.

File sumber utama:

- `resources/views/partials/navbar.blade.php`

### 8.2 Tentang Kami

Belum tersedia editor penuh untuk:

- Gambar section.
- Heading section.
- Checklist.
- Mini-statistik.
- CTA.
- Komposisi visual.

### 8.3 Visi–Misi

Belum tersedia editor penuh untuk:

- Heading section.
- Intro section.
- Framework.
- Tiga pilar visi–misi.
- Dekorasi visual section.

File sumber utama:

- `resources/views/partials/visi-misi.blade.php`

### 8.4 Keunggulan

Seluruh section masih hardcoded.

File sumber utama:

- `resources/views/partials/keunggulan.blade.php`

### 8.5 Statistik Utama

Sebagian besar statistik utama masih hardcoded.

File sumber utama:

- `resources/views/partials/statistik.blade.php`

### 8.6 Klien

Daftar dan logo klien masih hardcoded.

File sumber utama:

- `resources/views/partials/klien.blade.php`

### 8.7 Testimoni

Seluruh testimoni dan dokumentasinya masih hardcoded.

File sumber utama:

- `resources/views/partials/testimoni.blade.php`

### 8.8 Galeri

Section galeri sudah memiliki struktur CRUD, tetapi:

- Data runtime kosong.
- Kategori tidak dipakai.
- Jika testimoni sudah memuat dokumentasi, galeri menjadi redundan.

File sumber utama:

- `resources/views/partials/galeri.blade.php`

### 8.9 Footer

Belum tersedia editor penuh untuk:

- Deskripsi footer.
- Kota.
- Copyright.
- Label navigasi.
- Link footer.
- Logo/footer brand.

### 8.10 Background dan Dekorasi Visual

Dekorasi visual homepage masih berada di Blade/CSS, bukan admin. Ini wajar jika dianggap bagian desain, tetapi perlu dibedakan dari konten bisnis yang seharusnya editable.

---

## 9. Perilaku Fallback yang Perlu Diperbaiki

Saat seluruh layanan atau FAQ dinonaktifkan/dihapus dari admin, homepage tidak otomatis kosong atau hilang. Provider kembali memakai data statis dari `config/company.php`.

### Masalah

Ini membuat admin tidak sepenuhnya mengontrol section. Jika admin menonaktifkan semua layanan, seharusnya website:

- Menyembunyikan section, atau
- Menampilkan empty state internal, bukan publik, atau
- Tetap kosong sesuai keputusan admin.

Namun sistem justru menghidupkan kembali data statis.

### Dampak

- Admin tidak bisa menyembunyikan section dari publik.
- Data lama bisa muncul kembali tanpa disadari.
- CMS terasa tidak dapat dipercaya.
- Sulit membedakan data database dan data fallback.

### Rekomendasi

Gunakan fallback hanya untuk kondisi instalasi awal, bukan saat admin sengaja mengosongkan data.

Logika yang lebih benar:

1. Jika database belum punya konfigurasi sama sekali, pakai fallback config.
2. Jika database sudah punya konfigurasi dan admin menonaktifkan data, hormati keputusan admin.
3. Jika collection kosong karena semua item nonaktif, hide section dari homepage.
4. Jangan menampilkan data statis jika admin sudah mulai mengelola modul tersebut.

---

## 10. Evaluasi Galeri: Perlu Dipertahankan atau Dihapus?

### Kondisi

- Galeri memiliki CRUD.
- Section galeri ada.
- Data runtime kosong.
- Kategori tidak digunakan.
- Website juga memiliki testimoni/dokumentasi yang berpotensi memenuhi fungsi yang sama.

### Analisis

Jika galeri hanya berisi dokumentasi visual, sedangkan testimoni sudah memiliki dokumentasi, galeri akan membuat admin lebih berat tanpa manfaat besar. Dalam website company profile jasa konsultasi, section yang lebih penting biasanya:

1. Hero.
2. Layanan.
3. Legalitas/profil perusahaan.
4. Keunggulan.
5. Testimoni/bukti sosial.
6. Artikel/edukasi.
7. FAQ.
8. CTA WhatsApp.

Galeri umum sering menjadi section sekunder. Jika tidak ada strategi konten yang jelas, galeri biasanya menjadi kosong atau berisi foto tidak terawat.

### Rekomendasi

Untuk finalisasi cepat dan rapi:

- **Hapus/sembunyikan Galeri dari homepage dan admin** jika dokumentasi sudah cukup diwakili testimoni.
- Jika client tetap ingin dokumentasi, ubah konsep “Galeri” menjadi “Dokumentasi & Aktivitas” dan pastikan:
  - Ada minimal 6–9 gambar awal.
  - Kategori dipakai sebagai filter.
  - Admin dapat mengatur urutan dan status.
  - Empty state publik tidak tampil jika data kosong.

### Putusan yang Direkomendasikan

Karena data galeri kosong dan sudah ada testimoni, **lebih baik galeri dihapus/disembunyikan untuk versi final**. Ini membuat admin lebih fokus dan mengurangi fitur yang tidak dipakai.

---

## 11. Evaluasi Pesan Masuk: Perlu Dipertahankan atau Dihapus?

### Kondisi

- Form publik tidak aktif.
- Website mengarahkan user ke WhatsApp.
- Inbox admin tersedia tetapi tidak menerima pesan.
- Data runtime pesan masuk = 0.

### Analisis

Untuk bisnis konsultasi seperti PT Zam Zam Khan, WhatsApp jauh lebih praktis daripada inbox internal. Pengunjung cenderung ingin respons cepat. Admin juga lebih mudah menangani lead dari WhatsApp dibanding membuka dashboard CMS hanya untuk membaca pesan.

### Rekomendasi

Hapus/sembunyikan fitur Pesan Masuk dari UI admin untuk versi final.

Yang tetap perlu dipertahankan:

- WhatsApp CTA yang konsisten.
- Template pesan WhatsApp yang rapi.
- Nomor WhatsApp dari admin.
- Tracking manual melalui laporan internal jika diperlukan.

---

## 12. Evaluasi Alur Pendampingan: Perlu Dipertahankan atau Dihapus?

### Kondisi

- CRUD alur tersedia.
- Data alur aktif.
- Section tidak tampil.
- Struktur homepage baru sudah memakai visi–misi.

### Analisis

Alur Pendampingan bisa berguna jika website ingin menjelaskan proses kerja. Namun jika layanan sudah menjelaskan proses masing-masing, section ini bisa berlebihan.

### Rekomendasi

Untuk versi final, ada dua skenario:

#### Jika fokus homepage adalah ringkas dan modern

- Hapus/sembunyikan Alur Pendampingan.
- Gunakan Visi–Misi dan Layanan sebagai struktur utama.
- Jangan tampilkan fitur alur di admin.

#### Jika client ingin menjelaskan proses konsultasi

- Aktifkan kembali section Alur.
- Buat desain lebih ringkas.
- Pastikan data admin mengendalikan semua item.

### Putusan yang Direkomendasikan

Karena alur sudah diganti visi–misi, **sembunyikan/hapus dari admin dan homepage**.

---

## 13. Rekomendasi Arsitektur Konten Final

Untuk website final yang solid, struktur admin sebaiknya disederhanakan menjadi modul yang benar-benar dipakai.

### 13.1 Modul yang Dipertahankan

| Modul | Alasan |
|---|---|
| Dashboard | Monitoring ringkas untuk admin. |
| Profil & Identitas | Sumber utama nama perusahaan, kontak, alamat, sosial media, visi, misi, dan informasi dasar. |
| Hero Utama | Bagian paling penting untuk first impression dan CTA. |
| Layanan | Konten bisnis utama. |
| Artikel | Mendukung SEO dan edukasi pengunjung. |
| FAQ | Mengurangi pertanyaan berulang dan membantu konversi. |
| SEO Website | Wajib untuk meta dan share preview. |
| Testimoni/Bukti Sosial | Penting untuk trust, meskipun saat ini masih hardcoded. |
| Laporan Internal | Berguna jika client meminta laporan admin ke atasan. |

### 13.2 Modul yang Disarankan Dihapus/Disembunyikan

| Modul | Alasan |
|---|---|
| Pesan Masuk | Ghost; jalur publik diarahkan ke WhatsApp. |
| Alur Pendampingan | Ghost; sudah diganti visi–misi. |
| Galeri | Data kosong dan redundan jika testimoni/dokumentasi sudah ada. |

### 13.3 Modul yang Bisa Ditambahkan Jika Waktu Cukup

| Modul | Prioritas | Catatan |
|---|---|---|
| Section Settings | Medium | Untuk heading/deskripsi setiap section agar tidak hardcoded. |
| Testimoni CMS | Medium | Jika client sering update bukti sosial. |
| Klien CMS | Low-Medium | Jika daftar klien perlu sering diganti. |
| Laporan Internal | Medium-High | Jika client meminta export Excel/PDF. |
| Media Library | Low | Tidak wajib untuk company profile kecil. |

---

## 14. Rekomendasi Laporan Internal Admin

Client sempat meminta laporan admin yang dapat diekspor ke Excel/PDF dan hanya diakses internal. Modul ini masuk akal, tetapi jangan dibuat terlalu abstrak.

### 14.1 Tujuan Modul Laporan

Modul laporan sebaiknya menjawab kebutuhan praktis:

- Apa saja konten yang aktif/nonaktif?
- Berapa artikel published/draft?
- Layanan apa saja yang aktif?
- FAQ apa saja yang aktif?
- Data SEO apa yang sedang digunakan?
- Update konten terakhir kapan?
- Siapa admin yang mengubah data jika activity log tersedia?
- Ringkasan performa konten internal, bukan analytics publik kompleks.

### 14.2 Rekomendasi Jenis Laporan

| Laporan | Isi | Export |
|---|---|---|
| Laporan Konten Website | Hero, layanan, FAQ, artikel, profil, SEO, status aktif/nonaktif. | Excel/PDF |
| Laporan Artikel | Judul, status, kategori, tanggal publish, penulis, slug. | Excel/PDF |
| Laporan Layanan | Nama layanan, status, urutan, ringkasan, target pengguna. | Excel/PDF |
| Laporan FAQ | Pertanyaan, status, urutan. | Excel/PDF |
| Laporan SEO | Meta title, meta description, canonical, OG title/description. | PDF |
| Laporan Aktivitas Admin | Riwayat create/update/delete jika activity log dibuat. | Excel/PDF |

### 14.3 Rekomendasi Implementasi Teknis

Minimal:

- Halaman `admin/reports`.
- Filter tanggal.
- Filter modul.
- Tombol export Excel.
- Tombol export PDF.
- Hanya bisa diakses admin internal.
- Data export tidak perlu publik.

Library Laravel yang umum:

- Excel: `maatwebsite/excel`.
- PDF: `barryvdh/laravel-dompdf` atau generator PDF setara.

Catatan: sebelum menambah package, pastikan environment project stabil dan client benar-benar membutuhkan export. Jangan menambah dependency jika hanya butuh tampilan laporan sederhana.

---

## 15. Prioritas Perbaikan

### Prioritas 1 — Wajib Sebelum Deploy

1. Perbaiki mismatch nomor WhatsApp.
2. Putuskan dan rapikan modul ghost:
   - Pesan Masuk.
   - Alur Pendampingan.
3. Pastikan semua CTA WhatsApp memakai nomor admin yang sama.
4. Pastikan field admin yang tampil benar-benar berefek.
5. Hilangkan fallback statis yang melawan keputusan admin.
6. Pastikan section kosong tidak tampil di publik.
7. Cek semua link navbar dan CTA.

### Prioritas 2 — Penting untuk CMS yang Rapi

1. Bind logo dan brand name dari admin, atau hapus field tersebut.
2. Rapikan field hero `primary_button_url`.
3. Gunakan `Service.whatsapp_message` atau hapus field.
4. Gunakan `Service.is_featured` atau hapus field.
5. Rapikan galeri: hapus/sembunyikan atau aktifkan dengan data dan filter.
6. Jadikan heading section penting editable, minimal untuk FAQ, artikel, layanan, dan kontak.

### Prioritas 3 — Penyempurnaan Setelah Stabil

1. Testimoni CMS.
2. Klien CMS.
3. Section settings global.
4. Laporan internal export Excel/PDF.
5. Activity log admin.
6. Audit accessibility.
7. Optimasi performa gambar.
8. Final SEO on-page.

---

## 16. Roadmap Penyempurnaan Menuju Final

### Phase 0 — Backup dan Pemeriksaan Awal

- Backup project.
- Backup database.
- Cek `.env`.
- Cek route list.
- Cek migration status.
- Cek storage link.
- Cek apakah ada file hasil generate sebelumnya yang tidak perlu.
- Jangan menghapus migration/tabel tanpa alasan kuat.

### Phase 1 — Fix Konfigurasi Kritis

Target:

- WhatsApp menjadi single source of truth.
- Semua CTA membaca nomor yang sama.
- Nomor admin benar-benar mengontrol publik.

Checklist:

- `AppServiceProvider`.
- `config/company.php`.
- `whatsapp-lead-form.blade.php`.
- `hero.blade.php`.
- `layanan.blade.php`.
- `faq.blade.php`.
- `kontak.blade.php`.
- `navbar.blade.php`.
- Floating WhatsApp.

### Phase 2 — Bersihkan Fitur Ghost

Target:

- Tidak ada fitur admin yang tidak berdampak.

Tindakan:

- Sembunyikan/hapus Pesan Masuk dari sidebar/dashboard jika WhatsApp-only.
- Sembunyikan/hapus Alur Pendampingan dari sidebar/dashboard jika tidak dipakai.
- Rapikan route/admin card agar tidak menampilkan fitur mati.
- Update README/history log.

### Phase 3 — Sinkronkan Field Admin dengan Public

Target:

- Semua field admin jelas efeknya.

Tindakan:

- Logo admin dipakai publik atau field disembunyikan.
- Brand name admin dipakai navbar/footer/title.
- Favicon dipakai atau field dihapus.
- Hero primary button URL dipakai atau field diganti konsep.
- Service WhatsApp message dipakai per layanan.
- Service featured dipakai atau field dihapus.
- Gallery category dipakai atau field dihapus.

### Phase 4 — Rapikan Homepage Final

Target:

- Homepage tidak memuat section kosong.
- Tidak ada data statis yang bertabrakan dengan admin.
- CTA konsisten.
- Layout tetap rapi di desktop dan mobile.

Tindakan:

- Kondisikan section berdasarkan data aktif.
- Hapus/komentari section yang tidak dipakai.
- Pastikan galeri tidak muncul jika kosong.
- Pastikan artikel tidak rusak jika belum ada artikel.
- Pastikan FAQ tidak memunculkan fallback lama jika admin menonaktifkan semua FAQ.
- Pastikan layanan tidak memunculkan fallback lama jika admin menonaktifkan semua layanan.

### Phase 5 — Deploy Hardening

Target:

- Siap deploy secara teknis.

Checklist umum Laravel:

- `.env` produksi benar.
- `APP_ENV=production`.
- `APP_DEBUG=false`.
- `APP_KEY` valid.
- Database production tersambung.
- Storage link aktif.
- Permission `storage/` dan `bootstrap/cache/` benar.
- `php artisan config:cache`.
- `php artisan route:cache` jika route kompatibel.
- `php artisan view:cache`.
- `composer install --no-dev --optimize-autoloader`.
- Asset frontend sudah build.
- Tidak ada error 500.
- Tidak ada broken image.
- Tidak ada CTA ke nomor lama.
- Meta SEO tampil di source HTML.
- Sitemap/robots dicek jika tersedia.

---

## 17. Checklist Pengujian Manual

### 17.1 Admin

- Login admin berhasil.
- Dashboard tampil tanpa error.
- Edit profil berhasil.
- Edit nomor WhatsApp berhasil.
- Edit hero berhasil.
- Upload/update gambar hero berhasil.
- Tambah/edit/nonaktifkan layanan berhasil.
- Tambah/edit/nonaktifkan FAQ berhasil.
- Tambah/edit artikel published/draft berhasil.
- Edit SEO berhasil.
- Modul ghost tidak tampil jika sudah diputuskan dihapus/disembunyikan.
- Tidak ada tombol admin yang menuju halaman kosong.

### 17.2 Public Homepage

- Navbar tampil rapi.
- Logo benar.
- Brand name benar.
- Hero membaca data admin.
- CTA WhatsApp membaca nomor admin.
- Layanan tampil sesuai data aktif.
- FAQ tampil sesuai data aktif.
- Artikel terbaru tampil sesuai published terbaru.
- Section galeri tidak tampil jika kosong/dihapus.
- Section alur tidak tampil jika sudah diganti visi–misi.
- Kontak menampilkan nomor/email/alamat dari admin.
- Footer konsisten dengan data admin.
- Mobile responsive.

### 17.3 WhatsApp CTA

Uji semua titik:

- Tombol hero.
- Tombol navbar.
- Tombol layanan.
- Tombol FAQ.
- Tombol kontak.
- Floating WhatsApp.
- Mini form konsultasi.
- CTA footer jika ada.

Semua harus mengarah ke nomor yang sama dan template pesan yang benar.

### 17.4 Data Kosong

Uji skenario:

- Semua layanan nonaktif.
- Semua FAQ nonaktif.
- Tidak ada artikel published.
- Galeri kosong.
- Hero tidak aktif.
- SEO kosong.

Website tidak boleh rusak. Section kosong harus disembunyikan atau menampilkan fallback yang disengaja, bukan fallback statis lama yang membingungkan.

---

## 18. Acceptance Criteria Final

Website dapat dianggap siap deploy jika memenuhi seluruh kondisi berikut:

1. Admin menjadi sumber utama untuk konten penting.
2. Tidak ada mismatch nomor WhatsApp.
3. Semua CTA publik mengarah ke nomor yang sama.
4. Tidak ada modul ghost yang tampil di admin.
5. Field admin yang terlihat benar-benar berefek ke publik.
6. Section yang tidak dipakai dihapus/disembunyikan.
7. Section kosong tidak tampil berantakan.
8. Fallback statis tidak melawan keputusan admin.
9. Homepage stabil di desktop dan mobile.
10. SEO meta dasar tampil benar.
11. Tidak ada broken image utama.
12. Tidak ada link mati di navbar/footer/CTA.
13. Dashboard admin hanya menampilkan modul yang benar-benar digunakan.
14. Project bisa dijalankan tanpa error fatal.
15. Dokumentasi final menjelaskan modul aktif dan modul yang sengaja dinonaktifkan.

---

## 19. Putusan Audit Final

Status integrasi CMS saat ini adalah:

```text
Parsial, belum final, belum sepenuhnya siap deploy.
```

Kesimpulan detail:

- Hero, Profil, Layanan, Artikel, FAQ, Galeri, dan SEO sudah memiliki tingkat koneksi tertentu.
- SEO relatif paling aman dari gap besar berdasarkan audit.
- Alur Pendampingan dan Pesan Masuk adalah fitur ghost.
- Galeri secara struktur tersedia tetapi kosong dan berpotensi redundan.
- Banyak konten presentasional masih hardcoded.
- Nomor WhatsApp memiliki mismatch serius dan wajib diperbaiki sebelum deploy.
- Tidak semua field admin yang terlihat dapat diedit benar-benar memengaruhi website publik.
- Admin belum sepenuhnya menjadi CMS yang kredibel karena masih ada fallback dan hardcoded content yang menyalip data admin.

Rekomendasi strategis:

> Untuk versi final, sederhanakan CMS. Pertahankan modul yang benar-benar digunakan, hilangkan fitur ghost, jadikan WhatsApp sebagai jalur konsultasi utama yang konsisten, dan pastikan semua field admin yang tersisa benar-benar mengontrol website publik.

---

## 20. Keputusan Produk yang Direkomendasikan

Berikut keputusan produk yang paling rasional untuk menyelesaikan project dengan rapi:

| Area | Keputusan |
|---|---|
| Galeri | Hapus/sembunyikan jika testimoni/dokumentasi sudah cukup. |
| Alur Pendampingan | Hapus/sembunyikan karena sudah diganti visi–misi. |
| Pesan Masuk | Hapus/sembunyikan karena jalur publik diarahkan ke WhatsApp. |
| WhatsApp | Jadikan single source of truth dari admin. |
| Layanan | Pertahankan sebagai modul utama. |
| FAQ | Pertahankan. |
| Artikel | Pertahankan untuk SEO. |
| SEO | Pertahankan. |
| Profil & Identitas | Pertahankan dan perlu dirapikan agar logo/brand benar-benar dinamis. |
| Testimoni | Pertahankan sebagai bukti sosial; boleh tetap statis jika waktu terbatas, tetapi lebih baik dibuat CMS jika client sering update. |
| Laporan Internal | Tambahkan setelah integrasi utama stabil, bukan sebelum critical fix selesai. |

---

## 21. Catatan untuk Developer Lanjutan

Developer berikutnya harus memegang prinsip berikut:

1. Jangan menambah fitur baru sebelum mismatch WhatsApp dan fitur ghost selesai.
2. Jangan mempertahankan menu admin yang tidak punya efek ke publik.
3. Jangan membuat field admin palsu.
4. Jangan memakai fallback statis kecuali untuk initial install.
5. Jangan menghapus migration lama secara sembrono.
6. Jangan mengubah desain besar-besaran sebelum integrasi data stabil.
7. Setelah setiap perubahan, uji dari admin ke public.
8. Setiap modul harus punya keputusan jelas: dipakai, disembunyikan, atau dihapus.
9. Semua perubahan harus dicatat dalam history log.
10. Finalisasi harus mengutamakan stabilitas, bukan menambah banyak fitur.
