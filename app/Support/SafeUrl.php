<?php

namespace App\Support;

class SafeUrl
{
    private const GOOGLE_MAPS_HOSTS = [
        'google.com',
        'www.google.com',
        'maps.google.com',
        'google.co.id',
        'www.google.co.id',
        'maps.google.co.id',
        'maps.app.goo.gl',
        'goo.gl',
    ];

    public static function http(?string $value): ?string
    {
        $url = trim((string) $value);
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return $url !== ''
            && filter_var($url, FILTER_VALIDATE_URL)
            && in_array($scheme, ['http', 'https'], true)
                ? $url
                : null;
    }

    public static function googleMaps(?string $value): ?string
    {
        $url = self::http($value);
        if (! $url) {
            return null;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return in_array($host, self::GOOGLE_MAPS_HOSTS, true) ? $url : null;
    }

    /**
     * Normalisasi URL peta menjadi endpoint embed yang benar-benar boleh di-iframe.
     *
     * Google me-redirect (301) URL gaya lama `?output=embed` ke `/maps/embed`, dan respons
     * redirect itu membawa `X-Frame-Options: SAMEORIGIN` sehingga browser memblokir peta.
     * Endpoint `/maps/embed` sendiri tidak memakai header tersebut, jadi dipakai langsung.
     */
    public static function googleMapsEmbed(?string $value): ?string
    {
        $url = self::googleMaps($value);
        if (! $url) {
            return null;
        }

        // URL dari tombol "Sematkan peta" milik Google sudah berupa endpoint embed final.
        if (str_starts_with((string) parse_url($url, PHP_URL_PATH), '/maps/embed')) {
            return $url;
        }

        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        return self::googleMapsEmbedForPlace($query['q'] ?? $query['query'] ?? null);
    }

    /**
     * Bangun URL embed dari nama tempat/alamat bebas (tanpa perlu API key).
     */
    public static function googleMapsEmbedForPlace(?string $place): ?string
    {
        $place = trim((string) $place);

        return $place === ''
            ? null
            : 'https://www.google.com/maps/embed?origin=mfe&pb=!1m2!2m1!1s'.urlencode($place);
    }
}
