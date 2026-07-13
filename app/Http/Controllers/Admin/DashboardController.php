<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advantage;
use App\Models\Agenda;
use App\Models\Article;
use App\Models\BusinessApplication;
use App\Models\Client;
use App\Models\Faq;
use App\Models\HeroSection;
use App\Models\SeoSetting;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Statistic;
use App\Models\Testimonial;
use App\Models\WebVisit;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'articles' => Article::count(),
            'articles_draft' => Article::where('status', 'draft')->count(),
            'articles_published' => Article::where('status', 'published')->count(),
            'services' => Service::count(),
            'services_active' => Service::where('is_active', true)->count(),
            'faqs' => Faq::count(),
            'faqs_active' => Faq::where('is_active', true)->count(),
            'advantages_active' => Advantage::where('is_active', true)->count(),
            'statistics_active' => Statistic::where('is_active', true)->count(),
            'clients_active' => Client::where('is_active', true)->count(),
            'testimonials_active' => Testimonial::where('is_active', true)->count(),
            'agendas_active' => Agenda::where('is_active', true)->upcoming()->count(),
            'applications' => BusinessApplication::count(),
            'applications_ongoing' => BusinessApplication::whereNotIn('process_status', ['Sertifikat Terbit', 'Batal'])->count(),
            'visits_today' => WebVisit::whereBetween('visited_at', [now()->startOfDay(), now()->endOfDay()])->count(),
        ];

        $heroConfigured = HeroSection::where('is_active', true)->whereNotNull('title')->exists();
        $siteSetting = SiteSetting::first();
        $seo = SeoSetting::where('page_key', 'home')->first();

        $tiles = [
            ['label' => 'Total Artikel', 'value' => $stats['articles'], 'route' => 'admin.articles.index'],
            ['label' => 'Artikel Terbit', 'value' => $stats['articles_published'], 'route' => 'admin.articles.index'],
            ['label' => 'Artikel Draft', 'value' => $stats['articles_draft'], 'route' => 'admin.articles.index'],
            ['label' => 'Layanan Aktif', 'value' => $stats['services_active'], 'route' => 'admin.services.index'],
            ['label' => 'FAQ Aktif', 'value' => $stats['faqs_active'], 'route' => 'admin.faqs.index'],
            ['label' => 'Pengajuan Berjalan', 'value' => $stats['applications_ongoing'], 'route' => 'admin.applications.index'],
        ];

        $groups = [
            'Konten Website' => [
                ['admin.hero.edit', 'Hero Utama', $heroConfigured ? 'Aktif dan siap tampil di halaman utama' : 'Belum diatur', 'M13 10V3L4 14h7v7l9-11h-7z'],
                ['admin.settings.edit', 'Profil & Identitas', $siteSetting ? 'Informasi inti tersedia untuk public website' : 'Perlu dilengkapi', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                ['admin.services.index', 'Layanan', $stats['services_active'].' layanan aktif', 'M9 12l2 2 4-4m5 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['admin.advantages.index', 'Keunggulan', $stats['advantages_active'].' aktif', 'M12 3l2.7 5.5 6.1.9-4.4 4.3 1 6.1-5.4-2.8-5.4 2.8 1-6.1'],
                ['admin.statistics.index', 'Statistik', $stats['statistics_active'].' aktif', 'M5 20V10h3v10H5Zm6 0V4h3v16h-3Zm6 0v-7h3v7h-3Z'],
                ['admin.clients.index', 'Klien', $stats['clients_active'].' aktif', 'M8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM3 20a5 5 0 0 1 10 0'],
                ['admin.testimonials.index', 'Testimoni', $stats['testimonials_active'].' aktif', 'M5 6h14v10H9l-4 3V6Z'],
                ['admin.agendas.index', 'Agenda', $stats['agendas_active'].' aktif', 'M6 3v3M18 3v3M4 9h16M5 5h14v15H5V5Z'],
                ['admin.articles.index', 'Artikel & Insight', $stats['articles_published'].' terbit, '.$stats['articles_draft'].' draft', 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h9l7 7v7a2 2 0 01-2 2zM13 4v6h6'],
                ['admin.article-categories.index', 'Kategori Artikel', 'Kelola klasifikasi artikel', 'M4 6h16M4 12h16M4 18h10'],
                ['admin.faqs.index', 'FAQ', $stats['faqs_active'].' FAQ aktif', 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ],
            'Operasional Internal' => [
                ['admin.applications.index', 'Data Pengajuan', $stats['applications'].' data, '.$stats['applications_ongoing'].' berjalan', 'M5 4h14v16H5V4Z'],
                ['admin.business-categories.index', 'Kategori Bisnis', 'Master data pengajuan', 'M4 6h16M4 12h16M4 18h10'],
                ['admin.reports.index', 'Laporan', 'CSV Excel dan cetak PDF', 'M4 19V9h4v10H4ZM10 19V5h4v14h-4Z'],
                ['admin.analytics.index', 'Analitik Pengunjung', $stats['visits_today'].' tayangan hari ini', 'M4 19V9m5 10V5m5 14v-7m5 7V3'],
            ],
            'Pengaturan' => [
                ['admin.seo.edit', 'SEO Website', $seo?->meta_title ? 'Metadata utama tersedia' : 'Metadata perlu dilengkapi', 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'],
                ['admin.account.edit', 'Akun Admin', 'Kelola email dan password secara aman', 'M12 12a4 4 0 1 0 0-8M5 21a7 7 0 0 1 14 0'],
            ],
        ];

        return view('admin.dashboard', compact('stats', 'tiles', 'groups'));
    }
}
