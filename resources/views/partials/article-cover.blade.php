{{-- Cover artikel rasio 16:9. Fallback CSS/SVG brand bila cover kosong.
     Props: $article, $sizes (opsional class tambahan pada wrapper) --}}
@php($cat = $article->category?->name)
@php($gradientId = 'acg-'.($article->getKey() ?? 'preview'))
@if ($article->cover_image)
    <img src="{{ asset('storage/'.$article->cover_image) }}"
         alt="{{ $article->cover_alt ?: $article->title }}"
         width="1280" height="720" loading="lazy" decoding="async"
         class="article-cover__img">
@else
    <div class="article-cover__fallback" role="img"
         aria-label="Ilustrasi artikel {{ $cat ? $cat.' — ' : '' }}{{ $article->title }}">
        <svg class="article-cover__pattern" viewBox="0 0 320 180" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
            <defs>
                <linearGradient id="{{ $gradientId }}" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0" stop-color="#7f1d1d"/>
                    <stop offset="0.55" stop-color="#3a0d0d"/>
                    <stop offset="1" stop-color="#070707"/>
                </linearGradient>
            </defs>
            <rect width="320" height="180" fill="url(#{{ $gradientId }})"/>
            <g fill="none" stroke="#b91c1c" stroke-width="0.6" opacity="0.28">
                <circle cx="255" cy="40" r="55"/>
                <circle cx="255" cy="40" r="85"/>
                <circle cx="255" cy="40" r="115"/>
            </g>
        </svg>
        <span class="article-cover__mark" aria-hidden="true">ZZK</span>
        @if ($cat)
            <span class="article-cover__tag" aria-hidden="true">{{ $cat }}</span>
        @endif
    </div>
@endif
