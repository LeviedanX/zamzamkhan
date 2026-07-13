@php
    $latestArticles = collect();
    if (\Illuminate\Support\Facades\Schema::hasTable('articles')) {
        $latestArticles = \App\Models\Article::published()->with('category')->latestPublished()->take(3)->get();
    }
@endphp

@if ($latestArticles->isNotEmpty())
@php($featured = $latestArticles->first())
@php($rest = $latestArticles->slice(1))
<section id="artikel" class="article-section section relative overflow-hidden border-t border-navy-100 bg-white dark:border-white/10 dark:bg-navy-950">
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <div class="advantage-bg-glow advantage-bg-glow--1"></div>
        <div class="advantage-bg-glow advantage-bg-glow--2"></div>
        <div class="hero-grid absolute inset-0 opacity-[0.03] dark:opacity-[0.05]"></div>
    </div>

    <div class="container-x relative">
        {{-- Header --}}
        <div class="reveal flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-2xl">
                <span class="eyebrow">Artikel &amp; Insight</span>
                <h2 class="mt-3 font-display text-3xl font-extrabold leading-tight text-navy-900 sm:text-4xl dark:text-white">
                    Artikel &amp; Insight Bisnis
                </h2>
                <p class="mt-4 leading-relaxed text-navy-600 dark:text-navy-300">
                    Insight praktis seputar sertifikasi halal, legalitas usaha, BPOM, HAKI, perpajakan, branding, serta aktivitas pendampingan PT Zam Zam Khan.
                </p>
            </div>
            <a href="{{ route('artikel.index') }}" class="article-seeall group inline-flex flex-none items-center gap-2 self-start text-sm font-semibold text-emerald-brand sm:self-auto dark:text-tosca-400">
                Lihat Semua Artikel
                <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 12h15"/></svg>
            </a>
        </div>

        {{-- Grid: featured kiri + 2 sekunder kanan --}}
        <div class="mt-10 grid gap-6 lg:grid-cols-2">
            {{-- Featured --}}
            <a href="{{ route('artikel.show', $featured->slug) }}" class="article-card article-card--featured reveal reveal-left group">
                <div class="article-cover">
                    @include('partials.article-cover', ['article' => $featured])
                </div>
                <div class="article-card__body">
                    <div class="article-meta">
                        @if ($featured->category)<span class="article-chip">{{ $featured->category->name }}</span>@endif
                        <span class="article-date">{{ $featured->publishedDate() }}</span>
                    </div>
                    <h3 class="article-card__title article-card__title--lg">{{ $featured->title }}</h3>
                    @if ($featured->excerpt)<p class="article-card__excerpt">{{ $featured->excerpt }}</p>@endif
                    <span class="article-readmore">Baca Artikel
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 12h15"/></svg>
                    </span>
                </div>
            </a>

            {{-- Sekunder --}}
            <div class="flex flex-col gap-6">
                @foreach ($rest as $a)
                    <a href="{{ route('artikel.show', $a->slug) }}" class="article-card article-card--row reveal reveal-right group">
                        <div class="article-cover article-cover--sm">
                            @include('partials.article-cover', ['article' => $a])
                        </div>
                        <div class="article-card__body">
                            <div class="article-meta">
                                @if ($a->category)<span class="article-chip">{{ $a->category->name }}</span>@endif
                                <span class="article-date">{{ $a->publishedDate() }}</span>
                            </div>
                            <h3 class="article-card__title">{{ $a->title }}</h3>
                            <span class="article-readmore">Baca Artikel
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 12h15"/></svg>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
