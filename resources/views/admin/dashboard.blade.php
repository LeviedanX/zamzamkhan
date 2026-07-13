@extends('layouts.admin')

@section('title', 'Dashboard')

@php
    $adminName = auth('admin')->user()?->name ?: 'Admin';
    $todayLabel = now()->locale('id')->translatedFormat('l, d F Y');
@endphp

@section('content')
<section class="admin-hero-pattern relative w-full min-w-0 overflow-hidden rounded-[28px] px-5 py-7 shadow-2xl shadow-[rgb(24_19_19_/_0.16)] sm:px-8 lg:px-10">
    <div class="relative grid min-w-0 grid-cols-1 gap-8 lg:grid-cols-[1.05fr_0.95fr] lg:items-end">
        <div class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-white/60">Admin CMS</p>
            <h1 class="mt-4 max-w-3xl break-words text-3xl font-semibold leading-tight text-white sm:text-5xl">
                Selamat datang, {{ $adminName }}
            </h1>
            <p class="mt-4 max-w-2xl break-words text-base leading-7 text-white/70">
                Kelola konten publik, data pengajuan, agenda, dan laporan PT Zam Zam Khan dari satu workspace.
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <span class="inline-flex max-w-full rounded-full border border-white/10 bg-white/10 px-4 py-2 text-sm font-semibold text-white/80 backdrop-blur">
                    {{ $todayLabel }}
                </span>
            </div>
        </div>

        <div class="grid min-w-0 grid-cols-2 gap-3">
            @foreach ($tiles as $tile)
                <a href="{{ route($tile['route']) }}" class="min-w-0 rounded-3xl border border-white/10 bg-white/10 p-4 text-white shadow-sm backdrop-blur transition hover:-translate-y-1 hover:bg-white/15 {{ $loop->last && $loop->count % 2 !== 0 ? 'col-span-2 sm:col-span-1' : '' }}">
                    <p class="break-words text-xs font-semibold uppercase tracking-[0.16em] text-white/60">{{ $tile['label'] }}</p>
                    <p class="mt-3 text-3xl font-semibold">{{ $tile['value'] }}</p>
                </a>
            @endforeach
        </div>
    </div>
</section>

<section class="mt-10 space-y-10">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--admin-maroon)]">Navigasi Cepat</p>
        <h2 class="mt-2 text-2xl font-semibold text-[var(--admin-ink)]">Pilih Kategori Pengelolaan</h2>
        <p class="mt-2 text-sm text-[var(--admin-muted)]">Modul dikelompokkan berdasarkan fungsi agar konfigurasi lebih ringkas.</p>
    </div>

    @foreach ($groups as $group => $cards)
        <section class="admin-module-group">
            <div class="flex items-center justify-between gap-3">
                <h3 class="font-display text-lg font-bold text-[var(--admin-ink)]">{{ $group }}</h3>
                <span class="rounded-full bg-[var(--admin-soft-maroon)] px-3 py-1 text-xs font-bold text-[var(--admin-maroon)]">{{ count($cards) }} modul</span>
            </div>
            <div class="mt-5 grid min-w-0 grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($cards as [$route, $label, $desc, $icon])
                    <a href="{{ route($route) }}" class="admin-feature-card group flex min-h-[170px] min-w-0 flex-col items-center justify-center overflow-hidden rounded-2xl border border-[var(--admin-border)] bg-white px-6 py-6 text-center no-underline transition duration-300 hover:-translate-y-1 hover:border-[var(--admin-maroon)]">
                        <span class="relative z-10 flex h-12 w-12 items-center justify-center rounded-xl bg-[var(--admin-soft-maroon)] text-[var(--admin-maroon)] transition group-hover:bg-[var(--admin-maroon)] group-hover:text-white">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                        </span>
                        <h4 class="relative z-10 mt-4 break-words text-base font-bold text-[var(--admin-ink)]">{{ $label }}</h4>
                        <p class="relative z-10 mt-2 break-words text-sm leading-5 text-[var(--admin-muted)]">{{ $desc }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endforeach
</section>
@endsection
