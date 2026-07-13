<!DOCTYPE html>
<html lang="id" class="login-root">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#080404">
    <title>Login Admin - PT Zam Zam Khan</title>
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/login.js'])
</head>
<body class="login-page font-sans antialiased">
    <div class="login-grid" aria-hidden="true"></div>
    <div class="login-glow login-glow--1" aria-hidden="true"></div>
    <div class="login-glow login-glow--2" aria-hidden="true"></div>

    <main class="login-layout">
        <section class="login-panel">
            <div class="login-shell">
                <div class="login-card">
                    <div class="login-heading">
                        <span class="login-eyebrow">
                            <span class="login-eyebrow-dot"></span>
                            Area administrator
                        </span>
                        <h2>Selamat datang kembali</h2>
                        <p>Masukkan kredensial administrator untuk melanjutkan ke dashboard.</p>
                    </div>

                    @if ($errors->any())
                        <div id="login-error" class="login-alert mt-5" role="alert" aria-live="polite">
                            <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <circle cx="12" cy="12" r="9"/>
                                <path stroke-linecap="round" d="M12 8v5m0 3h.01"/>
                            </svg>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <form id="admin-login-form" method="POST" action="{{ route('admin.login.attempt') }}" class="mt-6 space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="login-label">Email administrator</label>
                            <div class="login-field mt-2">
                                <svg class="login-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <rect x="3" y="5" width="18" height="14" rx="2"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4 7 8 6 8-6"/>
                                </svg>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                                       autocomplete="username" placeholder="admin@perusahaan.com"
                                       class="login-input @error('email') field-error @enderror"
                                       @error('email') aria-invalid="true" aria-describedby="login-error" @enderror>
                            </div>
                        </div>

                        <div>
                            <label for="password" class="login-label">Kata sandi</label>
                            <div class="login-field mt-2">
                                <svg class="login-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <rect x="4" y="10" width="16" height="11" rx="2"/>
                                    <path stroke-linecap="round" d="M8 10V7a4 4 0 0 1 8 0v3"/>
                                </svg>
                                <input id="password" name="password" type="password" required autocomplete="current-password"
                                       placeholder="Masukkan kata sandi"
                                       class="login-input login-input--pw @error('password') field-error @enderror"
                                       @error('password') aria-invalid="true" aria-describedby="login-error" @enderror>
                                <button id="password-toggle" type="button" class="login-pw-toggle"
                                        aria-label="Tampilkan kata sandi" aria-pressed="false">
                                    <svg id="password-visible-icon" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/>
                                        <circle cx="12" cy="12" r="2.5"/>
                                    </svg>
                                    <svg id="password-hidden-icon" hidden class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 6.2A10.8 10.8 0 0 1 12 6c6 0 9.5 6 9.5 6a16 16 0 0 1-2.1 2.8M6.2 6.2C3.8 8 2.5 12 2.5 12s3.5 6 9.5 6a9.8 9.8 0 0 0 3-.5M9.9 9.9a3 3 0 0 0 4.2 4.2"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn-primary login-submit">
                            <span>Masuk ke Dashboard</span>
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
                            </svg>
                        </button>
                    </form>

                    <div class="login-divider"><span>Akses terbatas</span></div>
                    <p class="login-security-note">
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3 5 6v5c0 4.6 2.9 8.1 7 10 4.1-1.9 7-5.4 7-10V6l-7-3Z"/>
                            <path stroke-linecap="round" d="m9 12 2 2 4-4"/>
                        </svg>
                        Gunakan hanya akun yang telah diotorisasi.
                    </p>
                </div>

                <a href="{{ route('home') }}" class="login-back">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/>
                    </svg>
                    Kembali ke website publik
                </a>
            </div>
        </section>
    </main>
</body>
</html>
