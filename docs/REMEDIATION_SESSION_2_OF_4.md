# Remediasi Sesi 2 dari 4 — Konsistensi CMS dan Public Website

## Status Patch

Selesai untuk ruang lingkup Sesi 2/4. Website belum dinyatakan siap deploy karena hardening operasional, validasi CRUD/admin, dan checklist deployment final masih dijadwalkan pada Sesi 3–4.

## File yang Diubah

- `app/Providers/AppServiceProvider.php`
- `app/Models/Article.php`
- `app/Rules/GoogleMapsUrl.php`
- `app/Support/SafeUrl.php`
- `app/Http/Requests/Admin/UpdateSiteSettingRequest.php`
- `app/Http/Controllers/Admin/SeoController.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/partials/tentang.blade.php`
- `resources/views/partials/visi-misi.blade.php`
- `resources/views/partials/agenda.blade.php`
- `resources/views/partials/hero.blade.php`
- `resources/views/partials/navbar.blade.php`
- `resources/views/partials/footer.blade.php`
- `resources/views/partials/kontak.blade.php`
- `resources/views/partials/layanan.blade.php`
- `resources/views/partials/statistik.blade.php`
- `resources/views/partials/article-cover.blade.php`
- `resources/views/partials/seo-jsonld.blade.php`
- `resources/views/articles/show.blade.php`
- `resources/views/admin/settings/edit.blade.php`
- `tests/Feature/Admin/ProfilIdentitasTest.php`
- `tests/Feature/ArticleTest.php`
- `tests/Feature/PublicCmsConsistencyTest.php`

## Ringkasan Perubahan

- Menjadikan data CMS sebagai sumber kebenaran untuk profil, visi–misi, kontak, logo, brand, WhatsApp, tautan sosial, peta, dan canonical URL. Nilai kosong yang sengaja disimpan tidak lagi dihidupkan kembali oleh fallback konfigurasi.
- Membuat visibilitas section dan navigasi mengikuti data aktif. Section kosong tidak dirender dan tautan navigasi menuju section tersebut ikut dihilangkan.
- Agenda yang kosong atau telah berakhir tidak lagi menghasilkan section kosong maupun menu Agenda yang tidak memiliki target.
- Tombol serta modal WhatsApp hanya dirender ketika nomor WhatsApp valid tersedia. CTA layanan memakai kondisi yang sama.
- Memperketat URL eksternal menjadi hanya `http`/`https`; URL peta dibatasi ke host Google Maps yang diizinkan.
- Memperbaiki JSON-LD agar bersumber dari CMS dan aman dari pemutusan konteks `<script>`.
- Artikel dengan tanggal publikasi di masa depan tidak lagi muncul pada daftar, detail publik, atau sitemap.
- Counter statistik memiliki nilai server-side yang benar sebelum JavaScript berjalan.
- ID SVG fallback sampul artikel dibuat unik agar tidak bentrok ketika beberapa kartu dirender dalam satu halaman.

## Modul yang Disembunyikan/Dideprecated

- Tidak ada modul baru yang dideprecated pada sesi ini.
- Section Tentang dan Visi–Misi pada database saat ini tidak tampil karena isinya kosong. Ini perilaku CMS yang disengaja; section akan tampil kembali setelah admin mengisi kontennya.
- Agenda tidak tampil karena tidak ada agenda aktif yang masih berlangsung.

## Dampak ke Public Website

- Tidak ada section kosong, menu tanpa target, CTA WhatsApp tanpa tujuan, atau fallback statis yang melawan keputusan admin.
- Data profil dan SEO publik lebih konsisten dengan nilai yang dikelola admin.
- URL eksternal dan structured data lebih aman terhadap input tidak tepercaya.
- Render tanpa JavaScript tetap menampilkan statistik yang bermakna.

## Dampak ke Admin

- Mengosongkan field opsional benar-benar menghapus nilai tersebut dari public website; form edit tidak lagi mengisi ulang nilai fallback lama.
- Input URL non-HTTP(S), canonical yang tidak aman, dan URL peta di luar host yang diizinkan ditolak oleh validasi.
- Tidak ada perubahan pada autentikasi maupun struktur CRUD utama dalam sesi ini.

## Pengujian yang Dilakukan

- `php artisan test`: **91 test, 612 assertion, lulus**.
- Test terfokus konsistensi CMS/public: **36 test, 262 assertion, lulus**.
- Lint PHP untuk file PHP yang berubah: lulus.
- Kompilasi Blade (`view:cache`): lulus.
- Build Vite production: lulus.
- `composer validate`, audit Composer, dan audit dependency production npm: lulus, **0 kerentanan dependency terdeteksi**.
- HTTP runtime homepage: status 200; tidak ada section/menu Agenda kosong dan tidak ada raw script breakout.
- Browser desktop 1280×720: tidak ada overflow horizontal, gambar rusak, target hash hilang, atau JSON-LD invalid; tepat satu `h1`.
- Browser mobile 390×844: tidak ada overflow horizontal atau gambar rusak; menu mobile terbuka normal.
- Console browser: 0 error, 0 warning.
- Cache Laravel untuk config, route, dan view berhasil dibuat, lalu dibersihkan kembali agar workspace tetap netral.

## Hal yang Belum Dikerjakan

- Validasi browser end-to-end seluruh CRUD admin dan upload media.
- Pemeriksaan authorization per route/controller admin dan kebijakan file upload secara menyeluruh.
- Finalisasi migration/data production, storage, queue/scheduler, email, backup, logging, dan konfigurasi web server.
- Smoke test pada environment production-like serta checklist deployment final.
- Pekerjaan tersebut menjadi ruang lingkup Sesi 3 dan Sesi 4.

## Catatan Risiko

- Konten Tentang dan Visi–Misi harus diisi admin jika keduanya memang diwajibkan tampil saat peluncuran.
- Validasi URL menolak skema dan host di luar allowlist; URL lama yang tidak memenuhi aturan perlu diperbarui melalui admin.
- Repository Git yang terdeteksi dari direktori kerja berakar di profil pengguna, bukan di folder project. Karena itu status/diff Git tidak dipakai sebagai bukti perubahan agar file di luar project tidak tercampur.
- Hasil audit dependency tidak menggantikan pengujian keamanan dinamis pada deployment sebenarnya.
