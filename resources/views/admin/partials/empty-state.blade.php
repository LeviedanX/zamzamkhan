<div class="admin-empty-state">
    <div class="admin-empty-state__icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon ?? 'M5 5h14v14H5V5Zm4 4h6M9 13h4' }}"/>
        </svg>
    </div>
    <p class="admin-empty-state__title">{{ $title ?? 'Belum ada data' }}</p>
    @isset($description)
        <p class="admin-empty-state__description">{{ $description }}</p>
    @endisset
    @isset($action)
        <a href="{{ $action['href'] }}" class="btn-primary mt-5">{{ $action['label'] }}</a>
    @endisset
</div>
