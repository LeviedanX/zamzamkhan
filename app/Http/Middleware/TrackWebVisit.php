<?php

namespace App\Http\Middleware;

use App\Models\WebVisit;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackWebVisit
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldTrack($request, $response)) {
            return $response;
        }

        $userAgent = (string) $request->userAgent();
        try {
            WebVisit::create([
                // Session di-hash agar laporan tidak menyimpan IP atau identifier mentah.
                'visitor_key' => hash_hmac('sha256', $request->session()->getId(), (string) config('app.key')),
                'path' => mb_substr('/'.ltrim($request->path(), '/'), 0, 500),
                'route_name' => mb_substr((string) $request->route()?->getName(), 0, 160) ?: null,
                'referrer_host' => $this->referrerHost($request),
                'device_type' => $this->deviceType($userAgent),
                'visited_at' => now(),
            ]);
        } catch (QueryException) {
            // Telemetri tidak boleh membuat website publik gagal ketika tabel belum siap.
        }

        return $response;
    }

    private function shouldTrack(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET') || $request->is('admin', 'admin/*', 'up', 'sitemap.xml', 'robots.txt')) {
            return false;
        }

        $contentType = strtolower((string) $response->headers->get('Content-Type'));
        $userAgent = (string) $request->userAgent();

        return $response->getStatusCode() >= 200
            && $response->getStatusCode() < 400
            && str_contains($contentType, 'text/html')
            && ! preg_match('/bot|crawler|spider|slurp|headless|preview|facebookexternalhit/i', $userAgent);
    }

    private function referrerHost(Request $request): ?string
    {
        $host = parse_url((string) $request->headers->get('referer'), PHP_URL_HOST);

        return is_string($host) ? mb_substr(strtolower($host), 0, 255) : null;
    }

    private function deviceType(string $userAgent): string
    {
        return match (true) {
            preg_match('/ipad|tablet|kindle|silk/i', $userAgent) === 1 => 'tablet',
            preg_match('/mobile|android|iphone|ipod/i', $userAgent) === 1 => 'mobile',
            default => 'desktop',
        };
    }
}
