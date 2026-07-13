<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        if (Schema::hasTable('process_steps') && DB::table('process_steps')->doesntExist()) {
            $steps = [
                ['Konsultasi Awal', 'Diskusi kebutuhan usaha dan tujuan legalitas atau sertifikasi Anda.'],
                ['Identifikasi Kebutuhan', 'Menentukan jenis layanan yang paling sesuai dengan kondisi usaha.'],
                ['Persiapan Dokumen', 'Pengecekan dan penyiapan dokumen yang diperlukan untuk proses.'],
                ['Pengajuan & Proses', 'Pengajuan atau pendampingan administrasi sesuai ruang lingkup layanan.'],
                ['Monitoring Proses', 'Pemantauan perkembangan proses hingga tahap penyelesaian.'],
                ['Hasil & Tindak Lanjut', 'Penyerahan hasil akhir beserta arahan tindak lanjut bagi usaha Anda.'],
            ];

            DB::table('process_steps')->insert(collect($steps)->map(
                fn (array $step, int $index) => [
                    'title' => $step[0],
                    'description' => $step[1],
                    'display_order' => $index + 1,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            )->all());
        }

        if (Schema::hasTable('galleries') && DB::table('galleries')->doesntExist()) {
            $items = [
                ['Pendampingan Sertifikasi Halal', 'images/testimonials/testi1.jpeg', 'Dokumentasi pendampingan sertifikasi halal PT Zam Zam Khan'],
                ['Dokumentasi Klien Hospitality', 'images/testimonials/testi2.jpeg', 'Dokumentasi layanan klien hospitality PT Zam Zam Khan'],
                ['Penyerahan Sertifikat Halal', 'images/testimonials/testi3.jpeg', 'Penyerahan sertifikat halal kepada klien PT Zam Zam Khan'],
                ['Pendampingan UMKM', 'images/testimonials/testi4.jpeg', 'Dokumentasi pendampingan UMKM PT Zam Zam Khan'],
                ['Legalitas Produk Usaha', 'images/testimonials/testi5.jpeg', 'Dokumentasi legalitas produk usaha PT Zam Zam Khan'],
                ['Kegiatan Konsultasi Usaha', 'images/testimonials/testi6.jpeg', 'Dokumentasi konsultasi usaha PT Zam Zam Khan'],
            ];

            DB::table('galleries')->insert(collect($items)->map(
                fn (array $item, int $index) => [
                    'title' => $item[0],
                    'image_path' => $item[1],
                    'alt_text' => $item[2],
                    'category' => 'Dokumentasi',
                    'display_order' => $index + 1,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            )->all());
        }
    }

    public function down(): void
    {
        // Data hasil pemulihan dipertahankan agar rollback tidak menghapus konten admin.
    }
};
