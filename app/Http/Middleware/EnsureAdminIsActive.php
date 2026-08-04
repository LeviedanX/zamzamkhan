<?php

namespace App\Http\Middleware;

use App\Support\AdminSecurity;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = $request->user('admin');

        if (! $admin) {
            return $next($request);
        }

        $sessionVersion = $request->session()->get(AdminSecurity::SESSION_AUTH_VERSION);
        $now = now()->timestamp;
        $startedAt = $request->session()->get(AdminSecurity::SESSION_STARTED_AT);
        $lastActivityAt = $request->session()->get(AdminSecurity::SESSION_LAST_ACTIVITY_AT);
        $idleLimit = max(300, (int) config('admin.session_idle_seconds', 1800));
        $absoluteLimit = max($idleLimit, (int) config('admin.session_absolute_seconds', 28800));

        $reason = match (true) {
            ! $admin->is_admin => 'not-admin',
            ! $admin->is_active => 'inactive',
            is_numeric($sessionVersion) && (int) $sessionVersion !== (int) $admin->auth_version => 'revoked',
            is_numeric($startedAt) && $now - (int) $startedAt > $absoluteLimit => 'absolute-timeout',
            is_numeric($lastActivityAt) && $now - (int) $lastActivityAt > $idleLimit => 'idle-timeout',
            default => null,
        };

        if ($reason) {
            $this->invalidate($request, $admin->getAuthIdentifier(), $reason);

            return redirect()->route('home');
        }

        // actingAs() pada test tidak melewati login; produksi selalu mendapat marker ini saat login.
        $request->session()->put(AdminSecurity::SESSION_LAST_ACTIVITY_AT, $now);

        return $next($request);
    }

    private function invalidate(Request $request, mixed $adminId, string $reason): void
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Log::channel('security')->warning('admin_session_invalidated', [
            'admin_id' => $adminId,
            'reason' => $reason,
        ]);
    }
}
