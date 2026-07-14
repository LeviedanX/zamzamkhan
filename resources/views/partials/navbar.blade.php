@php
    $nav = collect(config('company.nav', []))->values();
@endphp
@php($navBase = request()->routeIs('home') ? '' : route('home'))
<header
    x-data="siteNav"
    @keydown.escape.window="closeMenu()"
    :class="scrolled ? 'bg-white/80 dark:bg-navy-900/80 shadow-lg shadow-navy-900/5 backdrop-blur-md' : 'bg-transparent'"
    class="fixed inset-x-0 top-0 z-50 transition-all duration-300"
>
    <nav class="container-x flex items-center justify-between gap-3 py-3.5 lg:py-4" aria-label="Navigasi utama">
        {{-- Brand --}}
        <a href="{{ $navBase }}#hero" class="brand-area flex min-w-0 flex-none items-center gap-2.5">
            <img src="{{ config('company.logo_url') ?: asset('images/logo-zzk.webp') }}" alt="Logo PT Zam Zam Khan" width="400" height="263" decoding="async" class="h-11 w-auto sm:h-12 lg:h-14">
            <span class="hidden whitespace-nowrap leading-[1.1] sm:block">
                <span :class="scrolled ? 'text-navy-900 dark:text-white' : 'text-white'" class="block font-display text-[15px] font-bold transition-colors">{{ config('company.name', 'PT Zam Zam Khan') }}</span>
                <span :class="scrolled ? 'text-navy-500 dark:text-navy-300' : 'text-navy-100'" class="block text-[11px] transition-colors">{{ config('company.tagline', 'Bisnis & Legal Konsultan') }}</span>
            </span>
        </a>

        {{-- Desktop menu (flat, tanpa dropdown) --}}
        <ul class="nav-menu hidden min-w-0 items-center gap-1 rounded-full border border-white/10 bg-navy-950/40 p-1.5 shadow-lg shadow-black/20 ring-1 ring-inset ring-white/5 backdrop-blur-xl xl:flex">
            @foreach ($nav as $item)
                @php($id = ltrim($item['anchor'], '#'))
                <li class="nav-item">
                    <a href="{{ $navBase.$item['anchor'] }}"
                       :aria-current="active === '{{ $id }}' ? 'true' : 'false'"
                       :class="active === '{{ $id }}'
                            ? 'nav-link-active bg-emerald-brand text-white shadow-sm shadow-emerald-brand/40'
                            : 'text-white/70 hover:bg-white/10 hover:text-white'"
                       class="nav-link block whitespace-nowrap rounded-full px-3.5 py-2 text-sm font-semibold transition-all duration-200 ease-out active:scale-95">
                        {{ $item['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>

        {{-- Aksi kanan: utility saja (CTA WhatsApp dipindah ke floating button) --}}
        <div class="nav-actions hidden flex-none items-center gap-2 xl:flex">
            @include('partials.theme-toggle', ['dynamicColor' => true])
            <a href="{{ route('admin.login') }}"
               aria-label="Login Admin"
               data-tooltip="Login Admin"
               class="nav-admin-login">
                <svg class="nav-admin-login__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v5c0 4.2-2.9 7.6-7 8.7C7.9 18.6 5 15.2 5 11V6l7-3z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 11.5a1.6 1.6 0 1 0 0-3.2 1.6 1.6 0 0 0 0 3.2zM9.6 15.2c.3-1.2 1.3-1.9 2.4-1.9s2.1.7 2.4 1.9"/>
                </svg>
                <span class="sr-only">Login Admin</span>
            </a>
        </div>

        {{-- Mobile / tablet: theme toggle + hamburger --}}
        <div class="flex flex-none items-center gap-2 xl:hidden">
            <button type="button" @click="$store.theme.toggle()" aria-label="Ganti tema"
                    class="mobile-nav-icon-btn inline-flex h-11 w-11 items-center justify-center rounded-xl border transition"
                    :class="scrolled ? 'border-navy-200/80 bg-white/85 text-navy-800 hover:border-emerald-brand/60 dark:border-white/15 dark:bg-white/10 dark:text-navy-100' : 'border-white/15 bg-white/10 text-white hover:border-tosca-400/55'">
                <svg x-show="!$store.theme.dark" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                <svg x-show="$store.theme.dark" x-cloak class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="4"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32l1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41m11.32-11.32l1.41-1.41"/></svg>
            </button>
            <button
                type="button"
                @click="openMenu()"
                class="mobile-nav-icon-btn inline-flex h-11 w-11 items-center justify-center rounded-xl border transition"
                :class="scrolled ? 'border-navy-200/80 bg-white/85 text-navy-800 hover:border-emerald-brand/60 dark:border-white/15 dark:bg-white/10 dark:text-navy-100' : 'border-white/15 bg-white/10 text-white hover:border-tosca-400/55'"
                aria-label="Buka menu"
                aria-controls="mobile-site-drawer"
                :aria-expanded="open ? 'true' : 'false'">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
        </div>
    </nav>

    {{-- Teleport mencegah drawer fixed terikat ke tinggi header saat backdrop-filter aktif. --}}
    <template x-teleport="body">
        <div
            x-show="open" x-cloak
            @keydown.escape.window="closeMenu()"
            @keyup.escape.window="closeMenu()"
            role="dialog" aria-modal="true" aria-label="Menu navigasi"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="site-drawer-layer fixed inset-0 z-[80] xl:hidden">
            <div class="absolute inset-0 bg-navy-950/65 backdrop-blur-sm" @click="closeMenu()" aria-hidden="true"></div>
            <div id="mobile-site-drawer"
                 class="site-drawer-panel absolute right-0 top-0 flex w-[min(22rem,calc(100vw-1.25rem))] flex-col overflow-hidden rounded-l-2xl border-l border-navy-100 bg-white shadow-2xl shadow-black/30 dark:border-white/10 dark:bg-navy-900"
                 x-transition:enter="transition ease-out duration-250" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
            <div class="site-drawer-header flex items-center justify-between gap-4 border-b border-navy-100 px-5 py-4 dark:border-white/10">
                <a href="{{ $navBase }}#hero" class="flex min-w-0 items-center gap-3" @click="closeMenu()">
                    <img src="{{ config('company.logo_url') ?: asset('images/logo-zzk.webp') }}" alt="Logo PT Zam Zam Khan" width="400" height="263" decoding="async" class="h-11 w-auto flex-none">
                    <span class="min-w-0">
                        <span class="block truncate font-display text-sm font-bold text-navy-900 dark:text-white">{{ config('company.name', 'PT Zam Zam Khan') }}</span>
                        <span class="block truncate text-xs text-navy-500 dark:text-navy-300">{{ config('company.tagline', 'Bisnis & Legal Konsultan') }}</span>
                    </span>
                </a>
                <button type="button" @click="closeMenu()" x-ref="drawerClose" aria-label="Tutup menu" class="inline-flex h-10 w-10 flex-none items-center justify-center rounded-xl border border-navy-200 text-navy-600 transition hover:border-emerald-brand hover:text-emerald-brand dark:border-white/15 dark:text-navy-200 dark:hover:text-white">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>
            <ul class="site-drawer-nav min-h-0 flex-1 space-y-1 overflow-y-auto px-4 py-4">
                @foreach ($nav as $item)
                    @php($id = ltrim($item['anchor'], '#'))
                    <li>
                        <a href="{{ $navBase.$item['anchor'] }}" @click="closeMenu()"
                           :class="active === '{{ $id }}' ? 'bg-red-tint text-emerald-brand ring-1 ring-emerald-brand/15 dark:bg-tosca-500/15 dark:text-red-200' : 'text-navy-700 hover:bg-navy-50 hover:text-emerald-brand dark:text-navy-200 dark:hover:bg-white/5'"
                           class="block rounded-xl px-4 py-3 text-sm font-semibold transition-colors">{{ $item['label'] }}</a>
                    </li>
                @endforeach
            </ul>
            {{-- Tautan admin sengaja tidak ditaruh di drawer mobile: EnsureDesktopAdminAccess
                 memblokir akses panel admin dari perangkat mobile di level server (403),
                 jadi tautan di sini hanya akan jadi dead-end bagi pengunjung mobile. --}}
            </div>
        </div>
    </template>
</header>
