<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = Vite::useCspNonce();
        View::share('cspNonce', $nonce);

        $response = $next($request);

        // PHP dan Symfony sama-sama bisa mengiklankan versi runtime; keduanya dicabut.
        $response->headers->remove('X-Powered-By');
        if (function_exists('header_remove')) {
            header_remove('X-Powered-By');
        }

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        if ($request->is('admin', 'admin/*')) {
            $response->headers->set('Cache-Control', 'no-store, private');
            $response->headers->set('Pragma', 'no-cache');
        }

        if (config('security.csp_enabled')) {
            [$viteHttpSource, $viteWebSocketSource] = $this->localViteSources();
            $viteAssetSource = $viteHttpSource ? ' '.$viteHttpSource : '';
            $localDataConnectSource = app()->environment('local') ? ' data:' : '';
            $viteConnectSources = $viteHttpSource
                ? ' '.$viteHttpSource.' '.$viteWebSocketSource
                : '';

            $directives = [
                "default-src 'self'",
                "base-uri 'self'",
                "object-src 'none'",
                "frame-ancestors 'none'",
                "form-action 'self'",
                "script-src 'self' 'nonce-{$nonce}'",
                "style-src 'self' 'nonce-{$nonce}'",
                "img-src 'self' data: blob: https:{$viteAssetSource}",
                "font-src 'self' data:{$viteAssetSource}",
                "connect-src 'self'{$localDataConnectSource}{$viteConnectSources}",
                'frame-src https://www.google.com https://maps.google.com https://www.google.co.id',
                "media-src 'self'",
                "worker-src 'self' blob:",
                "manifest-src 'self'",
            ];

            if (app()->environment('production') && $request->isSecure()) {
                $directives[] = 'upgrade-insecure-requests';
            }

            $response->headers->set('Content-Security-Policy', implode('; ', $directives));
        }

        if (config('security.hsts_enabled') && $request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age='.max(300, (int) config('security.hsts_max_age')).'; includeSubDomains'
            );
        }

        return $response;
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function localViteSources(): array
    {
        if (! app()->environment('local') || ! Vite::isRunningHot()) {
            return [null, null];
        }

        $hotUrl = trim((string) @file_get_contents(Vite::hotFile()));
        $parts = parse_url($hotUrl);

        if (! is_array($parts)) {
            return [null, null];
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower(trim((string) ($parts['host'] ?? ''), '[]'));
        $port = isset($parts['port']) ? (int) $parts['port'] : null;

        if (! in_array($scheme, ['http', 'https'], true)
            || ! in_array($host, ['127.0.0.1', 'localhost', '::1'], true)
            || $port === null
            || $port < 1
            || $port > 65535) {
            return [null, null];
        }

        $cspHost = $host === '::1' ? '[::1]' : $host;
        $httpSource = sprintf('%s://%s:%d', $scheme, $cspHost, $port);
        $webSocketScheme = $scheme === 'https' ? 'wss' : 'ws';
        $webSocketSource = sprintf('%s://%s:%d', $webSocketScheme, $cspHost, $port);

        return [$httpSource, $webSocketSource];
    }
}
