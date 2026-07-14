<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex,nofollow">
    <title>Panel Admin Khusus Desktop</title>
    @vite('resources/css/app.css')
</head>
<body class="min-h-dvh bg-[#080405] font-sans text-white antialiased">
    <main class="grid min-h-dvh place-items-center px-5 py-10">
        <section class="w-full max-w-md rounded-3xl border border-white/10 bg-white/5 p-6 text-center shadow-2xl shadow-black/40 backdrop-blur sm:p-8" aria-labelledby="desktop-only-title">
            <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-red-900/30 text-red-200 ring-1 ring-red-400/20" aria-hidden="true">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="3" y="4" width="18" height="12" rx="2"/><path stroke-linecap="round" d="M8 20h8M12 16v4"/>
                </svg>
            </span>
            <p class="mt-5 text-xs font-bold uppercase tracking-[0.18em] text-red-200/80">Akses dibatasi</p>
            <h1 id="desktop-only-title" class="mt-2 font-display text-2xl font-extrabold">Panel admin hanya tersedia di desktop</h1>
            <p class="mt-3 text-sm leading-6 text-white/65">Gunakan komputer atau laptop untuk mengakses dan mengelola panel admin.</p>
            <a href="{{ route('home') }}" class="mt-6 inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-red-800 px-5 text-sm font-bold transition hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-400/25">
                Kembali ke website
            </a>
        </section>
    </main>
</body>
</html>
