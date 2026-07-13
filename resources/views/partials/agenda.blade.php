@php
    // Agenda yang sudah selesai sudah disaring per request di AppServiceProvider,
    // jadi daftar ini dijamin hanya berisi agenda yang masih berjalan/akan datang.
    $agendas = collect(config('company.agendas', []));
@endphp

@if ($agendas->isNotEmpty())
<section id="agenda" class="section relative overflow-hidden border-t border-navy-100 bg-white dark:border-white/10 dark:bg-navy-950">
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
        <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-emerald-brand/[0.07] blur-3xl dark:bg-emerald-brand/15"></div>
        <div class="hero-grid absolute inset-0 opacity-[0.025] dark:opacity-[0.05]"></div>
    </div>

    <div class="container-x relative">
        <div class="mx-auto max-w-2xl text-center reveal">
            <span class="eyebrow">Agenda</span>
            <h2 class="mt-3 font-display text-3xl font-extrabold text-navy-900 sm:text-4xl dark:text-white">Jadwal Kegiatan & Konsultasi</h2>
            <p class="mt-4 leading-relaxed text-navy-600 dark:text-navy-300">Informasi kegiatan, kelas, dan sesi konsultasi yang dikelola langsung melalui panel admin.</p>
        </div>

        <div class="mx-auto mt-10 grid max-w-6xl gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach($agendas as $agenda)
                <article class="reveal flex h-full flex-col overflow-hidden rounded-3xl border border-navy-100 bg-navy-50/70 shadow-lg shadow-navy-900/5 dark:border-white/10 dark:bg-white/5.5 dark:shadow-black/20">
                    @if(filled($agenda['image_url'] ?? null))
                        <img src="{{ $agenda['image_url'] }}" alt="Agenda {{ $agenda['title'] }}" class="aspect-video w-full object-cover" loading="lazy" decoding="async">
                    @endif
                    <div class="flex flex-1 flex-col p-6">
                        <time datetime="{{ $agenda['starts_at'] ?? '' }}" class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-brand dark:text-tosca-400">
                            {{ $agenda['date'] ?? '' }} · {{ $agenda['time'] ?? '' }}
                            @if (filled($agenda['end_time'] ?? null))
                                — {{ ($agenda['end_date'] ?? null) !== ($agenda['date'] ?? null) ? ($agenda['end_date'].' · ') : '' }}{{ $agenda['end_time'] }}
                            @endif
                        </time>
                        <h3 class="mt-3 font-display text-xl font-bold text-navy-900 dark:text-white">{{ $agenda['title'] }}</h3>
                        @if(filled($agenda['venue'] ?? null))
                            <p class="mt-2 text-sm font-semibold text-navy-500 dark:text-navy-300">{{ $agenda['venue'] }}</p>
                        @endif
                        @if(filled($agenda['summary'] ?? null))
                            <p class="mt-3 line-clamp-3 text-sm leading-6 text-navy-600 dark:text-navy-300">{{ $agenda['summary'] }}</p>
                        @endif
                        <div class="mt-auto pt-5">
                            @if(filled($agenda['registration_url'] ?? null))
                                <a href="{{ $agenda['registration_url'] }}" target="_blank" rel="noopener noreferrer" class="btn-primary w-full">Daftar Agenda</a>
                            @elseif (filled(config('company.whatsapp_number')))
                                <button type="button" data-whatsapp-lead data-mode="undecided" data-needs="Saya ingin menanyakan agenda {{ $agenda['title'] }}." class="btn-outline w-full">Tanyakan Agenda</button>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
