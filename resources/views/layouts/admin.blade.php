<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csp-nonce" content="{{ $cspNonce }}">
    <title>@yield('title', 'Panel Admin') - PT Zam Zam Khan</title>
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">
    <script nonce="{{ $cspNonce }}">
        (function () {
            try {
                var t = localStorage.getItem('theme');
                if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {}
        })();
    </script>
    <style @if (! empty($cspNonce)) nonce="{{ $cspNonce }}" @endif>
        /* Background kritis mencegah flash putih sebelum stylesheet utama siap. */
        :root {
            background-color: #f7f1f1;
            color-scheme: light;
        }
        :root.dark {
            background-color: #0f0d0e;
            color-scheme: dark;
        }
        html, body { min-height: 100%; }
        body.admin-shell {
            margin: 0;
            background-color: #f7f1f1;
        }
        :root.dark body.admin-shell { background-color: #0f0d0e; }

        :root { view-transition-name: none; }
        ::view-transition-old(admin-content) {
            animation: admin-content-out 120ms ease-out both;
        }
        ::view-transition-new(admin-content) {
            animation: admin-content-in 220ms cubic-bezier(.22, 1, .36, 1) both;
        }
        ::view-transition-old(admin-title) {
            animation: admin-title-out 100ms ease-out both;
        }
        ::view-transition-new(admin-title) {
            animation: admin-title-in 180ms cubic-bezier(.22, 1, .36, 1) both;
        }
        @keyframes admin-content-out {
            from, to { opacity: 1; transform: translateY(0); }
        }
        @keyframes admin-content-in {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes admin-title-out {
            to { opacity: .65; transform: translateY(-2px); }
        }
        @keyframes admin-title-in {
            from { opacity: 0; transform: translateY(3px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (prefers-reduced-motion: reduce) {
            ::view-transition-old(admin-content),
            ::view-transition-new(admin-content),
            ::view-transition-old(admin-title),
            ::view-transition-new(admin-title) { animation: none; }
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    class="admin-shell min-h-screen font-sans antialiased"
    x-data="{ menuOpen: false, deleteOpen: false, deleteAction: '', deleteName: '' }"
    x-bind:class="{ 'admin-body-locked': menuOpen }"
    x-bind:data-admin-theme="$store.theme.dark ? 'dark' : 'light'"
    x-effect="document.body.classList.toggle('admin-body-locked', menuOpen)"
    @keydown.escape.window="menuOpen = false; deleteOpen = false"
    @open-delete-modal.window="deleteAction = $event.detail.action; deleteName = $event.detail.name || 'data ini'; deleteOpen = true"
>
    <a href="#admin-main-content" class="sr-only z-[100] rounded-lg bg-white px-4 py-3 font-semibold text-navy-900 shadow-lg focus:fixed focus:left-4 focus:top-4 focus:not-sr-only">
        Lewati ke konten utama
    </a>
    @auth('admin')
        @php
            $navSections = [
                ['label' => 'Konten Website', 'description' => 'Semua bagian yang tampil ke publik.', 'items' => [
                    ['route' => 'admin.hero.edit', 'label' => 'Hero Utama', 'active' => ['admin.hero.*'], 'icon' => 'M4 6h16v12H4V6Zm3 9 3-3 2 2 3-4 2 5H7Z', 'description' => 'Pembuka homepage.'],
                    ['route' => 'admin.settings.edit', 'label' => 'Profil & Identitas', 'active' => ['admin.settings.*'], 'icon' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 9a7 7 0 0 1 14 0H5Z', 'description' => 'Brand dan kontak.'],
                    ['route' => 'admin.services.index', 'label' => 'Layanan', 'active' => ['admin.services.*'], 'icon' => 'M9 6h6M9 12h6M9 18h6M5 6h.01M5 12h.01M5 18h.01', 'description' => 'Daftar layanan.'],
                    ['route' => 'admin.advantages.index', 'label' => 'Keunggulan', 'active' => ['admin.advantages.*'], 'icon' => 'M12 3l2.7 5.5 6.1.9-4.4 4.3', 'description' => 'Nilai perusahaan.'],
                    ['route' => 'admin.statistics.index', 'label' => 'Statistik', 'active' => ['admin.statistics.*'], 'icon' => 'M5 20V10h3v10H5Zm6 0V4h3v16h-3Z', 'description' => 'Angka pencapaian.'],
                    ['route' => 'admin.clients.index', 'label' => 'Klien', 'active' => ['admin.clients.*'], 'icon' => 'M8 11a3 3 0 1 0 0-6M3 20a5 5 0 0 1 10 0', 'description' => 'Logo klien.'],
                    ['route' => 'admin.testimonials.index', 'label' => 'Testimoni', 'active' => ['admin.testimonials.*'], 'icon' => 'M5 6h14v10H9l-4 3V6Z', 'description' => 'Bukti sosial.'],
                    ['route' => 'admin.agendas.index', 'label' => 'Agenda', 'active' => ['admin.agendas.*'], 'icon' => 'M6 3v3M18 3v3M4 9h16M5 5h14v15H5V5Z', 'description' => 'Jadwal publik.'],
                    ['route' => 'admin.articles.index', 'label' => 'Artikel & Insight', 'active' => ['admin.articles.*'], 'icon' => 'M5 4h10l4 4v12H5V4Zm10 0v5h5', 'description' => 'Konten dan insight.'],
                    ['route' => 'admin.article-categories.index', 'label' => 'Kategori Artikel', 'active' => ['admin.article-categories.*'], 'icon' => 'M4 6h16M4 12h16M4 18h10', 'description' => 'Klasifikasi artikel.'],
                    ['route' => 'admin.faqs.index', 'label' => 'FAQ', 'active' => ['admin.faqs.*'], 'icon' => 'M12 18h.01M9.1 9a3 3 0 1 1 5.8 1', 'description' => 'Pertanyaan umum.'],
                ]],
                ['label' => 'Operasional Internal', 'description' => 'Data internal dan pelaporan.', 'items' => [
                    ['route' => 'admin.applications.index', 'label' => 'Data Pengajuan', 'active' => ['admin.applications.*'], 'icon' => 'M5 4h14v16H5V4Z', 'description' => 'Proses pengajuan.'],
                    ['route' => 'admin.business-categories.index', 'label' => 'Kategori Bisnis', 'active' => ['admin.business-categories.*'], 'icon' => 'M4 6h16M4 12h16M4 18h10', 'description' => 'Master kategori.'],
                    ['route' => 'admin.reports.index', 'label' => 'Laporan', 'active' => ['admin.reports.*'], 'icon' => 'M4 19V9h4v10H4ZM10 19V5h4v14h-4Z', 'description' => 'Filter dan export.'],
                    ['route' => 'admin.analytics.index', 'label' => 'Analitik Pengunjung', 'active' => ['admin.analytics.*'], 'icon' => 'M4 19V9m5 10V5m5 14v-7m5 7V3M3 19h18', 'description' => 'Trafik dan perilaku.'],
                ]],
                ['label' => 'Pengaturan', 'description' => 'Konfigurasi teknis website.', 'items' => [
                    ['route' => 'admin.seo.edit', 'label' => 'SEO Website', 'active' => ['admin.seo.*'], 'icon' => 'M10.5 18a7.5 7.5 0 1 1 5.3-12.8M16 16l5 5', 'description' => 'Metadata pencarian.'],
                    ['route' => 'admin.account.edit', 'label' => 'Akun Admin', 'active' => ['admin.account.*'], 'icon' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM5 21a7 7 0 0 1 14 0M16 7l2 2 3-3', 'description' => 'Email dan password.'],
                ]],
            ];

            $currentLabel = html_entity_decode(trim($__env->yieldContent('title', 'Panel Admin')) ?: 'Panel Admin', ENT_QUOTES, 'UTF-8');
        @endphp

        <header class="admin-topbar">
            <div class="admin-topbar__inner">
                <a href="{{ route('admin.dashboard') }}" class="admin-topbar__brand" aria-label="Beranda Admin">
                    <span class="admin-topbar__logo">
                        <img src="{{ asset('images/logo-zzk.png') }}" alt="Logo PT Zam Zam Khan">
                    </span>
                    <span class="admin-topbar__brand-text">
                        <span>Admin PT Zam Zam Khan</span>
                    </span>
                </a>

                <button
                    type="button"
                    id="admin-menu-button"
                    class="admin-menu-button"
                    aria-controls="admin-drawer"
                    :aria-expanded="menuOpen.toString()"
                    :aria-label="menuOpen ? 'Tutup menu admin' : 'Buka menu admin'"
                    @click="menuOpen = true; $nextTick(() => $refs.drawerClose?.focus())"
                >
                    <span aria-hidden="true"></span>
                    <span aria-hidden="true"></span>
                    <span aria-hidden="true"></span>
                </button>

                <div class="admin-topbar__center">
                    <span>Panel Admin</span>
                    <p class="admin-topbar__title">{{ $currentLabel }}</p>
                </div>

                <div class="admin-topbar__actions">
                    <button
                        type="button"
                        class="admin-theme-button"
                        @click="$store.theme.toggle()"
                        :aria-pressed="$store.theme.dark ? 'true' : 'false'"
                        :aria-label="$store.theme.dark ? 'Dark mode aktif, klik untuk light mode' : 'Light mode aktif, klik untuk dark mode'"
                        title="Ganti tema"
                    >
                        <svg x-show="!$store.theme.dark" aria-hidden="true" class="admin-theme-button__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <circle cx="12" cy="12" r="4"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32 1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41m11.32-11.32 1.41-1.41"/>
                        </svg>
                        <svg x-show="$store.theme.dark" x-cloak aria-hidden="true" class="admin-theme-button__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/>
                        </svg>
                        <span x-text="$store.theme.dark ? 'Dark' : 'Light'"></span>
                    </button>
                    <form method="POST" action="{{ route('admin.logout') }}" class="admin-topbar__logout">
                        @csrf
                        <button type="submit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 8l4 4m0 0-4 4m4-4H9m3 8H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h7"/>
                            </svg>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <div
            x-show="menuOpen"
            x-cloak
            x-transition.opacity.duration.250ms
            class="admin-drawer-overlay"
            @click="menuOpen = false"
            aria-hidden="true"
        ></div>

        <aside
            id="admin-drawer"
            class="admin-drawer"
            x-cloak
            x-show="menuOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            role="dialog"
            aria-modal="true"
            aria-label="Navigasi Admin CMS"
        >
            <div class="admin-drawer__header">
                <div class="admin-drawer__brand">
                    <span class="admin-drawer__logo">
                        <img src="{{ asset('images/logo-zzk.png') }}" alt="Logo PT Zam Zam Khan">
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="admin-drawer__title">Admin PT Zam Zam Khan</span>
                        <span class="admin-drawer__subtitle">CMS Website</span>
                    </span>
                </div>
                <button
                    type="button"
                    class="admin-drawer__close"
                    aria-label="Tutup menu admin"
                    x-ref="drawerClose"
                    @click="menuOpen = false"
                >
                    <span aria-hidden="true"></span>
                    <span aria-hidden="true"></span>
                </button>
            </div>

            <nav class="admin-drawer__nav">
                <a href="{{ route('admin.dashboard') }}" class="admin-drawer__link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif @click="menuOpen = false">
                    <span class="admin-drawer__link-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13h8V3H3v10Zm0 8h8v-6H3v6Zm10 0h8V11h-8v10Zm0-18v6h8V3h-8Z"/></svg></span>
                    <span class="admin-drawer__link-text"><span>Dashboard</span><em>Ringkasan seluruh modul.</em></span>
                </a>
                @foreach ($navSections as $section)
                    <details class="admin-drawer__section admin-nav-group" open>
                        <summary>
                            <span><strong>{{ $section['label'] }}</strong><em>{{ $section['description'] }}</em></span>
                            <span class="admin-nav-group__count">{{ count($section['items']) }}</span>
                        </summary>
                        <div class="admin-drawer__items">
                            @foreach ($section['items'] as $item)
                                @php($active = collect($item['active'])->contains(fn ($pattern) => request()->routeIs($pattern)))
                                <a href="{{ route($item['route']) }}" class="admin-drawer__link {{ $active ? 'is-active' : '' }}" @if($active) aria-current="page" @endif @click="menuOpen = false">
                                    <span class="admin-drawer__link-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                                        </svg>
                                    </span>
                                    <span class="admin-drawer__link-text">
                                        <span>{{ $item['label'] }}</span>
                                        <em>{{ $item['description'] }}</em>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </details>
                @endforeach
            </nav>

            <div class="admin-drawer__footer">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="admin-drawer__logout">Keluar</button>
                </form>
            </div>
        </aside>

        <a
            href="{{ route('admin.dashboard') }}"
            class="admin-home-shortcut"
            aria-label="Kembali ke Beranda Admin"
            title="Kembali ke Beranda Admin"
            data-tooltip="Kembali ke Beranda Admin"
            x-bind:class="{ 'is-hidden': menuOpen }"
            x-show="!menuOpen"
            x-transition.opacity.duration.150ms
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 11.5 12 4l9 7.5M5.5 10.5V20h13v-9.5M9.5 20v-5h5v5"/>
            </svg>
        </a>
    @endauth

    <main id="admin-main-content" tabindex="-1" class="admin-enter admin-content">
        @auth('admin')
            @include('admin.partials.flash')
            @include('admin.partials.validation-errors')
            @include('admin.components.module-navigation')
        @endauth
        @yield('content')
    </main>

    @auth('admin')
        <div x-show="deleteOpen" x-cloak class="admin-delete-modal" role="dialog" aria-modal="true" aria-labelledby="delete-title">
            <div class="admin-delete-modal__backdrop" @click="deleteOpen = false"></div>
            <div class="admin-delete-modal__panel" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="admin-delete-modal__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M5 20h14L12 4 5 20Z"/></svg>
                </div>
                <h2 id="delete-title">Hapus data?</h2>
                <p>Data <strong x-text="deleteName"></strong> akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.</p>
                <form method="POST" :action="deleteAction" class="admin-delete-modal__actions">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn-outline" @click="deleteOpen = false">Batal</button>
                    <button type="submit" class="admin-danger-button">Hapus</button>
                </form>
            </div>
        </div>
    @endauth
    {{-- Navigasi instan dan feedback submit ditangani global di resources/js/app.js --}}
</body>
</html>
