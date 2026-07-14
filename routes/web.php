<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\AdvantageController;
use App\Http\Controllers\Admin\AgendaController;
use App\Http\Controllers\Admin\ArticleCategoryController;
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BusinessApplicationController;
use App\Http\Controllers\Admin\BusinessCategoryController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\HeroController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\StatisticController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\VisitorAnalyticsController;
use App\Http\Controllers\ArticleController;
use App\Http\Middleware\EnsureDesktopAdminAccess;
use App\Http\Middleware\EnsureAdminIsActive;
use App\Models\Article;
use App\Models\HeroSection;
use App\Models\Service;
use App\Models\SiteSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home'))->name('home');

// Artikel & Insight (public)
Route::get('/artikel', [ArticleController::class, 'index'])->name('artikel.index');
Route::get('/artikel/{slug}', [ArticleController::class, 'show'])->name('artikel.show');

// SEO: sitemap & robots (dinamis agar URL sesuai environment)
Route::get('/sitemap.xml', function () {
    $home = url('/');

    // lastmod harus mencerminkan perubahan konten sungguhan. Memakai now() akan
    // memberi tahu crawler bahwa setiap halaman berubah setiap kali sitemap
    // diambil, dan sinyal itu akan diabaikan karena terbukti tidak akurat.
    $toAtom = fn ($value) => $value
        ? Carbon::parse($value)->toAtomString()
        : now()->toAtomString();

    $articlesLastmod = Article::published()->max('updated_at');
    $homeLastmod = collect([
        SiteSetting::max('updated_at'),
        HeroSection::max('updated_at'),
        Service::max('updated_at'),
        $articlesLastmod,
    ])->filter()->max();

    $lastmod = $toAtom($homeLastmod);
    $indexLastmod = $toAtom($articlesLastmod);

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
        .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n"
        ."  <url>\n    <loc>{$home}</loc>\n    <lastmod>{$lastmod}</lastmod>\n    <changefreq>weekly</changefreq>\n    <priority>1.0</priority>\n  </url>\n"
        ."  <url>\n    <loc>".route('artikel.index')."</loc>\n    <lastmod>{$indexLastmod}</lastmod>\n    <changefreq>weekly</changefreq>\n    <priority>0.8</priority>\n  </url>\n";

    foreach (Article::published()->latestPublished()->get(['slug', 'updated_at']) as $article) {
        $loc = route('artikel.show', $article->slug);
        $mod = $article->updated_at?->toAtomString() ?? $lastmod;
        $xml .= "  <url>\n    <loc>{$loc}</loc>\n    <lastmod>{$mod}</lastmod>\n    <changefreq>monthly</changefreq>\n    <priority>0.6</priority>\n  </url>\n";
    }

    $xml .= '</urlset>';

    return response($xml, 200, ['Content-Type' => 'application/xml']);
})->name('sitemap');

Route::get('/robots.txt', function () {
    $body = "User-agent: *\nAllow: /\nDisallow: /admin\n\nSitemap: ".url('/sitemap.xml')."\n";

    return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
});

// ---------------------------------------------------------------------------
// Admin
// ---------------------------------------------------------------------------
Route::prefix('admin')->name('admin.')->middleware(EnsureDesktopAdminAccess::class)->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->name('login.attempt');
    });

    Route::middleware(['auth:admin', EnsureAdminIsActive::class])->group(function () {
        Route::get('/', fn () => redirect()->route('admin.dashboard'));
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('analytics', [VisitorAnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('account', [AccountController::class, 'edit'])->name('account.edit');
        Route::put('account', [AccountController::class, 'update'])->name('account.update');

        // CRUD konten
        Route::resource('articles', AdminArticleController::class)->except('show');
        Route::resource('services', ServiceController::class)->except('show');
        Route::resource('faqs', FaqController::class)->except('show');
        Route::resource('advantages', AdvantageController::class)->except('show');
        Route::resource('statistics', StatisticController::class)->except('show');
        Route::resource('clients', ClientController::class)->except('show');
        Route::resource('testimonials', TestimonialController::class)->except('show');
        Route::resource('agendas', AgendaController::class)->except('show');
        Route::resource('applications', BusinessApplicationController::class);
        Route::resource('business-categories', BusinessCategoryController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('article-categories', ArticleCategoryController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.excel');
        Route::post('reports/export/csv', [ReportController::class, 'exportCsv'])->name('reports.csv');
        Route::get('reports/print', [ReportController::class, 'printView'])->name('reports.print');
        Route::get('reports/history/{reportExport}/download', [ReportController::class, 'download'])->name('reports.download');
        Route::delete('reports/history', [ReportController::class, 'clearHistory'])->name('reports.history.clear');
        Route::delete('reports/history/{reportExport}', [ReportController::class, 'destroyHistory'])->name('reports.history.destroy');

        // Editor Hero Utama: elemen internal hero dikelola di satu layar.
        Route::get('hero', [HeroController::class, 'edit'])->name('hero.edit');
        Route::put('hero', [HeroController::class, 'update'])->name('hero.update');
        Route::get('settings', [SiteSettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SiteSettingController::class, 'update'])->name('settings.update');
        Route::get('seo', [SeoController::class, 'edit'])->name('seo.edit');
        Route::put('seo', [SeoController::class, 'update'])->name('seo.update');

    });
});
