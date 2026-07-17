<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAdminLoginEntry
{
    public const SESSION_KEY = 'admin_login_entry_expires_at';

    public const HANDOFF_KEY = 'admin_login_entry_handoff';

    public function handle(Request $request, Closure $next): Response
    {
        $expiresAt = $request->session()->get(self::SESSION_KEY);

        if (! is_numeric($expiresAt) || (int) $expiresAt < now()->timestamp) {
            $request->session()->forget([self::SESSION_KEY, self::HANDOFF_KEY]);

            return redirect()->route('home');
        }

        if ($request->isMethod('GET') && ! $request->session()->pull(self::HANDOFF_KEY, false)) {
            $request->session()->forget(self::SESSION_KEY);

            return redirect()->route('home');
        }

        // Izinkan redirect balik setelah validasi atau autentikasi gagal.
        if ($request->isMethod('POST')) {
            $request->session()->put(self::HANDOFF_KEY, true);
        }

        return $next($request);
    }
}
