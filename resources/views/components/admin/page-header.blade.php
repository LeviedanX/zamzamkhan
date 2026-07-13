@props([
    'eyebrow' => null,
    'title',
    'description' => null,
])

<header {{ $attributes->class(['admin-page-header']) }}>
    <div class="admin-page-header__copy">
        @if ($eyebrow)
            <p class="admin-page-kicker">{{ $eyebrow }}</p>
        @endif
        <h1 class="admin-page-header__title">{{ $title }}</h1>
        @if ($description)
            <p class="admin-page-header__description">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="admin-page-header__actions">{{ $actions }}</div>
    @endisset
</header>
