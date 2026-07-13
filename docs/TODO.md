# TODO — Admin CMS zzk-web

## Selesai
- [x] Topbar admin dengan tombol Buka Website Publik
- [x] Drawer admin mobile/tablet berbasis Alpine
- [x] Modal konfirmasi hapus reusable tanpa browser confirm
- [x] Dashboard control center dengan konteks per modul
- [x] Empty state dan status label list admin dipoles
- [x] Detail modal Layanan memakai deskripsi database bila tersedia
- [x] Kolom profil baru di `site_settings` (migration additive)
- [x] Form admin Profil & Identitas berkelompok + FormRequest
- [x] Bridge field baru ke public (about, visi, misi, konsultan, jam operasional, maps)
- [x] Wiring partial: navbar, hero, tentang, visi-misi, kontak, footer
- [x] Section Alur Pendampingan tampil di homepage setelah Layanan
- [x] Upload gambar Hero dari admin dipakai sebagai layer background opsional dengan fallback visual bawaan
- [x] Dashboard control center + statistik + feature card berkelompok
- [x] Navigasi admin dikelompokkan
- [x] Remake UI form/list admin utama agar konsisten dengan tampilan website utama
- [x] Toggle Light/Dark theme admin dan mode preview Hero Handphone/Windows
- [x] Full editor Hero untuk badge, trust line, chip layanan, background, dan gambar/caption figur direktur
- [x] Test: auth, resiliensi homepage, persistensi field, artikel draft, form kontak
- [x] Dokumentasi RUN_STATE / CHANGELOG / TODO

## Perlu tindakan pengguna (manual)
- [ ] Jalankan `php artisan storage:link` bila upload gambar hero/galeri/OG belum tampil di public
- [ ] Jalankan migration additive Hero full editor sebelum menyimpan field Hero baru di database existing
- [ ] `php artisan migrate` (menjalankan migration additive)
- [ ] `php artisan storage:link` (bila belum) agar gambar tampil
- [ ] `php artisan test` untuk verifikasi
- [ ] Isi Profil & Identitas di admin, lalu cek homepage (deskripsi, visi/misi, jam operasional, maps)
- [ ] Upload gambar Hero di admin, lalu cek layer background hero di public; bila kosong, pastikan hero bawaan tetap tampil

## Backlog (di luar patch ini)
- [ ] Mapping lanjutan untuk detail spesifik layanan masih berbasis icon sebagai fallback
- [ ] FormRequest terpisah untuk Hero/Service/FAQ/Gallery/SEO
- [ ] Test runtime/browser untuk drawer, modal delete, dan responsive admin
- [ ] Mapping lanjutan modal layanan masih berbasis icon sebagai fallback untuk detail khusus.
- [ ] Test runtime/browser untuk memastikan polish admin pada 360px, 390px, tablet, dan desktop
- [ ] Test runtime/browser untuk hero image dan layout responsive
- [ ] FormRequest terpisah untuk Hero/Service/FAQ/Gallery/SEO (saat ini validasi inline sudah aman)
- [ ] Modul konten untuk section `edukasi` (masih array statis)
- [ ] Konfirmasi status git root (idealnya `git init` di folder project, bukan folder home)
- [ ] Uji manual bridge DB→public (tidak tercakup PHPUnit karena guard `runningInConsole`)
