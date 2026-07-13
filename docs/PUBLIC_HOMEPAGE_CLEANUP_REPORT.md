# Public Homepage Cleanup Report

## Ringkasan Perubahan

- Homepage dibuat lebih ringkas dengan menghapus render section Edukasi Layanan besar dan Alur Pendampingan global.
- Section layanan dirapikan agar card, icon, watermark, dan tombol memakai sistem visual maroon yang konsisten.
- Section kontak diubah menjadi kontak cepat sebagai default, dengan form lengkap tetap tersedia di accordion collapsed.
- CTA WhatsApp diprioritaskan sebagai jalur konversi utama.
- Navbar dicek agar tidak memiliki anchor mati setelah section Edukasi dan Alur tidak lagi dirender.

## Section yang Tidak Lagi Dirender di Homepage

- `partials.alur` tidak lagi dipanggil dari `resources/views/home.blade.php`.
- `partials.edukasi` tidak lagi dipanggil dari `resources/views/home.blade.php`.
- File partial dan data config terkait tetap dibiarkan agar tidak mengganggu backend atau penggunaan lain.

## Perubahan Section Layanan

- Semua card layanan memakai aksen maroon/burgundy yang seragam.
- Variasi warna per card yang terlalu ramai dihilangkan.
- Watermark icon dipertahankan dengan opacity lebih lembut.
- Icon box dibuat konsisten dari sisi ukuran, warna, border, dan hover.
- Ditambahkan micro-copy bahwa detail dan alur layanan tersedia melalui tombol Detail.
- Modal detail layanan tetap digunakan dan dirapikan dengan block visual untuk "Cocok untuk", "Manfaat utama", dan "Alur Layanan".

## Button Detail dan Konsultasikan

- `Detail` tetap sebagai secondary/outline button.
- `Konsultasikan` tetap sebagai primary filled button.
- Semua tombol `Konsultasikan` memakai gradient maroon yang sama.
- Semua tombol `Detail` memakai outline maroon yang sama.
- Pada layar sempit, action layanan dapat stack full-width agar tidak bertabrakan.
- Link WhatsApp existing tidak diubah.

## Perubahan Section Kontak

- Form besar tidak lagi tampil default di homepage.
- Default section kontak sekarang menampilkan quick contact:
  - telepon/WhatsApp
  - email
  - CTA utama `Konsultasi via WhatsApp`
  - CTA sekunder `Kirim pesan lewat formulir`
- Form lengkap tetap ada di DOM dan dibuka melalui accordion.
- `action`, `method`, `@csrf`, `name`, `id`, `old()`, x-model, dan validasi frontend existing tidak diubah.
- Jika ada validation error atau session success, form otomatis terbuka agar feedback submit tetap terlihat.

## Perubahan Navbar dan Anchor

- Navbar public sudah memakai daftar final:
  - Tentang
  - Visi & Misi
  - Layanan
  - Keunggulan
  - Artikel
  - Testimoni
  - FAQ
  - Kontak
- Tidak ada link navbar ke `#alur` atau `#edukasi-halal`.
- Mobile drawer memakai source nav yang sama, sehingga ikut sinkron.
- Validasi browser menunjukkan semua anchor navbar yang dirender memiliki target section aktif.

## Status Detail Layanan

- Detail layanan saat ini tetap berupa modal frontend.
- Modal sudah siap menampilkan:
  - nama layanan
  - deskripsi lengkap
  - manfaat jika tersedia dari data existing
  - siapa yang cocok menggunakan layanan
  - alur layanan spesifik
  - CTA konsultasi via WhatsApp
- Tidak dibuat route baru dan tidak dibuat data backend baru.

## Catatan Responsive

Validasi dilakukan pada:

- 360px
- 390px
- 768px
- 1024px
- 1440px

Hasil:

- Tidak ada horizontal overflow.
- Section Edukasi Layanan dan Alur Pendampingan tidak dirender.
- Button layanan tidak overlap.
- Contact quick card tampil default.
- Form kontak collapsed default dan dapat dibuka.
- Floating WhatsApp tetap ada dan tidak menutupi form/konten kontak.

## Catatan Dark Mode

- Card layanan, quick contact, modal detail, dan form kontak tetap memiliki background/border kontras di dark mode.
- Teks layanan dan kontak tetap terbaca.
- CTA primary dan outline tetap terlihat.

## Area Backend yang Sengaja Tidak Diubah

- Database
- Migration
- Seeder
- Model
- Controller
- Route
- Auth
- Middleware
- Query database
- Backend CRUD
- Backend form kontak
- Nomor WhatsApp
- Struktur data
- Admin panel

## Known Issue

- Tidak ada known issue dari validasi responsive saat ini.
- Warning PHP `Module ... is already loaded` masih muncul dari konfigurasi PHP lokal, bukan dari perubahan frontend.
