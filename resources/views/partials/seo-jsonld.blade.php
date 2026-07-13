@php
    $c = config('company');
    $services = collect(config('company.services', []))->map(fn ($s) => [
        '@type' => 'Offer',
        'itemOffered' => ['@type' => 'Service', 'name' => $s['title']],
    ])->values()->all();

    $jsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'ProfessionalService',
        'name' => $c['name'],
        'description' => $c['seo']['description'] ?? $c['about'] ?? null,
        'url' => url('/'),
        'telephone' => $c['phone_display'] ?? null,
        'email' => $c['email'] ?? null,
        'image' => $c['logo_url'] ?? asset('images/logo-zzk.png'),
        'logo' => $c['logo_url'] ?? asset('images/logo-zzk.png'),
        'priceRange' => '$$',
        'address' => filled($c['address'] ?? null) ? [
            '@type' => 'PostalAddress',
            'streetAddress' => $c['address'],
            'addressLocality' => $c['city'] ?? null,
            'addressCountry' => 'ID',
        ] : null,
        'areaServed' => filled($c['city'] ?? null) ? ['@type' => 'City', 'name' => $c['city']] : null,
        'sameAs' => collect(config('company.socials', []))->pluck('url')->filter()->values()->all(),
        'hasOfferCatalog' => $services ? [
            '@type' => 'OfferCatalog',
            'name' => 'Layanan Konsultasi & Legalitas Usaha',
            'itemListElement' => $services,
        ] : null,
    ];
    $jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
@endphp
<script type="application/ld+json" nonce="{{ $cspNonce }}">
{!! json_encode(array_filter($jsonLd, fn ($value) => $value !== null && $value !== []), $jsonFlags) !!}
</script>
