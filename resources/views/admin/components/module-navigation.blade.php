@php
    $category = match (true) {
        request()->routeIs('admin.applications.*', 'admin.business-categories.*', 'admin.reports.*', 'admin.analytics.*') => 'Operasional Internal',
        request()->routeIs('admin.seo.*', 'admin.account.*') => 'Pengaturan',
        request()->routeIs('admin.dashboard') => 'Ringkasan',
        default => 'Konten Website',
    };

    $sections = collect($navSections ?? []);
    $workflow = $sections
        ->flatMap(fn ($section) => $section['items'] ?? [])
        ->values();

    $currentIndex = $workflow->search(function ($item) {
        foreach (($item['active'] ?? []) as $pattern) {
            if (request()->routeIs($pattern)) {
                return true;
            }
        }

        return request()->routeIs($item['route'] ?? '');
    });

    $isDashboard = request()->routeIs('admin.dashboard');

    $previousItem = ! $isDashboard && $currentIndex !== false && $currentIndex > 0 ? $workflow->get($currentIndex - 1) : null;
    $previousLabel = $previousItem['label'] ?? 'Dashboard';
    $previousUrl = $previousItem ? route($previousItem['route']) : route('admin.dashboard');

    $nextItem = $isDashboard
        ? $workflow->first()
        : ($currentIndex !== false ? $workflow->get($currentIndex + 1) : null);
    $nextLabel = $nextItem['label'] ?? 'Dashboard';
    $nextUrl = $nextItem ? route($nextItem['route']) : route('admin.dashboard');
@endphp

<nav class="admin-module-nav" aria-label="Posisi modul admin">
    <div class="admin-module-nav__bar">
        @if ($isDashboard)
            {{-- Dashboard adalah titik awal: slot kiri dikosongkan agar chip kategori tetap center. --}}
            <span aria-hidden="true"></span>
        @else
            <a href="{{ $previousUrl }}" class="admin-module-nav__item admin-module-nav__item--prev" data-admin-prefetch>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 18 9 12l6-6"/></svg>
                <span>{{ $previousLabel }}</span>
            </a>
        @endif
        <span class="admin-module-nav__home" aria-current="page">
            <span>{{ $category }}</span>
        </span>
        <a href="{{ $nextUrl }}" class="admin-module-nav__item admin-module-nav__item--next" data-admin-prefetch>
            <span class="admin-module-nav__label">{{ $nextLabel }}</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6l6 6-6 6"/></svg>
        </a>
    </div>
</nav>
