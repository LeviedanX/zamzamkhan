<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('services')
            ->where('slug', 'sertifikat-halal')
            ->update([
                'title' => 'Sertifikat Halal Self-Declare',
                'summary' => 'Pendampingan sertifikasi halal Self-Declare bagi UMK yang memiliki NIB, menggunakan bahan yang dipastikan halal, dan menjalankan proses produksi sederhana.',
                'description' => 'Pendampingan Sertifikat Halal Self-Declare bagi pelaku usaha mikro dan kecil (UMK) yang memenuhi kriteria. Layanan mencakup pemeriksaan kelayakan awal, penyiapan data produk dan bahan, penyusunan dokumen Sistem Jaminan Produk Halal (SJPH), pengajuan melalui SIHALAL, verifikasi dan validasi oleh Pendamping PPH, perbaikan dokumen bila diperlukan, hingga pemantauan penetapan halal dan penerbitan sertifikat elektronik oleh BPJPH.',
                'benefits' => implode("\n", [
                    'Pemeriksaan awal kesesuaian usaha dan produk dengan kriteria Self-Declare.',
                    'Pendampingan penyiapan data bahan, proses produksi, dan dokumen SJPH.',
                    'Pendampingan pengajuan serta pemenuhan catatan melalui SIHALAL.',
                    'Pemantauan proses sampai sertifikat halal elektronik diterbitkan BPJPH.',
                ]),
                'suitable_for' => 'Pelaku usaha mikro dan kecil yang telah memiliki NIB, menggunakan bahan yang jelas status kehalalannya, menjalankan proses produksi sederhana, serta produk dan prosesnya memenuhi kriteria skema Self-Declare.',
                'workflow_steps' => implode("\n", [
                    'Konsultasi awal dan pemeriksaan kelayakan skema Self-Declare',
                    'Penyiapan NIB, data pelaku usaha, daftar produk, bahan, pemasok, dan proses produksi',
                    'Penyusunan dokumen Sistem Jaminan Produk Halal (SJPH) dan pernyataan halal pelaku usaha',
                    'Pembuatan atau pelengkapan akun serta pengajuan permohonan melalui SIHALAL',
                    'Verifikasi dan validasi dokumen serta proses produk oleh Pendamping PPH',
                    'Perbaikan dan pemenuhan dokumen apabila terdapat catatan hasil verifikasi',
                    'Pengajuan hasil pendampingan untuk penetapan kehalalan oleh Komite Fatwa Produk Halal',
                    'Penerbitan sertifikat halal elektronik oleh BPJPH dan arahan penggunaan label halal',
                ]),
                'whatsapp_message' => 'Halo PT Zam Zam Khan, saya ingin konsultasi layanan Sertifikat Halal Self-Declare. Mohon dibantu pemeriksaan kelayakan, persyaratan dokumen, dan alur pendampingannya.',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('services')
            ->where('slug', 'sertifikat-halal')
            ->update([
                'title' => 'Sertifikat Halal',
                'summary' => 'Pendampingan proses sertifikasi halal untuk membantu pelaku usaha memenuhi kebutuhan jaminan kehalalan produk sesuai ketentuan yang berlaku.',
                'description' => 'Pendampingan sertifikasi halal untuk membantu pelaku usaha memenuhi kebutuhan jaminan kehalalan produk sesuai ketentuan yang berlaku, mulai dari persiapan dokumen hingga pendampingan proses pengajuan.',
                'benefits' => null,
                'suitable_for' => 'UMKM serta produsen makanan, minuman, dan produk konsumsi yang ingin produknya memiliki sertifikat halal.',
                'workflow_steps' => implode("\n", [
                    'Konsultasi awal & identifikasi produk',
                    'Persiapan dokumen dan data produk',
                    'Pengajuan permohonan sertifikasi',
                    'Pendampingan proses verifikasi',
                    'Terbit sertifikat & tindak lanjut',
                ]),
                'whatsapp_message' => 'Halo PT Zam Zam Khan, saya ingin konsultasi terkait layanan Sertifikat Halal. Mohon informasi persyaratan, alur pendampingan, dan estimasi prosesnya.',
                'updated_at' => now(),
            ]);
    }
};
