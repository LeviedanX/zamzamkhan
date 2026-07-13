@php($dynamicColor = $dynamicColor ?? false)
<button type="button"
        @click="$store.theme.toggle()"
        :aria-pressed="$store.theme.dark ? 'true' : 'false'"
        aria-label="Ganti tema terang atau gelap"
        title="Ganti tema"
        @if ($dynamicColor)
            :class="scrolled ? 'border-navy-200/80 text-navy-600 hover:border-emerald-brand/60 hover:text-emerald-brand dark:border-white/15 dark:text-navy-200' : 'border-white/15 text-white/85 hover:border-tosca-400/55 hover:bg-tosca-400/10'"
            class="theme-toggle inline-flex h-10 w-10 flex-none items-center justify-center rounded-xl border bg-white/5 transition"
        @else
            class="theme-toggle inline-flex h-10 w-10 flex-none items-center justify-center rounded-xl border border-navy-200 text-navy-700 transition hover:text-emerald-brand dark:border-navy-700 dark:text-navy-200"
        @endif
>
    {{-- Bulan (tampil saat mode terang) --}}
    <svg x-show="!$store.theme.dark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
    </svg>
    {{-- Matahari (tampil saat mode gelap) --}}
    <svg x-show="$store.theme.dark" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
        <circle cx="12" cy="12" r="4"/>
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32l1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41m11.32-11.32l1.41-1.41"/>
    </svg>
</button>
