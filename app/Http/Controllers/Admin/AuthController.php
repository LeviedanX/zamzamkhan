<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
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

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik."])
                ->setStatusCode(429);
        }

        if (Auth::guard('admin')->attempt([...$credentials, 'is_active' => true])) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            $admin = Auth::guard('admin')->user();
            $admin->forceFill(['last_login_at' => now()])->save();

            return redirect()->intended(route('admin.dashboard'));
        }

        RateLimiter::hit($throttleKey, 60);

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Email atau kata sandi salah, atau akun tidak aktif.']);
    }

    private function throttleKey(string $email, ?string $ip): string
    {
        return 'admin-login:'.hash('sha256', $email.'|'.($ip ?? 'unknown'));
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
