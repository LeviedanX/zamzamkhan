@php
    $advantages = collect(config('company.advantages', []))
        ->filter(fn ($item) => is_array($item) && filled($item['title'] ?? null))
        ->values();
@endphp
@if(count($advantages))
<section id="keunggulan" class="advantage-section section relative overflow-hidden border-t border-navy-100 bg-navy-50 dark:border-white/10 dark:bg-navy-950">
    {{-- Background decorative subtle --}}
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <div class="advantage-bg-glow advantage-bg-glow--1"></div>
        <div class="advantage-bg-glow advantage-bg-glow--2"></div>
        <div class="hero-grid absolute inset-0 opacity-[0.03] dark:opacity-[0.05]"></div>
    </div>

    <div class="advantage-grid container-x relative grid items-center gap-10 lg:grid-cols-12 lg:gap-14">
        {{-- ============ KIRI: visual authority (buzamzami2.webp) ============ --}}
        <div class="advantage-visual reveal reveal-left relative lg:col-span-5">
            <div class="advantage-glow-behind" aria-hidden="true"></div>
            <div class="advantage-visual-card group relative z-10 mx-auto w-full max-w-md overflow-hidden rounded-[1.85rem] border lg:max-w-none">
                <div class="relative aspect-3/4 w-full overflow-hidden">
                    <img src="{{ asset('images/buzamzami2.webp') }}"
                         alt="Penyelia halal PT Zam Zam Khan bersama dokumen sertifikasi halal dan panduan kepatuhan halal"
                         width="1086" height="1448" loading="lazy" decoding="async"
                         class="advantage-image h-full w-full object-cover object-center transition duration-700 group-hover:scale-[1.04]">
                    <div class="pointer-events-none absolute inset-x-0 bottom-0 h-1/4 bg-linear-to-t from-black/45 to-transparent"></div>
                </div>
            </div>
        </div>

        {{-- ============ KANAN: konten ============ --}}
        <div class="advantage-content reveal reveal-right lg:col-span-7" data-reveal-delay="120">
            <span class="eyebrow">Keunggulan</span>
            <h2 class="mt-3 font-display text-3xl font-extrabold leading-tight text-navy-900 sm:text-4xl dark:text-white">
                Mengapa Memilih PT Zam Zam Khan?
            </h2>
            <p class="mt-4 max-w-xl leading-relaxed text-navy-600 dark:text-navy-300">
                PT Zam Zam Khan menghadirkan pendampingan yang jelas, terarah, dan komunikatif agar kebutuhan halal, legalitas, sertifikasi, dan identitas usaha dapat berjalan lebih tertata.
            </p>

            {{-- value statement --}}
            <div class="mt-5 flex items-start gap-3 rounded-xl border border-emerald-brand/15 bg-emerald-brand/4 px-4 py-3 dark:border-emerald-brand/25 dark:bg-emerald-brand/10">
                <svg class="mt-0.5 h-5 w-5 flex-none text-emerald-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm font-medium text-navy-700 dark:text-navy-200">Bukan sekadar konsultasi, tetapi pendampingan yang membantu pelaku usaha memahami kebutuhan, menyiapkan dokumen, dan mengambil langkah yang tepat.</p>
            </div>

            {{-- 6 keunggulan --}}
            <div class="advantage-list mt-6 grid gap-3.5 sm:grid-cols-2">
                @foreach ($advantages as $a)
                    <div class="advantage-card group flex flex-col rounded-2xl border border-navy-100 bg-white p-4 transition duration-300 hover:-translate-y-1 hover:border-emerald-brand/40 hover:shadow-lg hover:shadow-navy-900/10 sm:p-5 dark:border-white/10 dark:bg-white/5 dark:hover:border-tosca-400/40">
                        <span class="advantage-card-icon flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-emerald-brand/10 text-emerald-brand transition-colors group-hover:bg-emerald-brand group-hover:text-white dark:bg-emerald-brand/20 dark:text-tosca-400 dark:group-hover:bg-tosca-400 dark:group-hover:text-white">
                            @switch($a['icon'])
                                @case('clipboard')
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-5.5 9l1.8 1.8L15 11"/></svg>
                                    @break
                                @case('chat')
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10.5h8M8 14h5M21 11.5a8 8 0 01-8 8H7.5L3 22.5V11.5a8 8 0 1118 0z"/></svg>
                                    @break
                                @case('users')
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3"/><path stroke-linecap="round" stroke-linejoin="round" d="M3.5 20a5.5 5.5 0 0111 0M16 6.2a3 3 0 010 5.6M17 20a5.5 5.5 0 00-2.7-4.2"/></svg>
                                    @break
                                @case('shield')
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v5c0 4.2-2.8 7.9-7 9-4.2-1.1-7-4.8-7-9V6l7-3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4.5"/></svg>
                                    @break
                                @case('star')
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3.2l2.6 5.3 5.8.8-4.2 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8L3.4 9.3l5.8-.8L12 3.2z"/></svg>
                                    @break
                                @default
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-6.3 7-11a7 7 0 10-14 0c0 4.7 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                            @endswitch
                        </span>
                        <p class="mt-3 font-display text-[15px] font-bold text-navy-900 dark:text-white">{{ $a['title'] }}</p>
                        <p class="mt-1 text-[13px] leading-relaxed text-navy-600 dark:text-navy-300">{{ $a['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
