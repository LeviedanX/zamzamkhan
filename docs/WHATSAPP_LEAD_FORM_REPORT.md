# WhatsApp Lead Form Report

## Ringkasan Perubahan

Alur WhatsApp public website sekarang memakai satu komponen frontend yang mendukung dua konteks berbeda:

- `service` untuk user yang memilih layanan spesifik dari card layanan.
- `undecided` untuk user yang masih bingung menentukan layanan.

User tetap diarahkan ke WhatsApp dengan pesan siap kirim dan tetap perlu menekan Send di WhatsApp.

## Nomor WhatsApp Final

Nomor WhatsApp public CTA memakai:

- Display: `081256059099`
- URL/wa.me: `6281256059099`

Nomor ini diganti pada fallback utama:

- `config/company.php`

Komponen form mengambil nomor submit dari `config('company.whatsapp_number')`, lalu fallback ke `config('company.phone_raw')` bila key tersebut tidak ada. Nomor tetap dinormalisasi dari `08...` menjadi `628...`.

## Mode Service

Mode `service` dipakai saat user klik:

- Tombol `Konsultasikan` pada card layanan.
- Tombol `Konsultasikan via WhatsApp` dari modal detail layanan.

Field:

- Nama, wajib.
- Layanan dipilih, fixed/read-only dari card layanan.
- Jenis usaha/produk, opsional.
- Domisili, opsional.
- Kebutuhan singkat, wajib.

Layanan tidak tampil sebagai dropdown. User harus menutup form dan memilih card layanan lain jika ingin mengganti layanan.

Template pesan:

```text
Halo PT Zam Zam Khan, saya ingin konsultasi layanan.

Nama: [nama]
Layanan: [layanan_fixed]
Jenis usaha/produk: [jenis_usaha]
Domisili: [domisili]
Kebutuhan: [kebutuhan]

Mohon arahan untuk proses selanjutnya.
```

## Mode Undecided

Mode `undecided` dipakai saat user klik CTA umum:

- Hero CTA utama `Konsultasi via WhatsApp`.
- CTA kontak `Konsultasi Cepat via WhatsApp`.
- CTA FAQ `Tanya via WhatsApp`.
- CTA artikel `Konsultasikan via WhatsApp`.
- Floating WhatsApp button.

Field:

- Nama, wajib.
- Jenis usaha/produk, opsional.
- Domisili, opsional.
- Ceritakan kebutuhan atau kendala usaha, wajib.

Mode ini tidak menampilkan dropdown layanan dan menampilkan info kecil: `Saya belum yakin layanan apa yang dibutuhkan.`

Template pesan:

```text
Halo PT Zam Zam Khan, saya masih bingung menentukan layanan yang sesuai.

Nama: [nama]
Jenis usaha/produk: [jenis_usaha]
Domisili: [domisili]
Kebutuhan/kendala: [kebutuhan]

Mohon arahan layanan yang paling sesuai untuk kebutuhan saya.
```

## Validasi Frontend

Mode service:

- Nama wajib diisi.
- Layanan fixed wajib ada dari card.
- Kebutuhan singkat wajib diisi.

Mode undecided:

- Nama wajib diisi.
- Kebutuhan/kendala wajib diisi.

Field opsional yang kosong dikirim sebagai `Belum diisi`.

## Catatan Responsive dan Dark Mode

Form tetap menjadi bottom sheet pada mobile dan dialog tengah pada layar lebih besar. Panel memakai `max-height: 100dvh` dan `overflow-y: auto` agar tombol submit tetap bisa dijangkau. Floating WhatsApp dan back-to-top disembunyikan sementara saat modal terbuka.

Style dark mode tersedia untuk panel, label, input, placeholder, badge layanan fixed, note undecided, tombol close, dan error message.

## Area Backend yang Tidak Diubah

- Database
- Migration
- Seeder
- Model
- Controller
- Route
- Auth
- Middleware
- Query
- Backend CRUD
- Backend form kontak
- Admin panel
