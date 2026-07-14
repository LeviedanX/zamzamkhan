<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\HeroSection;
use App\Models\SeoSetting;
use App\Models\Service;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $c = config('company');

        SiteSetting::updateOrCreate(['id' => 1], [
            'company_name' => $c['name'],
            'tagline' => $c['tagline'],
            'phone' => $c['phone_display'],
            'whatsapp' => '6285234797788',
            'email' => $c['email'],
            'address' => $c['address'],
            'operating_hours' => $c['operating_hours'],
            'company_description' => $c['about'],
            'vision' => $c['vision'],
            'mission' => $c['mission'],
            'maps_url' => $c['maps_url'],
            'maps_embed_url' => $c['maps_embed'],
            'instagram_url' => $c['socials'][0]['url'] ?? null,
            'facebook_url' => $c['socials'][1]['url'] ?? null,
            'tiktok_url' => $c['socials'][2]['url'] ?? null,
            'logo_path' => 'images/logo-zzk.png',
        ]);

        HeroSection::updateOrCreate(['id' => 1], [
            'title' => 'Konsultan Halal dan Legalitas Usaha di Malang',
            'subtitle' => 'PT Zam Zam Khan membantu pelaku usaha dalam pendampingan sertifikasi halal, legalitas usaha, BPOM, HAKI, NPWP, akta pendirian, serta desain logo dan label kemasan produk.',
            'secondary_button_text' => 'Lihat Layanan',
            // secondary_button_url tidak diisi: tombol hero selalu mengarah ke #layanan (hardcoded),
            // tujuan tombol dihapus dari admin karena tidak ada kegunaan lain selain anchor tetap ini.
            // Gambar latar bersifat opsional; hero sudah punya visual bawaan (images/bg1.webp).
            // Jangan seed path ke file yang tidak ada — itu membuat latar tidak pernah render
            // sekaligus memunculkan opsi "Hapus gambar latar" untuk file hantu di admin.
            'image_path' => null,
            'badge_text' => 'Konsultan Bisnis & Legal — Kota Malang',
            'trust_text' => 'Dipercaya 500++ pelaku usaha dan badan usaha.',
            'service_chips' => "Sertifikasi Halal\nLegalitas Usaha\nBPOM & HAKI\nLogo & Label Kemasan",
            'portrait_path' => 'images/buzamzami.png',
            'portrait_alt' => 'Direktur PT Zam Zam Khan, Dra. Atfiah El Zam Zami, MM.',
            'portrait_role' => 'Direktur',
            'portrait_name' => 'Dra. Atfiah El Zam Zami, MM.',
            'is_active' => true,
        ]);

        foreach ($c['services'] as $i => $s) {
            Service::updateOrCreate(
                ['slug' => $s['slug'] ?? Str::slug($s['title'])],
                [
                    'title' => $s['title'],
                    'icon' => $s['icon'],
                    'summary' => Str::limit($s['desc'], 150),
                    'description' => $s['detail'] ?? $s['desc'],
                    'benefits' => implode("\n", $s['benefits'] ?? []),
                    'suitable_for' => $s['suitable_for'] ?? null,
                    'workflow_steps' => implode("\n", $s['workflow_steps'] ?? []),
                    'whatsapp_message' => $s['whatsapp_message'] ?? null,
                    'display_order' => $i + 1,
                    'is_featured' => $i < 4,
                    'is_active' => true,
                ]
            );
        }

        foreach ($c['faq'] as $i => $f) {
            Faq::updateOrCreate(
                ['question' => $f['q']],
                ['answer' => $f['a'], 'display_order' => $i + 1, 'is_active' => true]
            );
        }

        SeoSetting::updateOrCreate(['page_key' => 'home'], [
            'meta_title' => 'Konsultan Halal & Legalitas Usaha di Malang | PT Zam Zam Khan',
            'meta_description' => 'PT Zam Zam Khan melayani konsultasi halal, legalitas usaha, NIB, akta pendirian, NPWP, BPOM, HAKI, dan desain label kemasan untuk UMKM serta pelaku usaha di Malang.',
            'meta_keywords' => 'konsultan halal Malang, jasa sertifikat halal Malang, konsultan legalitas usaha Malang, jasa BPOM Malang, jasa NIB Malang, jasa HAKI Malang',
            'og_title' => 'PT Zam Zam Khan — Bisnis & Legal Konsultan',
            'og_description' => 'Pendampingan halal, legalitas usaha, dan branding produk untuk pelaku usaha di Malang.',
        ]);
    }
}
