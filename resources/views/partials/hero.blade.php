@php
    $hero = config('company.hero');
    $hero = is_array($hero) ? $hero : [];
    $trust = ! empty($hero['service_chips'])
        ? $hero['service_chips']
        : ['Sertifikasi Halal', 'Legalitas Usaha', 'BPOM & HAKI', 'Logo & Label Kemasan'];
    $defaultPortraitPath = public_path('images/buzamzami.png');
    $defaultPortraitUrl = asset('images/buzamzami.png').'?v='.(is_file($defaultPortraitPath) ? filemtime($defaultPortraitPath) : '1');
    $configuredPortraitUrl = $hero['portrait_url'] ?? null;
    $configuredPortraitPath = $configuredPortraitUrl ? parse_url($configuredPortraitUrl, PHP_URL_PATH) : null;
    $portraitUrl = ! $configuredPortraitPath || str_ends_with($configuredPortraitPath, '/images/buzamzami.png')
        ? $defaultPortraitUrl
        : $configuredPortraitUrl;
@endphp
<section id="hero" x-data="{ y: 0 }" @scroll.window.passive="y = window.scrollY"
         class="noise relative overflow-hidden bg-navy-950 text-white">
    {{-- ============ Background cityscape (bg1.webp) â€” layering rapi ============ --}}
    <div class="hero-bg pointer-events-none absolute inset-0" aria-hidden="true">
        {{-- 1. Cityscape bg1.webp (parallax halus).
             Sumber gambar via asset() â†’ URL origin Laravel (bukan Vite),
             agar bg tetap tampil saat mode dev (Vite serve) maupun produksi. --}}
        <div class="hero-bg-image absolute inset-0"
             :style="{ transform: `scale(1.08) translateY(${y * 0.05}px)` }"></div>
        @if (! empty($hero['image_url']))
            {{-- URL berasal dari database, jadi tidak bisa ditaruh di stylesheet statis.
                 Dipasang lewat data-bg + CSSOM agar CSP tidak butuh style-src unsafe-inline. --}}
            <div class="absolute inset-0 bg-cover bg-center opacity-55 mix-blend-screen"
                 data-bg="{{ $hero['image_url'] }}"
                 :style="{ transform: `scale(1.06) translateY(${y * 0.035}px)` }"></div>
            <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(6,4,4,0.96)_0%,rgba(20,6,8,0.9)_38%,rgba(42,10,12,0.76)_66%,rgba(8,5,7,0.88)_100%)]"></div>
            <div class="absolute inset-0 bg-[radial-gradient(55%_50%_at_72%_36%,rgba(153,27,27,0.42),transparent_72%)]"></div>
        @endif
        {{-- 2. Overlay gelap kiri untuk keterbacaan teks --}}
        <div class="hero-overlay absolute inset-0"></div>
        {{-- 3. Grid sangat halus --}}
        <div class="hero-grid absolute inset-0 opacity-[0.10]"></div>
        {{-- 4. Ambient red glow kanan (bergerak pelan) + beam shimmer --}}
        <div class="hero-light absolute inset-0"></div>
        <div class="hero-beam absolute inset-0"></div>
    </div>

    <div class="container-x relative z-10 grid items-center gap-12 pb-28 pt-36 md:pt-40 lg:grid-cols-12 lg:gap-8 lg:pb-36">
        <div class="hero-copy lg:col-span-7">
            <span class="hero-badge reveal inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-1.5 text-xs font-medium tracking-wide text-tosca-400 backdrop-blur">
                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-tosca-400"></span>
                {{ $hero['badge_text'] ?? 'Konsultan Bisnis & Legal — Kota Malang' }}
            </span>

            <h1 class="reveal mt-6 font-display text-[2.6rem] font-extrabold leading-[1.08] tracking-tight sm:text-5xl lg:text-[3.5rem]" data-reveal-delay="90">
                @if ($hero && ! empty($hero['title']))
                    {{ $hero['title'] }}
                @else
                    Konsultan Halal, Legalitas &amp;
                    <span class="text-gradient animate-gradient">Branding Usaha</span>
                    di Malang
                @endif
            </h1>

            <p class="reveal mt-6 max-w-160 text-base leading-relaxed text-navy-100 sm:text-lg" data-reveal-delay="180">
                {{ $hero['subtitle'] ?? 'PT Zam Zam Khan mendampingi UMKM dan badan usaha dalam pengurusan sertifikasi halal, legalitas usaha, BPOM, HAKI, NPWP, akta pendirian, serta desain logo dan label kemasan produk.' }}
            </p>

            {{-- Trust line --}}
            <div class="hero-trust reveal mt-6 inline-flex items-center gap-2.5 text-sm text-navy-100" data-reveal-delay="230">
                <span class="hero-trust-badge inline-flex h-6 w-6 items-center justify-center rounded-full bg-tosca-400/15 text-tosca-400 ring-1 ring-tosca-400/30">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </span>
                {{ $hero['trust_text'] ?? 'Dipercaya 500++ pelaku usaha dan badan usaha.' }}
            </div>

            @if (! empty(config('company.services')))
            <div class="reveal mt-8 flex" data-reveal-delay="290">
                <a href="#layanan" class="hero-service-cta">
                    <span>{{ $hero['secondary_text'] ?? 'Lihat Layanan' }}</span>
                    <span class="hero-service-cta__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0-5-5m5 5-5 5"/>
                        </svg>
                    </span>
                </a>
            </div>
            @endif

            {{-- Service chips --}}
            <div class="reveal mt-8 flex flex-wrap items-center gap-2.5" data-reveal-delay="360">
                @foreach ($trust as $badge)
                    <span class="hero-chip inline-flex items-center gap-1.5 rounded-full border border-white/10 bg-white/6 px-3.5 py-1.5 text-xs font-medium text-navy-100 backdrop-blur">
                        <svg class="h-3.5 w-3.5 text-tosca-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        {{ $badge }}
                    </span>
                @endforeach
            </div>
        </div>

        {{-- Visual foto penyelia halal (cutout menyatu, tanpa card) --}}
        <div class="hero-visual reveal reveal-scale relative lg:col-span-5 lg:mt-6" data-reveal-delay="220">
            {{-- Soft red back glow di belakang kepala & bahu --}}
            <div class="hero-portrait-glow" aria-hidden="true"></div>
            <div class="hero-portrait relative z-10 mx-auto w-[72%] max-w-72.5 sm:max-w-82.5 lg:mr-2 lg:w-full lg:max-w-112.5">
                <img src="{{ $portraitUrl }}"
                     alt="{{ $hero['portrait_alt'] ?? 'Direktur PT Zam Zam Khan, Dra. Atfiah El Zam Zami, MM.' }}"
                     width="624" height="779" decoding="async" fetchpriority="high"
                     class="hero-portrait-img w-full select-none">

                {{-- Caption: title/nama Direktur, menempel di bawah figur --}}
                <div class="hero-portrait-caption pointer-events-none absolute inset-x-0 bottom-3 z-2 px-3 text-center">
                    <span class="mx-auto mb-2 block h-0.5 w-9 rounded-full bg-linear-to-r from-transparent via-tosca-400 to-transparent"></span>
                    <p class="hero-portrait-role font-display text-base font-bold uppercase tracking-[0.14em] text-white sm:text-lg">{{ $hero['portrait_role'] ?? 'Direktur' }}</p>
                    <p class="hero-portrait-name mt-1 text-xs font-medium text-white/85 sm:text-[13px]">{{ $hero['portrait_name'] ?? 'Dra. Atfiah El Zam Zami, MM.' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Moving wave divider (transisi ke section berikutnya) --}}
    <div class="relative -mb-px text-white dark:text-navy-950" aria-hidden="true">
        <div class="wave-track wave-slow">
            <svg class="h-12 md:h-16" viewBox="0 0 1440 60" preserveAspectRatio="none" fill="currentColor"><path d="M0 60V22C180 46 360 4 540 12C720 20 900 54 1080 50C1260 46 1350 20 1440 26V60H0Z"/></svg>
            <svg class="h-12 md:h-16" viewBox="0 0 1440 60" preserveAspectRatio="none" fill="currentColor"><path d="M0 60V22C180 46 360 4 540 12C720 20 900 54 1080 50C1260 46 1350 20 1440 26V60H0Z"/></svg>
        </div>
    </div>
</section>
