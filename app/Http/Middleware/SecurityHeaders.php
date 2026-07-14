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
            $directives = [
                "default-src 'self'",
                "base-uri 'self'",
                "object-src 'none'",
                "frame-ancestors 'none'",
                "form-action 'self'",
                // 'unsafe-eval' masih diperlukan Alpine.js: ekspresi seperti
                // x-data / :class dievaluasi lewat new Function(). Menghapusnya
                // menuntut migrasi ke build @alpinejs/csp dan penulisan ulang
                // ~129 ekspresi inline di seluruh Blade. Tidak ada 'unsafe-inline'
                // di script-src, jadi skrip yang disuntikkan tetap tertolak.
                "script-src 'self' 'nonce-{$nonce}' 'unsafe-eval'",
                "style-src 'self' 'nonce-{$nonce}'",
                "img-src 'self' data: blob: https:",
                "font-src 'self' data:",
                "connect-src 'self'",
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
}
