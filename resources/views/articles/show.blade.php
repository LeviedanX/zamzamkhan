@extends('layouts.app')

@php
    $metaTitle = $article->meta_title ?: $article->title;
    $metaDesc = $article->meta_description ?: ($article->excerpt ?: 'Artikel dan insight bisnis dari PT Zam Zam Khan — konsultan halal dan legalitas usaha di Malang.');
    $ogImage = $article->cover_image ? asset('storage/'.$article->cover_image) : (config('company.logo_url') ?: asset('images/logo-zzk.png'));
    $canonical = route('artikel.show', $article->slug);
@endphp

@section('title', $metaTitle.' | '.config('company.name', 'PT Zam Zam Khan'))
@section('description', $metaDesc)
@section('canonical', $canonical)
@section('ogType', 'article')
@section('ogTitle', $metaTitle)
@section('ogDescription', $metaDesc)
@section('ogImage', $ogImage)
@section('ogUrl', $canonical)

@section('jsonld')
    @php
        $jsonLd = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $article->title,
            'description' => $metaDesc,
            'image' => $article->cover_image ? $ogImage : null,
            'datePublished' => optional($article->published_at ?? $article->created_at)->toAtomString(),
            'dateModified' => optional($article->updated_at)->toAtomString(),
            'articleSection' => $article->category?->name,
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonical],
            'author' => ['@type' => 'Organization', 'name' => config('company.name')],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('company.name'),
                'logo' => ['@type' => 'ImageObject', 'url' => config('company.logo_url') ?: asset('images/logo-zzk.png')],
            ],
        ]);
        $jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
            | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
    @endphp
    <script type="application/ld+json" nonce="{{ $cspNonce }}">
{!! json_encode($jsonLd, $jsonFlags) !!}
    </script>
@endsection

@section('content')
    <article>
        {{-- Header --}}
        <header class="article-hero relative overflow-hidden">
            <div class="article-hero__glow" aria-hidden="true"></div>
            <div class="hero-grid absolute inset-0 opacity-[0.08]" aria-hidden="true"></div>
            <div class="container-x relative pb-14 pt-28 sm:pt-32">
                {{-- Breadcrumb --}}
                <nav class="article-breadcrumb" aria-label="Breadcrumb">
                    <a href="{{ route('home') }}">Beranda</a>
                    <span aria-hidden="true">/</span>
                    <a href="{{ route('artikel.index') }}">Artikel</a>
                    <span aria-hidden="true">/</span>
                    <span class="article-breadcrumb__current" aria-current="page">{{ Str::limit($article->title, 42) }}</span>
                </nav>

                <div class="mt-6 max-w-3xl">
                    <div class="article-meta article-meta--light">
                        @if ($article->category)
                            <a href="{{ route('artikel.index', ['kategori' => $article->category->slug]) }}" class="article-chip article-chip--link">{{ $article->category->name }}</a>
                        @endif
                        <span class="article-date">{{ $article->publishedDate() }}</span>
                    </div>
                    <h1 class="mt-4 font-display text-3xl font-extrabold leading-tight text-white sm:text-4xl md:text-[2.6rem]">
                        {{ $article->title }}
                    </h1>
                    @if ($article->excerpt)
                        <p class="mt-4 text-lg leading-relaxed text-navy-200">{{ $article->excerpt }}</p>
                    @endif
                </div>
            </div>
        </header>

        <div class="bg-white pb-20 dark:bg-navy-950">
            <div class="container-x">
                {{-- Cover 16:9 --}}
                <div class="article-detail-cover article-cover -mt-8 md:-mt-12">
                    @include('partials.article-cover', ['article' => $article])
                </div>

                {{-- Isi artikel --}}
                <div class="article-prose prose prose-lg mx-auto mt-10 max-w-3xl dark:prose-invert">
                    @foreach (preg_split("/\n\s*\n/", trim($article->content)) as $para)
                        @if (trim($para) !== '')
                            <p>{!! nl2br(e($para)) !!}</p>
                        @endif
                    @endforeach
                </div>

                {{-- CTA WhatsApp --}}
                <div class="article-cta mx-auto mt-12 max-w-3xl">
                    <div class="article-cta__inner">
                        <div>
                            <h2 class="font-display text-xl font-bold text-white sm:text-2xl">Butuh pendampingan terkait kebutuhan usaha Anda?</h2>
                            <p class="mt-2 text-sm leading-relaxed text-navy-200">Tim PT Zam Zam Khan siap membantu proses sertifikasi halal, legalitas usaha, dan kebutuhan bisnis lainnya di Malang.</p>
                        </div>
                        <button type="button" data-whatsapp-lead data-mode="undecided"
                                class="article-cta__btn" aria-label="Konsultasikan via WhatsApp">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M.057 24l1.687-6.163a11.867 11.867 0 01-1.587-5.945C.16 5.335 5.495 0 12.05 0a11.82 11.82 0 018.413 3.488 11.82 11.82 0 013.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 01-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 001.51 5.26l-.999 3.648 3.578-.607zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.767.967-.94 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.149-.174.198-.298.297-.497.099-.198.05-.371-.025-.52-.074-.149-.669-1.611-.916-2.206-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.478 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413z"/></svg>
                            Konsultasikan via WhatsApp
                        </button>
                    </div>
                </div>

                {{-- Artikel terkait --}}
                @if ($related->isNotEmpty())
                    <section class="mx-auto mt-16 max-w-5xl" aria-labelledby="related-heading">
                        <h2 id="related-heading" class="font-display text-2xl font-bold text-navy-900 dark:text-white">Artikel Terkait</h2>
                        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($related as $r)
                                <a href="{{ route('artikel.show', $r->slug) }}" class="article-card article-card--grid group">
                                    <div class="article-cover">
                                        @include('partials.article-cover', ['article' => $r])
                                    </div>
                                    <div class="article-card__body">
                                        <div class="article-meta">
                                            @if ($r->category)<span class="article-chip">{{ $r->category->name }}</span>@endif
                                            <span class="article-date">{{ $r->publishedDate() }}</span>
                                        </div>
                                        <h3 class="article-card__title">{{ $r->title }}</h3>
                                        <span class="article-readmore">Baca Artikel
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 12h15"/></svg>
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>
        </div>
    </article>
@endsection
