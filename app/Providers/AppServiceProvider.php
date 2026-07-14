<?php

namespace App\Providers;

use App\Models\Advantage;
use App\Models\Agenda;
use App\Models\Article;
use App\Models\Client;
use App\Models\Faq;
use App\Models\HeroSection;
use App\Models\SeoSetting;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Statistic;
use App\Models\Testimonial;
use App\Support\SafeUrl;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    private const CACHE_KEY = 'site_content_v5';

    public function register(): void
    {
        //
    }

    /**
     * Paksa seluruh URL yang dihasilkan memakai skema HTTPS di produksi.
     *
     * Sengaja dikunci ke APP_URL yang berskema https, bukan sekadar
     * environment('production'): tanpa itu, environment production yang masih
     * memakai APP_URL http (mis. saat uji coba lokal) akan menghasilkan URL
     * https yang tidak bisa dijangkau. Redirect http→https itu sendiri tetap
     * ditangani web server, karena request http idealnya tidak sampai ke PHP.
     */
    private function enforceHttps(): void
    {
        $scheme = parse_url((string) config('app.url'), PHP_URL_SCHEME);

        if ($this->app->environment('production') && $scheme === 'https') {
            URL::forceScheme('https');
            $this->app['request']->server->set('HTTPS', 'on');
        }
    }

    public function boot(): void
    {
        $this->enforceHttps();

        // Selalu daftarkan: simpan/hapus konten di admin → bersihkan cache (perubahan langsung tampil).
        $this->registerCacheFlush();

        // Request admin/aset tidak butuh override konten publik → hemat query, lebih cepat.
        if (($this->app->runningInConsole() && ! $this->app->runningUnitTests()) || request()->is('admin/*')) {
            return;
        }

        $company = $this->cachedSiteContent();
        if ($company) {
            config(['company' => $this->presentForRequest($company)]);
        }
    }

    /**
     * Bagian konten yang tidak boleh ikut membeku bersama cache 6 jam, karena
     * bergantung pada request: URL aset (mengikuti host) dan status agenda
     * (mengikuti waktu). Dihitung ulang tiap request di atas payload cache.
     */
    private function presentForRequest(array $company): array
    {
        return $this->materializeAssetUrls($this->withLiveAgendaState($company));
    }

    /**
     * Agenda dapat kedaluwarsa di tengah masa cache (6 jam), sehingga daftar hasil
     * cache bisa memuat agenda yang sebenarnya sudah selesai. Saring ulang tiap
     * request (murni operasi array, tanpa query) agar daftar agenda, section, dan
     * menu navbar selalu sepakat — tidak ada menu "Agenda" yang menunjuk ke section
     * yang sudah hilang.
     */
    private function withLiveAgendaState(array $company): array
    {
        $company['agendas'] = collect($company['agendas'] ?? [])
            ->filter(function ($item) {
                if (! is_array($item) || blank($item['title'] ?? null)) {
                    return false;
                }

                $finish = $item['ends_at'] ?? $item['starts_at'] ?? null;

                return blank($finish) || Carbon::parse($finish)->isFuture();
            })
            ->values()
            ->all();

        if (! empty($company['agendas'])) {
            return $company;
        }

        // Cache dibersihkan setiap agenda disimpan/dihapus, jadi daftar hanya bisa
        // menyusut di sini — tidak pernah bertambah. Aman untuk mencabut navigasinya.
        $company['section_visibility']['agenda'] = false;
        $company['nav'] = collect($company['nav'] ?? [])
            ->reject(fn ($item) => ltrim((string) ($item['anchor'] ?? ''), '#') === 'agenda')
            ->values()
            ->all();

        return $company;
    }

    /**
     * Ambil config('company') hasil merge DB (di-cache). Fallback aman ke config statis.
     */
    private function cachedSiteContent(): ?array
    {
        try {
            if (! Schema::hasTable('site_settings')) {
                return null;
            }

            return Cache::remember(self::CACHE_KEY, now()->addHours(6), fn () => $this->buildSiteContent());
        } catch (\Throwable $e) {
            return null; // DB bermasalah → pakai config statis.
        }
    }

    /**
     * Bangun array config('company') lengkap dari database (hanya jalan saat cache miss).
     */
    private function buildSiteContent(): array
    {
        $company = $this->normalizeLegacyContent(config('company'));

        if ($s = SiteSetting::first()) {
            // Record setting yang sudah ada adalah sumber kebenaran. Nilai null
            // harus tetap menimpa fallback agar admin benar-benar dapat mengosongkan field.
            $overrides = [
                'name' => $s->company_name,
                'tagline' => $s->tagline,
                'phone_display' => $s->phone,
                'phone_raw' => $s->phone ? preg_replace('/\D/', '', $s->phone) : null,
                'email' => $s->email,
                'address' => $s->address,
                'operating_hours' => $s->operating_hours,
                'about' => $s->company_description,
                'vision' => $s->vision,
                'mission' => $s->mission,
                'maps_url' => SafeUrl::googleMaps($s->maps_url),
                // Field kosong = admin sengaja menyembunyikan peta. Bila field diisi tetapi
                // bentuknya tidak bisa diurai (mis. shortlink), peta tetap tampil memakai alamat.
                'maps_embed' => filled($s->maps_embed_url)
                    ? (SafeUrl::googleMapsEmbed($s->maps_embed_url) ?? SafeUrl::googleMapsEmbedForPlace($s->address))
                    : null,
            ];

            $wa = $this->normalizeWhatsapp($s->whatsapp);
            $overrides['whatsapp_number'] = $wa;
            $overrides['whatsapp'] = null;
            if ($wa) {
                $text = rawurlencode('Halo '.($s->company_name ?: 'PT Zam Zam Khan').', saya ingin berkonsultasi mengenai layanan Anda.');
                // Single source of truth: nomor admin mengisi kedua key yang dipakai Blade/JS.
                $overrides['whatsapp'] = "https://wa.me/{$wa}?text={$text}";
            }

            if ($logo = $this->publicAssetPath($s->logo_path)) {
                $overrides['logo_url'] = $logo;
            }

            $company = array_merge($company, $overrides);

            $socials = collect($s->social_links ?: [])
                ->map(fn ($item) => [
                    'label' => $item['label'] ?? null,
                    'handle' => $item['label'] ?? null,
                    'url' => SafeUrl::http($item['url'] ?? null),
                ])
                ->filter(fn ($item) => filled($item['label']) && filled($item['url']))
                ->values()
                ->all();

            if (! $socials) {
                foreach ([['Instagram', $s->instagram_url], ['Facebook', $s->facebook_url], ['TikTok', $s->tiktok_url]] as [$label, $url]) {
                    if ($safeUrl = SafeUrl::http($url)) {
                        $socials[] = ['label' => $label, 'handle' => $label, 'url' => $safeUrl];
                    }
                }
            }
            $company['socials'] = $socials;
        }

        if ($h = HeroSection::where('is_active', true)->latest('updated_at')->latest('id')->first()) {
            $company['hero'] = [
                'title' => $h->title,
                'subtitle' => $h->subtitle,
                'secondary_text' => $h->secondary_button_text,
                'image_url' => $this->publicAssetPath($h->image_path),
                'badge_text' => $h->badge_text,
                'trust_text' => $h->trust_text,
                'service_chips' => collect(preg_split('/\r\n|\r|\n/', (string) $h->service_chips))->map(fn ($x) => trim($x))->filter()->values()->all(),
                'portrait_url' => $this->publicAssetPath($h->portrait_path),
                'portrait_alt' => $h->portrait_alt,
                'portrait_role' => $h->portrait_role,
                'portrait_name' => $h->portrait_name,
            ];
        }

        // Layanan unggulan (is_featured) tampil lebih dulu, lalu urutan tampil.
        $services = Service::where('is_active', true)
            ->orderByDesc('is_featured')
            ->orderBy('display_order')
            ->get();
        // Fallback statis hanya bila admin belum pernah mengelola layanan (tabel kosong).
        // Bila ada baris tetapi semua nonaktif → array kosong → section disembunyikan (hormati admin).
        if (Service::exists()) {
            $company['services'] = $services->map(fn ($x) => [
                'icon' => $x->icon ?: 'halal',
                'title' => $x->title,
                'desc' => $x->summary ?: $x->description,
                'detail' => $x->description ?: $x->summary,
                'benefits' => collect(preg_split('/\r\n|\r|\n/', (string) $x->benefits))->map(fn ($line) => trim($line))->filter()->values()->all(),
                'suitable_for' => $x->suitable_for,
                'workflow_steps' => collect(preg_split('/\r\n|\r|\n/', (string) $x->workflow_steps))->map(fn ($line) => trim($line))->filter()->values()->all(),
                'whatsapp_message' => $x->whatsapp_message,
                'is_featured' => $x->is_featured,
            ])->all();
        }

        $faqs = Faq::where('is_active', true)->orderBy('display_order')->get();
        // Sama seperti layanan: fallback statis hanya bila tabel FAQ benar-benar kosong.
        if (Faq::exists()) {
            $company['faq'] = $faqs->map(fn ($x) => [
                'q' => $x->question,
                'a' => $x->answer,
            ])->all();
        }

        if ($seo = SeoSetting::where('page_key', 'home')->first()) {
            $company['seo'] = [
                'title' => $seo->meta_title,
                'description' => $seo->meta_description,
                'keywords' => $seo->meta_keywords,
                'og_title' => $seo->og_title ?: $seo->meta_title,
                'og_description' => $seo->og_description ?: $seo->meta_description,
                'og_image' => $this->publicAssetPath($seo->og_image_path) ?? 'images/logo-zzk.png',
                'canonical' => SafeUrl::http($seo->canonical_url),
            ];
        }

        if (Schema::hasTable('advantages')) {
            $company['advantages'] = Advantage::where('is_active', true)->orderBy('display_order')->get()
                ->map(fn ($x) => ['icon' => $x->icon, 'title' => $x->title, 'text' => $x->description])->all();
        }
        if (Schema::hasTable('statistics')) {
            $company['stats'] = Statistic::where('is_active', true)->orderBy('display_order')->get()
                ->map(fn ($x) => ['value' => $x->value, 'label' => $x->label, 'description' => $x->description])->all();
        }
        if (Schema::hasTable('clients')) {
            $company['clients'] = Client::where('is_active', true)->orderBy('display_order')->get()
                ->map(fn ($x) => ['name' => $x->name, 'image_url' => $this->publicAssetPath($x->logo_path), 'url' => SafeUrl::http($x->website_url), 'industry' => $x->industry])
                ->filter(fn ($x) => filled($x['image_url']))->values()->all();
        }
        if (Schema::hasTable('testimonials')) {
            $company['testimonials'] = Testimonial::where('is_active', true)->orderBy('display_order')->get()
                ->map(fn ($x) => ['title' => $x->client_name, 'service' => $x->service_name, 'caption' => $x->content, 'image_url' => $this->publicAssetPath($x->image_path), 'alt' => $x->image_alt])
                ->filter(fn ($x) => filled($x['image_url']))->values()->all();
        }
        if (Schema::hasTable('agendas')) {
            // Tampilkan selama agenda belum selesai (termasuk yang sedang berlangsung).
            // Dulu filternya memakai starts_at >= awal hari ini, sehingga agenda lintas
            // hari hilang sebelum benar-benar selesai dan agenda hari ini tetap tampil
            // meski jamnya sudah lewat.
            $company['agendas'] = Agenda::where('is_active', true)
                ->upcoming()
                ->orderBy('display_order')
                ->orderBy('starts_at')
                ->get()
                ->map(fn ($x) => [
                    'title' => $x->title,
                    'summary' => $x->summary ?: $x->description,
                    'venue' => $x->venue,
                    'starts_at' => $x->starts_at->toIso8601String(),
                    'date' => $x->starts_at->locale('id')->translatedFormat('d F Y'),
                    'time' => $x->starts_at->format('H:i').' WIB',
                    'ends_at' => $x->ends_at?->toIso8601String(),
                    'end_date' => $x->ends_at?->locale('id')->translatedFormat('d F Y'),
                    'end_time' => $x->ends_at?->format('H:i').' WIB',
                    'registration_url' => SafeUrl::http($x->registration_url),
                    'image_url' => $this->publicAssetPath($x->image_path),
                ])
                ->all();
        }

        $sectionVisibility = [
            'tentang' => filled($company['about'] ?? null),
            'visi-misi' => filled($company['vision'] ?? null) || filled($company['mission'] ?? null),
            'layanan' => ! empty($company['services']),
            'keunggulan' => ! empty($company['advantages']),
            'artikel' => Schema::hasTable('articles') && Article::published()->exists(),
            // Sama seperti section lain: sembunyi saat tidak ada agenda aktif, agar
            // homepage tidak memuat section kosong. Section (beserta menu navbar-nya)
            // muncul kembali otomatis begitu admin menjadwalkan agenda.
            'agenda' => ! empty($company['agendas']),
            'statistik' => ! empty($company['stats']),
            'klien' => ! empty($company['clients']),
            'testimoni' => ! empty($company['testimonials']),
            'faq' => ! empty($company['faq']),
            'kontak' => collect([
                $company['phone_display'] ?? null,
                $company['whatsapp_number'] ?? null,
                $company['email'] ?? null,
                $company['address'] ?? null,
                $company['maps_url'] ?? null,
                $company['maps_embed'] ?? null,
            ])->contains(fn ($value) => filled($value)),
        ];

        $company['section_visibility'] = $sectionVisibility;
        $company['nav'] = collect($company['nav'] ?? [])
            ->filter(function (array $item) use ($sectionVisibility) {
                $section = ltrim((string) ($item['anchor'] ?? ''), '#');

                return $section === '' || ($sectionVisibility[$section] ?? true);
            })
            ->values()
            ->all();

        return $company;
    }

    /**
     * Samakan kontrak data fallback lama dengan payload database.
     * Ini menjaga homepage tetap aman sebelum migration baru dijalankan.
     */
    private function normalizeLegacyContent(array $company): array
    {
        $icons = ['clipboard', 'chat', 'users', 'shield', 'star', 'pin'];
        $company['advantages'] = collect($company['advantages'] ?? [])
            ->map(function ($item, int $index) use ($icons) {
                if (is_array($item)) {
                    return [
                        'icon' => (string) ($item['icon'] ?? $icons[$index % count($icons)]),
                        'title' => (string) ($item['title'] ?? ''),
                        'text' => (string) ($item['text'] ?? $item['description'] ?? ''),
                    ];
                }

                $text = trim((string) $item);

                return ['icon' => $icons[$index % count($icons)], 'title' => $text, 'text' => $text];
            })
            ->filter(fn (array $item) => $item['title'] !== '')
            ->values()
            ->all();

        $company['stats'] = collect($company['stats'] ?? [])
            ->filter(fn ($item) => is_array($item) && isset($item['value'], $item['label']))
            ->map(fn (array $item) => [
                'value' => (string) $item['value'],
                'label' => (string) $item['label'],
                'description' => (string) ($item['description'] ?? ''),
            ])
            ->values()
            ->all();

        $company['clients'] = collect($company['clients'] ?? [])
            ->filter(fn ($item) => is_array($item) && filled($item['name'] ?? null))
            ->map(fn (array $item) => [
                'name' => (string) $item['name'],
                'image_url' => $item['image_url'] ?? (isset($item['img']) ? asset('images/Logo/'.$item['img']) : null),
                'url' => $item['url'] ?? null,
                'industry' => $item['industry'] ?? null,
            ])
            ->filter(fn (array $item) => filled($item['image_url']))
            ->values()
            ->all();

        $company['testimonials'] = collect($company['testimonials'] ?? [])
            ->filter(fn ($item) => is_array($item) && filled($item['title'] ?? null))
            ->map(fn (array $item) => [
                'title' => (string) $item['title'],
                'service' => (string) ($item['service'] ?? ''),
                'caption' => (string) ($item['caption'] ?? $item['content'] ?? ''),
                'image_url' => $item['image_url'] ?? (isset($item['img']) ? asset('images/testimonials/'.$item['img']) : null),
                'alt' => (string) ($item['alt'] ?? ''),
            ])
            ->filter(fn (array $item) => filled($item['image_url']))
            ->values()
            ->all();

        $company['agendas'] = collect($company['agendas'] ?? [])
            ->filter(fn ($item) => is_array($item) && filled($item['title'] ?? null))
            ->values()
            ->all();

        return $company;
    }

    /**
     * Normalisasi nomor WhatsApp ke format internasional Indonesia tanpa +, spasi, atau strip.
     * 08xxxx / 8xxxx → 62xxxx. Sudah 62xxxx dibiarkan.
     */
    private function normalizeWhatsapp(?string $value): ?string
    {
        $number = preg_replace('/\D/', '', (string) $value);

        if ($number === '') {
            return null;
        }

        if (str_starts_with($number, '0')) {
            $number = '62'.substr($number, 1);
        } elseif (str_starts_with($number, '8')) {
            $number = '62'.$number;
        }

        return $number;
    }

    /**
     * Path aset relatif (mis. "images/logo.png" atau "storage/foo.jpg") untuk disimpan
     * di cache. Sengaja TIDAK memanggil asset(): URL absolut ikut membekukan host/port
     * request yang kebetulan menghangatkan cache, sehingga gambar mati bila situs
     * diakses dari host/port lain selama cache masih hidup. URL absolut dibentuk ulang
     * per request di materializeAssetUrls().
     */
    private function publicAssetPath(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $normalized = ltrim($path, '/');
        if (str_starts_with($normalized, 'images/') && is_file(public_path($normalized))) {
            return $normalized;
        }

        return Storage::disk('public')->exists($path) ? 'storage/'.$path : null;
    }

    /**
     * Ubah path aset hasil cache menjadi URL absolut memakai host request saat ini.
     * asset() mengembalikan URL yang sudah absolut apa adanya, jadi aman dipanggil
     * pada nilai fallback statis yang memang sudah berupa URL penuh.
     */
    private function materializeAssetUrls(array $company): array
    {
        $toUrl = fn ($path) => filled($path) ? asset($path) : null;

        $company['logo_url'] = $toUrl($company['logo_url'] ?? null);

        foreach (['image_url', 'portrait_url'] as $key) {
            if (isset($company['hero'][$key])) {
                $company['hero'][$key] = $toUrl($company['hero'][$key]);
            }
        }

        if (isset($company['seo']['og_image'])) {
            $company['seo']['og_image'] = $toUrl($company['seo']['og_image']);
        }

        foreach (['clients', 'testimonials', 'agendas'] as $group) {
            if (empty($company[$group])) {
                continue;
            }

            $company[$group] = array_map(function (array $item) use ($toUrl) {
                $item['image_url'] = $toUrl($item['image_url'] ?? null);

                return $item;
            }, $company[$group]);
        }

        return $company;
    }

    /**
     * Bersihkan cache konten setiap kali model konten disimpan/dihapus dari admin.
     */
    private function registerCacheFlush(): void
    {
        $models = [SiteSetting::class, HeroSection::class, Service::class, Faq::class, SeoSetting::class, Advantage::class, Statistic::class, Client::class, Testimonial::class, Agenda::class, Article::class];
        $forget = fn () => Cache::forget(self::CACHE_KEY);

        foreach ($models as $model) {
            $model::saved($forget);
            $model::deleted($forget);
        }
    }
}
