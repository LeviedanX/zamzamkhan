@php
    $stats = collect(config('company.stats', []))
        ->filter(fn ($item) => is_array($item) && isset($item['value'], $item['label']))
        ->values();
@endphp
@if(count($stats))
<section id="statistik" class="bg-white py-16 dark:bg-navy-950">
    <div class="container-x">
        <div class="reveal reveal-scale grid gap-6 rounded-3xl border border-navy-100 bg-gradient-to-br from-navy-50 to-white p-8 shadow-xl shadow-navy-900/5 sm:grid-cols-2 lg:grid-cols-4 lg:p-10 dark:border-navy-800 dark:from-navy-900 dark:to-navy-950">
            @foreach ($stats as $i => $stat)
                @php(preg_match('/^(\d+)(.*)$/', $stat['value'], $m))
                <div class="text-center {{ $i < 3 ? 'lg:border-r lg:border-navy-100 dark:lg:border-navy-800' : '' }}">
                    @if ($m)
                        <p class="font-display text-4xl font-extrabold text-navy-900 dark:text-white"
                           data-count="{{ $m[1] }}" data-suffix="{{ $m[2] }}">{{ $stat['value'] }}</p>
                    @else
                        <p class="font-display text-4xl font-extrabold text-navy-900 dark:text-white">{{ $stat['value'] }}</p>
                    @endif
                    <p class="mt-2 text-sm font-medium text-navy-500 dark:text-navy-300">{{ $stat['label'] }}</p>
                    @if (filled($stat['description'] ?? null))
                        <p class="mx-auto mt-1 max-w-[15rem] text-xs leading-relaxed text-navy-400 dark:text-navy-400">{{ $stat['description'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
