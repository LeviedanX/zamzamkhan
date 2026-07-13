# PT Zam Zam Khan

Website company profile dan CMS untuk **PT Zam Zam Khan**, Bisnis & Legal Konsultan di Malang.

Website ini membantu calon klien memperoleh informasi layanan dan menghubungi tim konsultasi melalui WhatsApp. Panel admin digunakan untuk mengelola konten publik seperti profil, hero, layanan, artikel, FAQ, dan SEO.

## Teknologi

- Laravel 13
- PHP 8.3+
- MySQL atau SQLite untuk pengembangan
- Blade
- Vite, Tailwind CSS, dan Alpine.js

## Menjalankan Project Secara Lokal

### Prasyarat

- PHP 8.3 atau lebih baru
- Composer
- Node.js dan npm
- Database MySQL atau SQLite

### Instalasi

```bash
git clone https://github.com/LeviedanX/zamzamkhan.git
cd zamzamkhan
composer install
copy .env.example .env
php artisan key:generate
npm install
npm run build
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Untuk pengembangan frontend, jalankan `npm run dev` pada terminal terpisah.

### Konfigurasi Environment

Jangan commit `.env`. Sesuaikan minimal nilai `APP_URL` dan koneksi database pada file tersebut. Gunakan `.env.example` sebagai template. Kredensial admin dan data produksi harus dikelola di environment deployment, bukan di source code.

## Pengujian

```bash
php artisan test
npm run build
```

## Struktur Konten CMS

Modul aktif utama:

- Profil dan identitas perusahaan
- Hero homepage
- Layanan
- Artikel
- FAQ
- SEO website
- Laporan konten internal

WhatsApp menjadi jalur konsultasi utama dan nomor tujuan dikelola dari panel admin.

## Deployment

Panduan deployment dan checklist kesiapan tersedia di [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md).

## Lisensi

Lisensi project belum ditetapkan. Jangan menggunakan ulang atau mendistribusikan source code sebelum ada izin dari pemilik project.
