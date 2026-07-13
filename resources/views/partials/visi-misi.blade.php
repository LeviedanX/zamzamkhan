@php($pillars = [
    ['title' => 'Legal', 'desc' => 'Mendukung usaha agar lebih tertata dan patuh secara administratif.', 'icon' => 'shield'],
    ['title' => 'Strategis', 'desc' => 'Memberikan arahan yang relevan untuk pengembangan usaha.', 'icon' => 'compass'],
    ['title' => 'Halal', 'desc' => 'Mengutamakan proses bisnis yang aman, tepat, dan sesuai prinsip syariah.', 'icon' => 'badge'],
])
@php($missionCms = config('company.mission'))
@php($visionCms = config('company.vision'))
@php($missions = collect(preg_split('/\r\n|\r|\n/', (string) $missionCms, -1, PREG_SPLIT_NO_EMPTY))->map(fn ($item) => trim($item))->filter()->values())
@if (filled($visionCms) || $missions->isNotEmpty())
<section id="visi-misi" class="vision-mission-section section relative overflow-hidden border-t border-navy-100 bg-white dark:border-white/10 dark:bg-navy-950">
    {{-- Background decorative subtle (glow orbs + grid + watermark ZZK) --}}
    <div class="vm-bg-decoration pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <div class="vm-bg-glow vm-bg-glow--1 vm-glow-pulse"></div>
        <div class="vm-bg-glow vm-bg-glow--2 vm-glow-pulse"></div>
        <div class="vm-bg-grid absolute inset-0"></div>
        <span class="vm-bg-watermark">ZZK</span>
    </div>

    <div class="container-x relative">
        {{-- Header --}}
        <div class="vm-header reveal mx-auto max-w-2xl text-center">
            <span class="eyebrow">Visi &amp; Misi</span>
            <h2 class="mt-3 font-display text-3xl font-extrabold leading-tight text-navy-900 sm:text-4xl dark:text-white">
                Arah dan Komitmen PT Zam Zam Khan
            </h2>
            <p class="mt-4 leading-relaxed text-navy-600 dark:text-navy-300">
                Kami hadir untuk membantu pelaku usaha berkembang secara legal, strategis, dan lebih siap bersaing melalui pendampingan bisnis yang terarah.
            </p>
        </div>

        {{-- Visual framework simbolik: Legal – Strategis – Halal --}}
        <div class="vm-framework reveal mx-auto mt-10 max-w-3xl" data-reveal-delay="80">
            <div class="vm-framework-card relative overflow-hidden rounded-3xl border p-6 sm:p-8">
                <div class="vm-framework-inner-glow vm-glow-pulse" aria-hidden="true"></div>

                <div class="vm-framework-inner">
                    {{-- Connector lines (desktop, inline SVG) — hanya mengisi celah antar-kartu --}}
                    <svg class="vm-framework-svg" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                        <g class="vm-line-base" fill="none">
                            <line x1="50" y1="31.5" x2="50" y2="24"/>
                            <line x1="34" y1="67.5" x2="25" y2="76.5"/>
                            <line x1="66" y1="67.5" x2="75" y2="76.5"/>
                        </g>
                        <g class="vm-line-shimmer" fill="none">
                            <line x1="50" y1="31.5" x2="50" y2="24"/>
                            <line x1="34" y1="67.5" x2="25" y2="76.5"/>
                            <line x1="66" y1="67.5" x2="75" y2="76.5"/>
                        </g>
                    </svg>

                    {{-- Center node --}}
                    <div class="vm-framework-center">
                        <div class="vm-framework-center-inner vm-float">
                            <span class="vm-framework-badge">ZZK</span>
                            <p class="vm-framework-center-title">PT Zam Zam Khan</p>
                            <p class="vm-framework-center-sub">Bisnis &amp; Legal Konsultan</p>
                            <span class="vm-framework-caption">Pendampingan Usaha Terarah</span>
                        </div>
                    </div>

                    {{-- Node Halal (atas) --}}
                    <div class="vm-framework-node vm-framework-node--halal">
                        <div class="vm-framework-node-inner">
                            <span class="vm-framework-icon">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M8.5 12.5l2.4 2.4 4.6-5.1"/></svg>
                            </span>
                            <div class="vm-framework-node-body">
                                <p class="vm-framework-title">Halal</p>
                                <p class="vm-framework-text">Prinsip aman &amp; sesuai syariah</p>
                            </div>
                        </div>
                    </div>

                    {{-- Node Legal (kiri) --}}
                    <div class="vm-framework-node vm-framework-node--legal">
                        <div class="vm-framework-node-inner">
                            <span class="vm-framework-icon">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v5c0 4.2-2.8 7.9-7 9-4.2-1.1-7-4.8-7-9V6l7-3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4.5"/></svg>
                            </span>
                            <div class="vm-framework-node-body">
                                <p class="vm-framework-title">Legal</p>
                                <p class="vm-framework-text">Usaha tertata secara administrasi</p>
                            </div>
                        </div>
                    </div>

                    {{-- Node Strategis (kanan) --}}
                    <div class="vm-framework-node vm-framework-node--strategic">
                        <div class="vm-framework-node-inner">
                            <span class="vm-framework-icon">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 9l-2 4-4 2 2-4 4-2z"/></svg>
                            </span>
                            <div class="vm-framework-node-body">
                                <p class="vm-framework-title">Strategis</p>
                                <p class="vm-framework-text">Arahan relevan untuk berkembang</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Grid Visi & Misi --}}
        <div class="vm-grid mt-12 grid gap-6 {{ filled($visionCms) && $missions->isNotEmpty() ? 'lg:grid-cols-2' : 'lg:grid-cols-1' }} lg:gap-8">
            {{-- VISI --}}
            @if (filled($visionCms))
            <article class="vm-card vm-vision-card reveal reveal-left relative overflow-hidden rounded-3xl border border-emerald-brand/20 bg-linear-to-br from-white to-navy-50/70 p-7 shadow-xl shadow-navy-900/5 sm:p-9 dark:border-emerald-brand/30 dark:from-white/8 dark:to-white/2 dark:shadow-black/20">
                <span class="absolute inset-x-0 top-0 h-1 bg-linear-to-r from-emerald-brand via-tosca-400 to-emerald-brand"></span>
                <div class="flex items-center gap-3">
                    <span class="flex h-12 w-12 flex-none items-center justify-center rounded-2xl bg-emerald-brand text-white shadow-lg shadow-emerald-brand/30">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4.5"/><circle cx="12" cy="12" r="1"/></svg>
                    </span>
                    <h3 class="font-display text-2xl font-extrabold text-navy-900 dark:text-white">Visi</h3>
                </div>
                <p class="mt-5 text-[15px] leading-relaxed text-navy-700 dark:text-navy-200">
                    {{ $visionCms }}
                </p>
            </article>
            @endif

            {{-- MISI --}}
            @if ($missions->isNotEmpty())
            <article class="vm-card vm-mission-card reveal reveal-right relative overflow-hidden rounded-3xl border border-navy-100 bg-white p-7 shadow-xl shadow-navy-900/5 sm:p-9 dark:border-white/10 dark:bg-white/5 dark:shadow-black/20">
                <div class="flex items-center gap-3">
                    <span class="flex h-12 w-12 flex-none items-center justify-center rounded-2xl border border-emerald-brand/20 bg-emerald-brand/10 text-emerald-brand dark:border-emerald-brand/30 dark:bg-emerald-brand/20 dark:text-tosca-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5.25l2.25 2.25L9 3.75M3 12l2.25 2.25L9 10.5M3 18.75L5.25 21 9 17.25M12.75 6h8.25M12.75 12.75H21M12.75 19.5H21"/></svg>
                    </span>
                    <h3 class="font-display text-2xl font-extrabold text-navy-900 dark:text-white">Misi</h3>
                </div>
                <ul class="vm-mission-list mt-5 space-y-4">
                    @foreach ($missions as $i => $m)
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-6 w-6 flex-none items-center justify-center rounded-full bg-emerald-brand text-[12px] font-bold text-white dark:bg-tosca-400">{{ $i + 1 }}</span>
                            <span class="text-[15px] leading-relaxed text-navy-700 dark:text-navy-200">{{ $m }}</span>
                        </li>
                    @endforeach
                </ul>
            </article>
            @endif
        </div>

        {{-- 3 Pilar komitmen --}}
        <div class="vm-pillars reveal mt-6 grid gap-4 sm:grid-cols-3">
            @foreach ($pillars as $p)
                <div class="vm-pillar-card flex items-start gap-3.5 rounded-2xl border border-navy-100 bg-navy-50/60 p-5 transition duration-300 hover:-translate-y-1 hover:border-emerald-brand/40 hover:shadow-lg hover:shadow-navy-900/10 dark:border-white/10 dark:bg-white/5 dark:hover:border-tosca-400/40">
                    <span class="flex h-11 w-11 flex-none items-center justify-center rounded-xl bg-emerald-brand/10 text-emerald-brand dark:bg-emerald-brand/20 dark:text-tosca-400">
                        @if ($p['icon'] === 'shield')
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M12 3l7 3v5c0 4.2-2.8 7.9-7 9-4.2-1.1-7-4.8-7-9V6l7-3z"/></svg>
                        @elseif ($p['icon'] === 'compass')
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M14.8 9.2l-1.7 4-4 1.7 1.7-4 4-1.7z"/></svg>
                        @else
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M12 2.5l2.3 1.7 2.85-.2.9 2.7 2.3 1.7-.9 2.7.9 2.7-2.3 1.7-.9 2.7-2.85-.2L12 21.5l-2.3-1.7-2.85.2-.9-2.7L3.65 15.6l.9-2.7-.9-2.7 2.3-1.7.9-2.7 2.85.2L12 2.5z"/></svg>
                        @endif
                    </span>
                    <div>
                        <p class="font-display text-base font-bold text-navy-900 dark:text-white">{{ $p['title'] }}</p>
                        <p class="mt-1 text-sm leading-relaxed text-navy-600 dark:text-navy-300">{{ $p['desc'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
