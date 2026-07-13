@if (session('ok'))
    <div class="admin-alert admin-alert--success" role="status">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
        </svg>
        <span>{{ session('ok') }}</span>
    </div>
@endif

@if (session('warning'))
    <div class="admin-alert admin-alert--warning" role="status">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M5 20h14L12 4 5 20Z"/>
        </svg>
        <span>{{ session('warning') }}</span>
    </div>
@endif

@if (session('error'))
    <div class="admin-alert admin-alert--danger" role="alert">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M5 20h14L12 4 5 20Z"/>
        </svg>
        <span>{{ session('error') }}</span>
    </div>
@endif
