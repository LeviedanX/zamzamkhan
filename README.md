# PT Zam Zam Khan — Company Profile & CMS

## Bahasa Indonesia

### Tentang Project

Project ini adalah website company profile dan Content Management System (CMS) untuk **PT Zam Zam Khan**, Bisnis & Legal Konsultan yang berbasis di Malang.

Website publik memperkenalkan perusahaan, menjelaskan layanan konsultasi, menampilkan bukti sosial dan artikel edukasi, serta mengarahkan calon klien ke WhatsApp sebagai jalur konsultasi utama. Panel admin digunakan untuk mengelola konten website tanpa mengubah source code.

### Layanan Bisnis

- Sertifikasi halal.
- Legalitas usaha dan pendirian badan usaha.
- BPOM.
- HAKI.
- NPWP.
- Desain logo dan label kemasan.
- Pendampingan bisnis dan legal untuk UMKM hingga usaha besar.

### Fitur Utama

Website publik:

- Homepage dengan hero, profil, visi-misi, layanan, keunggulan, statistik, klien, testimoni, artikel, agenda, FAQ, dan kontak.
- Halaman daftar artikel dan detail artikel berdasarkan slug.
- SEO dasar: meta tag, Open Graph, JSON-LD, sitemap dinamis, dan robots.txt.
- CTA WhatsApp terpusat dari konfigurasi perusahaan yang dikelola admin.
- Responsive layout untuk desktop dan perangkat mobile.
- Logo, profil, kontak, alamat, Google Maps, social links, dan konten publik berbasis database.

Panel admin:

- Login dan pengelolaan akun admin.
- Dashboard CMS, editor hero, identitas perusahaan, dan SEO.
- CRUD layanan, FAQ, keunggulan, statistik, klien, testimoni, agenda, dan artikel.
- Kategori artikel dan kategori kebutuhan bisnis.
- Business applications beserta status dan riwayat status.
- Analitik kunjungan website.
- Laporan konten dengan export CSV/Excel.
- Middleware keamanan, security headers, validasi URL, dan pemeriksaan kesiapan deployment.

### Modul Deprecated

- **Pesan Masuk:** tidak menjadi alur aktif karena website menggunakan pendekatan WhatsApp-first.
- **Alur Pendampingan:** digantikan oleh bagian visi-misi.
- **Galeri:** tidak dirender sebagai section aktif pada homepage final.

Model, migration, atau tabel legacy dapat tetap berada di source code untuk kompatibilitas dan rollback, tetapi tidak boleh dianggap sebagai modul publik aktif.

### Teknologi

- Laravel 13 dan PHP 8.3+.
- Blade, Vite 8, Tailwind CSS 4, dan Alpine.js.
- MySQL untuk development/deployment; SQLite untuk testing dan skenario lokal tertentu.
- PHPUnit 12.
- OpenSpout untuk export spreadsheet.

### Instalasi Lokal

Prasyarat: PHP 8.3+ dengan extension database yang sesuai, Composer, Node.js, npm, dan MySQL atau SQLite.

```bash
git clone https://github.com/LeviedanX/zamzamkhan.git
cd zamzamkhan
composer install
copy .env.example .env
php artisan key:generate
npm install
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Pada macOS/Linux, gunakan `cp .env.example .env` sebagai pengganti `copy`. Sesuaikan `.env` sebelum migration, terutama `DB_CONNECTION`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD`. File `.env` tidak boleh di-commit.

Untuk frontend hot reload, jalankan pada terminal terpisah:

```bash
npm run dev
```

`serve.bat` dan `serve.ps1` adalah helper lokal yang membutuhkan folder `php-ini`; folder tersebut sengaja tidak disimpan di Git.

### Akun Admin

Isi `ADMIN_NAME`, `ADMIN_EMAIL`, dan `ADMIN_PASSWORD` pada `.env` lokal sebelum menjalankan seeder. Jangan menulis credential admin di README, source code, commit, issue, atau repository publik.

### Testing dan Build

```bash
php artisan test
npm run build
```

Testing menggunakan SQLite in-memory sesuai `phpunit.xml`, sehingga tidak mengubah database development.

### Struktur Direktori

```text
app/                    Logika aplikasi, controller, model, middleware, dan support class
bootstrap/              Bootstrap Laravel dan cache placeholder
config/                 Konfigurasi aplikasi dan fallback perusahaan
database/               Migration, factory, dan seeder
public/                 Entry point serta asset publik
resources/views/        Blade layout, halaman admin, dan partial homepage
resources/css/          Styling aplikasi
resources/js/           Entry point frontend
routes/                 Route publik, admin, dan console
scripts/                Script build/release
storage/                Runtime storage Laravel; data runtime tidak di-commit
tests/                  Feature test dan unit test
```

### Deployment

Sebelum deployment, siapkan environment production yang terpisah dari konfigurasi development. Production minimal harus menggunakan:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_KEY=<generate-secure-key>
```

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
npm ci
npm run build
php artisan optimize
```

Pastikan backup database, permission `storage` dan `bootstrap/cache`, HTTPS, mail, database production, serta credential admin sudah benar sebelum go-live.

### Lisensi

Source code ini bersifat proprietary. Lisensi open-source belum ditetapkan. Jangan menggunakan ulang, mendistribusikan, atau memodifikasi source code tanpa izin pemilik project.

---

## English

### Project Overview

This project is a company profile website and Content Management System (CMS) for **PT Zam Zam Khan**, a Business & Legal Consulting company based in Malang, Indonesia.

The public website introduces the company, explains its consulting services, presents social proof and educational content, and directs prospective clients to WhatsApp as the primary consultation channel. The admin panel manages website content without direct source-code changes.

### Business Services

- Halal certification.
- Business and company legal registration.
- BPOM assistance.
- Intellectual property rights (HAKI).
- Tax registration (NPWP).
- Logo and packaging-label design.
- Business and legal assistance for SMEs and larger businesses.

### Main Features

Public website:

- Homepage with hero, company profile, vision and mission, services, advantages, statistics, clients, testimonials, articles, agenda, FAQ, and contact sections.
- Article listing and slug-based article detail pages.
- Basic SEO with meta tags, Open Graph, JSON-LD, dynamic sitemap, and robots.txt.
- Centralized WhatsApp CTA configuration managed from the admin panel.
- Responsive desktop and mobile layout.
- Database-driven company profile, contact information, address, Google Maps, social links, logo, and public content.

Admin panel:

- Admin authentication and account management.
- CMS dashboard, hero editor, company identity editor, and SEO settings.
- CRUD management for services, FAQ, advantages, statistics, clients, testimonials, agendas, and articles.
- Article and business-need categories.
- Business application management with status history.
- Website visit analytics.
- Content reports with CSV/Excel export.
- Security middleware, security headers, URL validation, and deployment-readiness checks.

### Deprecated Modules

- **Inbox/messages:** not active because the website follows a WhatsApp-first approach.
- **Consulting process flow:** replaced by the vision and mission section.
- **Gallery:** not rendered as an active homepage section in the final version.

Legacy models, migrations, or tables may remain for compatibility and rollback, but they must not be treated as active public modules.

### Technology Stack

- Laravel 13 and PHP 8.3+.
- Blade, Vite 8, Tailwind CSS 4, and Alpine.js.
- MySQL for development/deployment; SQLite for tests and selected local scenarios.
- PHPUnit 12.
- OpenSpout for spreadsheet exports.

### Local Installation

Requirements: PHP 8.3+ with the required database extension, Composer, Node.js, npm, and MySQL or SQLite.

```bash
git clone https://github.com/LeviedanX/zamzamkhan.git
cd zamzamkhan
composer install
copy .env.example .env
php artisan key:generate
npm install
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

On macOS/Linux, use `cp .env.example .env` instead of `copy`. Configure `.env` before running migrations, especially `DB_CONNECTION`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`. Never commit `.env`.

For frontend hot reload, run this in a separate terminal:

```bash
npm run dev
```

`serve.bat` and `serve.ps1` are local helpers that require a `php-ini` directory; that directory is intentionally excluded from Git.

### Admin Account

Set `ADMIN_NAME`, `ADMIN_EMAIL`, and `ADMIN_PASSWORD` in the local `.env` before running the seeder. Never place admin credentials in the README, source code, commits, issues, or a public repository.

### Testing and Build

```bash
php artisan test
npm run build
```

Tests use the SQLite in-memory database configured in `phpunit.xml`, so they do not modify the development database.

### Deployment

Before deployment, prepare a production environment that is separate from the development configuration. Production should at minimum use:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_KEY=<generate-secure-key>
```

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
npm ci
npm run build
php artisan optimize
```

Before going live, verify database backups, `storage` and `bootstrap/cache` permissions, HTTPS, mail, production database settings, and admin credentials.

### License

This source code is proprietary. An open-source license has not been granted. Do not reuse, redistribute, or modify the source code without the project owner's permission.
