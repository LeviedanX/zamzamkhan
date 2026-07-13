@if ($errors->any())
    <div class="admin-alert admin-alert--danger" role="alert">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M5 20h14L12 4 5 20Z"/>
        </svg>
        <div>
            <p class="font-semibold">Periksa kembali isian Anda:</p>
            <ul class="mt-1 list-inside list-disc">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
