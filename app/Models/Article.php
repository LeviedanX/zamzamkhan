<?php

namespace App\Models;

use App\Support\PublicMedia;
use App\Support\SafeUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Article extends Model
{
    protected $guarded = [];

    protected $casts = [
        'published_at' => 'datetime',
        'exclude_from_sitemap' => 'boolean',
    ];

    // Template pesan WhatsApp per slug kategori. Fallback: konsultasi umum.
    private const WA_TEMPLATES = [
        'sertifikasi-halal' => 'Halo PT Zam Zam Khan, saya ingin konsultasi terkait layanan Sertifikat Halal. Mohon informasi persyaratan, alur pendampingan, dan estimasi prosesnya.',
        'legalitas-usaha' => 'Halo PT Zam Zam Khan, saya ingin konsultasi terkait pengurusan NIB. Mohon informasi persyaratan dan alur pendampingannya.',
        'bpom' => 'Halo PT Zam Zam Khan, saya ingin konsultasi terkait layanan BPOM. Mohon informasi dokumen awal, kategori produk, dan alur pengurusannya.',
        'haki' => 'Halo PT Zam Zam Khan, saya ingin konsultasi terkait layanan HAKI. Mohon informasi perlindungan merek, dokumen awal, dan proses pendaftarannya.',
        'perpajakan' => 'Halo PT Zam Zam Khan, saya ingin konsultasi terkait NPWP dan Pelaporan Pajak. Mohon dibantu informasi kebutuhan administrasi dan tahapannya.',
        'branding-kemasan' => 'Halo PT Zam Zam Khan, saya ingin konsultasi terkait Desain Logo dan Label Kemasan. Mohon informasi konsep desain, kebutuhan file, dan alur pengerjaannya.',
        '_default' => 'Halo PT Zam Zam Khan, saya ingin berkonsultasi terkait layanan halal, legalitas usaha, atau pengembangan bisnis. Mohon informasi layanan yang sesuai dengan kebutuhan usaha saya.',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'article_category_id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ---------------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------------
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->where(function (Builder $query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    /** Terbaru berdasarkan published_at, fallback created_at. */
    public function scopeLatestPublished(Builder $query): Builder
    {
        return $query->orderByRaw('COALESCE(published_at, created_at) DESC');
    }

    // ---------------------------------------------------------------------
    // Slug helper — SEO-friendly & unik, aman dari tabrakan.
    // ---------------------------------------------------------------------
    public static function uniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source) ?: 'artikel';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    // ---------------------------------------------------------------------
    // Presentasi
    // ---------------------------------------------------------------------
    public function whatsappUrl(): string
    {
        $slug = $this->category?->slug;
        $text = self::WA_TEMPLATES[$slug] ?? self::WA_TEMPLATES['_default'];

        $number = preg_replace('/\D+/', '', (string) config('company.whatsapp_number', config('company.phone_raw', '')));

        return 'https://wa.me/'.$number.'?text='.rawurlencode($text);
    }

    public function publishedDate(): ?string
    {
        $date = $this->published_at ?? $this->created_at;

        return $date?->locale('id')->translatedFormat('d F Y');
    }

    public function seoTitle(): string
    {
        return trim((string) $this->meta_title) ?: $this->title;
    }

    public function seoDescription(): string
    {
        $description = trim((string) $this->meta_description)
            ?: trim((string) $this->excerpt)
            ?: Str::limit(trim(strip_tags((string) $this->content)), 160, '');

        return $description ?: 'Artikel dan insight bisnis dari '.config('company.name', 'PT Zam Zam Khan').'.';
    }

    public function canonicalUrl(): string
    {
        return SafeUrl::http($this->canonical_url)
            ?: route('artikel.show', $this->slug);
    }

    public function robotsDirective(): string
    {
        $robots = in_array($this->seo_robots, ['index, follow', 'noindex, follow', 'noindex, nofollow'], true)
            ? $this->seo_robots
            : 'index, follow';

        return str_starts_with($robots, 'index')
            ? $robots.', max-image-preview:large, max-snippet:-1, max-video-preview:-1'
            : $robots;
    }

    public function socialTitle(): string
    {
        return trim((string) $this->og_title) ?: $this->seoTitle();
    }

    public function socialDescription(): string
    {
        return trim((string) $this->og_description) ?: $this->seoDescription();
    }

    public function articleImageUrl(): ?string
    {
        return PublicMedia::previewUrl($this->og_image_path)
            ?: PublicMedia::previewUrl($this->cover_image);
    }

    public function socialImageUrl(): string
    {
        return $this->articleImageUrl()
            ?: (config('company.logo_url') ?: asset('images/logo-zzk.png'));
    }

    public function isIndexable(): bool
    {
        return ! str_starts_with((string) $this->seo_robots, 'noindex');
    }

    public function isSitemapEligible(): bool
    {
        $self = rtrim(route('artikel.show', $this->slug), '/');

        return ! $this->exclude_from_sitemap
            && $this->isIndexable()
            && rtrim($this->canonicalUrl(), '/') === $self;
    }
}
