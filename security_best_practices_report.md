# Laporan Hardening Keamanan

Status: seluruh temuan kritis/tinggi yang teridentifikasi pada audit repository
telah diperbaiki dan diuji.

## Perbaikan utama

- Output metadata dan atribut layout di-escape untuk mencegah stored XSS.
- Alpine memakai build CSP resmi; CSP tidak lagi membutuhkan `unsafe-eval`.
- Sink DOM `innerHTML` dan inline event handler dihapus.
- Login admin memakai throttle per akun+IP dan per IP, audit log, regenerasi sesi,
  idle timeout, absolute timeout, dan `auth_version` untuk revokasi.
- Database session memakai guard admin sehingga `user_id` tercatat dan revokasi
  sesi lintas perangkat berfungsi.
- Password admin baru/seed minimum 14 karakter dengan huruf besar-kecil, angka,
  dan simbol.
- Seluruh mutasi admin diperiksa terhadap payload sisipan aktif dan pola
  spam perjudian/judol, termasuk encoding dan obfuscation umum.
- Upload publik hanya menerima JPG/PNG/WEBP berdasarkan MIME dan struktur file,
  membatasi jumlah piksel, memakai nama ULID, serta menolak polyglot/trailing
  payload.
- Apache dan Nginx menolak file aktif/non-raster dari `/storage/`.
- Command `security:scan` mendeteksi executable publik, upload berbahaya,
  stored injection, judol, dan marker development; dijadwalkan harian.
- Log keamanan dipisahkan dengan retensi 90 hari.
- Checklist deployment production dan prosedur respons insiden ditambahkan.

## Bukti verifikasi

- `php artisan test`: 125 test, 807 assertion, seluruhnya lulus.
- Test keamanan khusus: 6 test, 23 assertion, seluruhnya lulus.
- `npm run build`: build production berhasil, audit npm 0 vulnerability.
- `php artisan security:scan --json`: lulus tanpa finding.
- Browser Chromium dengan CSP aktif: homepage dan modal layanan berfungsi,
  0 console error dan 0 warning.
- Migration hardening sesi berhasil diterapkan.

## Batasan dan kontrol infrastruktur

- Tidak ada aplikasi yang dapat dijamin kebal absolut. Patch ini menutup jalur
  serangan yang ditemukan dan menambah defense-in-depth.
- Untuk production bernilai tinggi, tambahkan MFA admin, WAF/rate limiting di
  edge, alert terpusat dari `security.log`, backup immutable, pemindaian malware
  server-side, patch OS/PHP rutin, dan uji penetrasi independen berkala.
