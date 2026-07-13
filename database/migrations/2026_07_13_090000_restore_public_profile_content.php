<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('site_settings')) {
            return;
        }

        $defaults = [
            'operating_hours' => 'Senin–Jumat, 08.00–16.00 WIB',
            'company_description' => "PT Zam Zam Khan hadir sebagai mitra pendamping bagi pelaku usaha yang ingin menata legalitas, sertifikasi, dan identitas produknya secara lebih profesional. Kami membantu proses sertifikasi halal, legalitas usaha, BPOM, HAKI, NPWP, akta pendirian, perpajakan, hingga desain logo dan label kemasan.\nDengan pendekatan yang terarah, kami mendampingi UMKM, restoran, catering, produsen makanan, dan badan usaha agar memiliki dokumen usaha yang lebih tertata, legal, dan siap bersaing di pasar.",
            'vision' => 'Jadikan bisnis Anda lebih berkembang dan berkah dengan layanan konsultasi bisnis halal dari PT Zam Zam Khan. Kami hadir untuk memberikan solusi strategis sesuai prinsip syariah agar setiap langkah bisnis berjalan tepat, aman, halal, dan berkelanjutan.',
            'mission' => "Membantu pelaku usaha, baik UMK maupun non-UMK, agar berkembang secara legal dan mampu bersaing di dunia usaha.\nMemberikan pendampingan mulai dari tahap perencanaan jenis usaha dan pengembangan branding.\nMembantu proses perizinan dan kebutuhan legalitas usaha secara terarah agar usaha dapat tumbuh dan bersaing.",
            'maps_url' => 'https://www.google.com/maps/search/?api=1&query=Jl.%20MT.%20Haryono%20Gang%206B%20No.949%2C%20Dinoyo%2C%20Lowokwaru%2C%20Kota%20Malang',
            'maps_embed_url' => 'https://www.google.com/maps?q=Jl.%20MT%20Haryono%20Gang%206B%20No.949%2C%20Dinoyo%2C%20Lowokwaru%2C%20Kota%20Malang%2C%20Jawa%20Timur&output=embed',
        ];

        foreach ($defaults as $column => $value) {
            if (Schema::hasColumn('site_settings', $column)) {
                DB::table('site_settings')->whereNull($column)->update([$column => $value]);
            }
        }
    }

    public function down(): void
    {
        // Data yang sudah dipulihkan tidak dihapus saat rollback agar perubahan
        // admin setelah migration tetap aman.
    }
};
