<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $services = require config_path('service-details.php');

        foreach ($services as $service) {
            DB::table('services')
                ->where('slug', $service['slug'])
                ->update([
                    'title' => $service['title'],
                    'icon' => $service['icon'],
                    'summary' => $service['desc'],
                    'description' => $service['detail'],
                    'benefits' => implode("\n", $service['benefits']),
                    'suitable_for' => $service['suitable_for'],
                    'workflow_steps' => implode("\n", $service['workflow_steps']),
                    'whatsapp_message' => $service['whatsapp_message'],
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        $legacy = [
            'sertifikat-halal-reguler' => ['Pendampingan sertifikat halal reguler untuk usaha di luar kategori self declare, seperti restoran, catering, cafe, pabrik produksi besar, RPH, dan RPU.', 'Restoran, catering, cafe, pabrik produksi skala besar, Rumah Potong Hewan (RPH), dan Rumah Potong Unggas (RPU).', ['Konsultasi & pemetaan kategori usaha', 'Penyiapan dokumen dan penyelia halal', 'Pengajuan permohonan reguler', 'Pendampingan proses audit & verifikasi', 'Terbit sertifikat & tindak lanjut']],
            'nib-nomor-induk-berusaha' => ['Pendampingan pembuatan Nomor Induk Berusaha sebagai identitas resmi pelaku usaha dan dasar legalitas kegiatan usaha.', 'Pelaku usaha baru maupun yang sedang berjalan dan membutuhkan identitas berusaha resmi.', ['Konsultasi awal kebutuhan usaha', 'Penyiapan data & dokumen usaha', 'Pendampingan pendaftaran NIB', 'Pemrosesan hingga terbit', 'Penyerahan NIB & arahan lanjutan']],
            'akta-pendirian-badan-usaha' => ['Pendampingan kebutuhan akta pendirian untuk badan usaha seperti PT, CV, firma, atau bentuk usaha lain sesuai kebutuhan.', 'Pelaku usaha yang ingin mendirikan badan usaha resmi (PT, CV, firma) atau membenahi legalitas badan usahanya.', ['Konsultasi pemilihan badan usaha', 'Penyiapan data pendiri & dokumen awal', 'Penyusunan draft akta', 'Pengesahan melalui notaris', 'Dokumen legal terbit & tindak lanjut']],
            'npwp-dan-pelaporan-pajak' => ['Pendampingan administrasi NPWP dan pelaporan pajak agar kewajiban perpajakan usaha lebih tertib.', 'Pelaku usaha yang membutuhkan NPWP badan/pribadi dan pendampingan administrasi pelaporan pajak.', ['Konsultasi kebutuhan perpajakan', 'Penyiapan dokumen administrasi', 'Pendampingan pengurusan NPWP', 'Pendampingan pelaporan pajak', 'Arahan tindak lanjut berkala']],
            'bpom' => ['Pendampingan informasi dan proses awal terkait izin edar BPOM untuk produk yang membutuhkan legalitas distribusi sesuai ketentuan.', 'Produsen makanan, minuman, kosmetik, atau produk lain yang memerlukan izin edar untuk distribusi.', ['Konsultasi & identifikasi kategori produk', 'Penyiapan dokumen awal produk', 'Pendampingan pengajuan izin edar', 'Pemantauan proses verifikasi', 'Tindak lanjut hasil pengurusan']],
            'haki' => ['Pendampingan pendaftaran hak kekayaan intelektual untuk melindungi karya, merek, desain, atau aset intelektual usaha.', 'Pelaku usaha yang ingin melindungi merek, logo, karya, atau aset intelektual dari penggunaan pihak lain.', ['Konsultasi perlindungan merek/karya', 'Penelusuran & penyiapan dokumen awal', 'Pendampingan pendaftaran', 'Pemantauan proses pemeriksaan', 'Arahan tindak lanjut hasil']],
            'desain-logo-label-kemasan' => ['Jasa desain logo dan label kemasan produk untuk mendukung branding, identitas visual, dan kebutuhan pemasaran produk.', 'Pelaku usaha yang membutuhkan logo, identitas visual, atau desain label kemasan yang profesional.', ['Konsultasi brief & konsep', 'Penyusunan arah visual (moodboard)', 'Proses desain awal', 'Revisi sesuai masukan', 'Penyerahan file final siap pakai']],
        ];

        foreach ($legacy as $slug => [$description, $suitableFor, $steps]) {
            DB::table('services')->where('slug', $slug)->update([
                'summary' => $description,
                'description' => $description,
                'benefits' => null,
                'suitable_for' => $suitableFor,
                'workflow_steps' => implode("\n", $steps),
                'updated_at' => now(),
            ]);
        }
    }
};
