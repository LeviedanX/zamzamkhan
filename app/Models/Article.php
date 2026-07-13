<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Article extends Model
{
    protected $guarded = [];

    protected $casts = [
        'published_at' => 'datetime',
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
}
