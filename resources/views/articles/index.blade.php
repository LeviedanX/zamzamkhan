@extends('layouts.app')

@php
    $currentPage = $articles->currentPage();

    // Canonical menunjuk ke dirinya sendiri, lengkap dengan kategori & halaman,
    // supaya halaman 2 dst. tidak dianggap duplikat halaman 1.
    $canonicalQuery = array_filter([
        'kategori' => $activeCategory?->slug,
        'q' => $q !== '' ? $q : null,
        'page' => $currentPage > 1 ? $currentPage : null,
    ]);
    $canonicalUrl = route('artikel.index').($canonicalQuery ? '?'.http_build_query($canonicalQuery) : '');

    $pageTitle = 'Artikel & Insight Bisnis — Konsultan Halal & Legalitas Usaha Malang | PT Zam Zam Khan';
    $pageDesc = 'Kumpulan artikel dan insight seputar sertifikasi halal, legalitas usaha, NIB, BPOM, HAKI, perpajakan, dan branding untuk pelaku usaha di Malang bersama PT Zam Zam Khan.';

    if ($activeCategory) {
        $pageTitle = $activeCategory->name.' — Artikel & Insight | PT Zam Zam Khan';
    }
    if ($currentPage > 1) {
        $pageTitle = 'Halaman '.$currentPage.' — '.$pageTitle;
    }

    // Hasil pencarian bersifat tipis dan tak terbatas jumlahnya; jangan diindeks,
    // tapi tetap biarkan crawler mengikuti tautan artikelnya.
    $pageRobots = $q !== '' ? 'noindex, follow' : 'index, follow';
@endphp

@section('title', $pageTitle)
@section('description', $pageDesc)
@section('robots', $pageRobots)
@section('canonical', $canonicalUrl)
@section('ogUrl', $canonicalUrl)
@section('ogTitle', $pageTitle)
@section('ogDescription', $pageDesc)

@section('content')
    {{-- Page header (lebih ringkas dari hero utama) --}}
    <header class="article-hero relative overflow-hidden">
        <div class="article-hero__glow" aria-hidden="true"></div>
        <div class="hero-grid absolute inset-0 opacity-[0.08]" aria-hidden="true"></div>
        <div class="container-x relative pb-14 pt-32 text-center sm:pt-36">
            <div class="mb-8 flex justify-start">
                <a href="{{ route('home') }}" class="article-home-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m0 0 6-6m-6 6 6 6"/>
                    </svg>
                    Kembali ke Halaman Utama
                </a>
            </div>
            <span class="article-hero__eyebrow">Artikel &amp; Insight</span>
            <h1 class="mt-4 font-display text-4xl font-extrabold leading-tight text-white sm:text-5xl">
                Artikel &amp; Insight Bisnis
            </h1>
            <p class="mx-auto mt-4 max-w-2xl leading-relaxed text-navy-200">
                Edukasi praktis seputar sertifikasi halal, legalitas usaha, dan pengembangan bisnis untuk membantu pelaku usaha di Malang tumbuh lebih tertib dan terpercaya.
            </p>

            {{-- Search (GET) --}}
            <form method="GET" action="{{ route('artikel.index') }}" role="search" class="article-search mx-auto mt-8 flex max-w-xl items-center gap-2">
                <label for="article-q" class="sr-only">Cari artikel</label>
                <div class="relative flex-1">
                    <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-navy-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M21 21l-4.3-4.3"/></svg>
                    <input id="article-q" type="search" name="q" value="{{ $q }}"
                           placeholder="Cari topik, layanan, atau informasi…"
                           class="article-search__input">
                </div>
                @if ($activeCategory)
                    <input type="hidden" name="kategori" value="{{ $activeCategory->slug }}">
                @endif
                <button type="submit" class="btn-primary px-5! py-3!">Cari</button>
            </form>
        </div>
    </header>

    <section class="section bg-white pt-12 dark:bg-navy-950 md:pt-14">
        <div class="container-x">
            {{-- Filter chip kategori --}}
            <nav class="article-filter" aria-label="Filter kategori artikel">
                <a href="{{ route('artikel.index', array_filter(['q' => $q])) }}"
                   @class(['article-chip-filter', 'article-chip-filter--active' => ! $activeCategory])
                   @if(! $activeCategory) aria-current="true" @endif>Semua</a>
                @foreach ($categories as $cat)
                    <a href="{{ route('artikel.index', array_filter(['kategori' => $cat->slug, 'q' => $q])) }}"
                       @class(['article-chip-filter', 'article-chip-filter--active' => $activeCategory && $activeCategory->id === $cat->id])
                       @if($activeCategory && $activeCategory->id === $cat->id) aria-current="true" @endif>{{ $cat->name }}</a>
                @endforeach
            </nav>

            @if ($articles->isEmpty())
                {{-- Empty state --}}
                <div class="article-empty mx-auto mt-12 max-w-md text-center">
                    <div class="article-empty__icon" aria-hidden="true">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M21 21l-4.3-4.3"/></svg>
                    </div>
                    <h2 class="mt-5 font-display text-xl font-bold text-navy-900 dark:text-white">Artikel tidak ditemukan</h2>
                    <p class="mt-2 text-sm text-navy-500 dark:text-navy-300">
                        Belum ada artikel yang cocok dengan pencarian atau filter Anda. Coba kata kunci lain atau atur ulang filter.
                    </p>
                    <a href="{{ route('artikel.index') }}" class="btn-outline mt-6">Atur Ulang Filter</a>
                </div>
            @else
                <p class="mt-8 text-sm text-navy-500 dark:text-navy-400">
                    Menampilkan {{ $articles->count() }} dari {{ $articles->total() }} artikel
                    @if ($q) untuk “<span class="font-semibold text-navy-700 dark:text-navy-200">{{ $q }}</span>” @endif
                    @if ($activeCategory) pada kategori <span class="font-semibold text-navy-700 dark:text-navy-200">{{ $activeCategory->name }}</span> @endif
                </p>

                <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($articles as $a)
                        <a href="{{ route('artikel.show', $a->slug) }}" class="article-card article-card--grid group">
                            <div class="article-cover">
                                @include('partials.article-cover', ['article' => $a])
                            </div>
                            <div class="article-card__body">
                                <div class="article-meta">
                                    @if ($a->category)<span class="article-chip">{{ $a->category->name }}</span>@endif
                                    <span class="article-date">{{ $a->publishedDate() }}</span>
                                </div>
                                <h2 class="article-card__title">{{ $a->title }}</h2>
                                @if ($a->excerpt)<p class="article-card__excerpt">{{ $a->excerpt }}</p>@endif
                                <span class="article-readmore">Baca Artikel
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 12h15"/></svg>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $articles->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
