<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDesktopAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isMobileDevice($request)) {
            return response()
                ->view('errors.admin-desktop-only', status: 403)
                ->header('Cache-Control', 'no-store, private')
                ->header('X-Robots-Tag', 'noindex, nofollow');
        }

        return $next($request);
    }

    private function isMobileDevice(Request $request): bool
    {
        if (str_contains((string) $request->header('Sec-CH-UA-Mobile'), '?1')) {
            return true;
        }

        $userAgent = (string) $request->userAgent();

        if ($userAgent === '') {
            return false;
        }

        return (bool) preg_match(
            '/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile|Tablet|Silk|Kindle/i',
            $userAgent,
        );
    }
}
