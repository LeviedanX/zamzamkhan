<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\RequireAdminLoginEntry;
use App\Support\AdminSecurity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function requestAccess(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        // Rotasi ID sesi sebelum membuka entry login untuk mencegah session fixation.
        $request->session()->regenerate();
        $request->session()->put([
            RequireAdminLoginEntry::SESSION_KEY => now()
                ->addSeconds(max(60, (int) config('admin.login_entry_ttl_seconds', 600)))
                ->timestamp,
            RequireAdminLoginEntry::HANDOFF_KEY => true,
        ]);

        return redirect()->route('admin.login');
    }

    public function showLogin()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $throttleKey = $this->throttleKey($credentials['email'], $request->ip());
        $ipThrottleKey = $this->ipThrottleKey($request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5) || RateLimiter::tooManyAttempts($ipThrottleKey, 20)) {
            $seconds = max(
                RateLimiter::availableIn($throttleKey),
                RateLimiter::availableIn($ipThrottleKey),
            );
            Log::channel('security')->warning('admin_login_rate_limited', [
                'email_hash' => hash('sha256', $credentials['email']),
                'ip_hash' => $this->ipHash($request->ip()),
            ]);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik."])
                ->setStatusCode(429);
        }

        if (Auth::guard('admin')->attempt([...$credentials, 'is_admin' => true, 'is_active' => true])) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();
            $request->session()->forget([
                RequireAdminLoginEntry::SESSION_KEY,
                RequireAdminLoginEntry::HANDOFF_KEY,
            ]);

            $admin = Auth::guard('admin')->user();
            $admin->forceFill(['last_login_at' => now()])->save();
            $now = now()->timestamp;
            $request->session()->put([
                AdminSecurity::SESSION_AUTH_VERSION => (int) $admin->auth_version,
                AdminSecurity::SESSION_STARTED_AT => $now,
                AdminSecurity::SESSION_LAST_ACTIVITY_AT => $now,
            ]);
            Log::channel('security')->notice('admin_login_succeeded', [
                'admin_id' => $admin->getAuthIdentifier(),
                'ip_hash' => $this->ipHash($request->ip()),
            ]);

            return redirect()->intended(route('admin.dashboard'));
        }

        RateLimiter::hit($throttleKey, 60);
        RateLimiter::hit($ipThrottleKey, 60);
        Log::channel('security')->warning('admin_login_failed', [
            'email_hash' => hash('sha256', $credentials['email']),
            'ip_hash' => $this->ipHash($request->ip()),
        ]);

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Email atau kata sandi salah, atau akun tidak aktif.']);
    }

    private function throttleKey(string $email, ?string $ip): string
    {
        return 'admin-login:'.hash('sha256', $email.'|'.($ip ?? 'unknown'));
    }

    private function ipThrottleKey(?string $ip): string
    {
        return 'admin-login-ip:'.hash('sha256', $ip ?? 'unknown');
    }

    private function ipHash(?string $ip): string
    {
        return hash_hmac('sha256', $ip ?? 'unknown', (string) config('app.key'));
    }

    public function logout(Request $request)
    {
        $adminId = Auth::guard('admin')->id();
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Log::channel('security')->notice('admin_logout', [
            'admin_id' => $adminId,
            'ip_hash' => $this->ipHash($request->ip()),
        ]);

        return redirect()->route('home');
    }
}
