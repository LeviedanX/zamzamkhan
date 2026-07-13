@php
    $clients = collect(config('company.clients', []))
        ->filter(fn ($item) => is_array($item) && filled($item['name'] ?? null) && filled($item['image_url'] ?? null))
        ->values();
@endphp
@if(count($clients))
<section id="klien" class="client-section section relative overflow-hidden border-t border-navy-100 bg-white dark:border-white/10 dark:bg-navy-950">
    {{-- Background decorative subtle --}}
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <div class="vm-glow-pulse absolute -top-24 left-1/4 h-72 w-72 rounded-full bg-emerald-brand/[0.05] blur-3xl dark:bg-emerald-brand/15"></div>
        <div class="vm-glow-pulse absolute -bottom-24 right-0 h-72 w-72 rounded-full bg-tosca-400/[0.04] blur-3xl dark:bg-tosca-400/10"></div>
        <div class="hero-grid absolute inset-0 opacity-[0.03] dark:opacity-[0.05]"></div>
    </div>

    <div class="container-x relative">
        {{-- Header --}}
        <div class="client-header reveal mx-auto max-w-2xl text-center">
            <span class="eyebrow">Klien yang Telah Didampingi</span>
            <h2 class="mt-3 font-display text-3xl font-extrabold leading-tight text-navy-900 sm:text-4xl dark:text-white">
                Dipercaya oleh Berbagai Brand, UMKM, Kuliner, dan Hospitality
            </h2>
            <p class="mt-4 leading-relaxed text-navy-600 dark:text-navy-300">
                Sebagian klien yang telah mendapatkan pendampingan legalitas, sertifikasi halal, dan penguatan administrasi usaha bersama PT Zam Zam Khan.
            </p>
        </div>

        {{-- Grid logo (4x2 desktop) — card putih agar semua logo terbaca jelas --}}
        <div class="client-logo-grid reveal mx-auto mt-12 grid max-w-5xl grid-cols-2 gap-4 sm:gap-5 md:grid-cols-3 lg:grid-cols-4" data-reveal-delay="80">
            @foreach ($clients as $c)
                <article class="client-logo-card group relative flex min-h-32 flex-col items-center justify-center rounded-2xl border border-navy-100 bg-white p-5 shadow-md shadow-navy-900/5 transition duration-300 hover:-translate-y-1 hover:border-emerald-brand/40 hover:shadow-lg hover:shadow-navy-900/10 sm:p-6 dark:border-white/10 dark:shadow-black/20 dark:hover:border-tosca-400/50">
                    <img src="{{ $c['image_url'] }}"
                         alt="Logo {{ $c['name'] }}"
                         loading="lazy" decoding="async"
                         class="client-logo-image h-16 w-auto max-w-full object-contain sm:h-[4.5rem]">
                    @if (filled($c['industry'] ?? null))
                        <p class="mt-3 text-center text-xs font-semibold text-navy-500 dark:text-navy-300">{{ $c['industry'] }}</p>
                    @endif
                    @if (filled($c['url'] ?? null))
                        <a href="{{ $c['url'] }}" target="_blank" rel="noopener noreferrer" class="absolute inset-0 rounded-2xl focus:outline-none focus:ring-2 focus:ring-emerald-brand" aria-label="Kunjungi website {{ $c['name'] }}">
                            <span class="sr-only">Kunjungi website {{ $c['name'] }}</span>
                        </a>
                    @endif
                </article>
            @endforeach
        </div>

    </div>
</section>
@endif
