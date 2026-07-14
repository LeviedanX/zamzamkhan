@php
    $testi = collect(config('company.testimonials', []))
        ->filter(fn ($item) => is_array($item) && filled($item['title'] ?? null) && filled($item['image_url'] ?? null))
        ->values();
@endphp
@if(count($testi))
<section id="testimoni" x-data="testimonialSlider()"
         class="testimonial-section section relative overflow-hidden border-t border-navy-100 bg-navy-50 dark:border-white/5 dark:bg-[#0c0708]">
    {{-- Aksen background halus + pemisah visual dari section Tentang --}}
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -top-24 left-1/4 h-72 w-72 rounded-full bg-emerald-brand/6 blur-3xl dark:bg-emerald-brand/15"></div>
        <div class="absolute bottom-0 right-0 h-72 w-72 rounded-full bg-tosca-400/5 blur-3xl dark:bg-tosca-400/10"></div>
        <div class="absolute inset-0 hero-grid opacity-[0.04] dark:opacity-[0.06]"></div>
    </div>

    <div class="container-x relative">
        {{-- Header (center) --}}
        <div class="testimonial-header reveal mx-auto max-w-2xl text-center">
            <span class="eyebrow">Social Proof</span>
            <h2 class="mt-3 font-display text-3xl font-extrabold leading-tight text-navy-900 sm:text-4xl dark:text-white">
                Testimoni &amp; Dokumentasi Pendampingan
            </h2>
            <p class="mt-4 leading-relaxed text-navy-600 dark:text-navy-300">
                Dokumentasi penyerahan sertifikat halal dan pendampingan usaha bersama klien PT Zam Zam Khan.
            </p>
        </div>

        {{-- Viewport: hover/focus mem-pause autoplay --}}
        <div class="testimonial-viewport"
             @mouseenter="hoverPause()" @mouseleave="hoverResume()" @focusin="hoverPause()" @focusout="hoverResume()">
        {{-- Slider --}}
        <div class="testimonial-slider reveal mt-10">
            <div class="testimonial-track" x-ref="track" @scroll.passive="onScroll()" @pointerdown="userInteract()"
                 role="list" aria-label="Dokumentasi pendampingan PT Zam Zam Khan">
                @foreach ($testi as $t)
                    <article class="testimonial-card group" role="listitem">
                        <div class="relative aspect-4/3 overflow-hidden bg-navy-100 dark:bg-navy-800">
                            <img src="{{ $t['image_url'] }}"
                                 alt="{{ filled($t['alt'] ?? null) ? $t['alt'] : 'Dokumentasi '.$t['service'].' — '.$t['title'] }}"
                                 loading="lazy" decoding="async"
                                 class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.05]">
                            <span class="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-emerald-brand px-2.5 py-1 text-[11px] font-semibold text-white shadow-sm">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                {{ $t['service'] }}
                            </span>
                        </div>
                        <div class="flex flex-1 flex-col p-5">
                            <div class="mb-1.5 text-tosca-400">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M7.17 6A5.17 5.17 0 002 11.17V18h6.83v-6.83H5.6A3.6 3.6 0 019.2 7.6L7.17 6zm9.6 0a5.17 5.17 0 00-5.17 5.17V18H18.4v-6.83h-3.23a3.6 3.6 0 013.6-3.57L16.77 6z"/></svg>
                            </div>
                            <h3 class="font-display text-base font-bold leading-snug text-navy-900 line-clamp-1 dark:text-white">{{ $t['title'] }}</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-navy-600 line-clamp-2 dark:text-navy-300">{{ $t['caption'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>

        {{-- Kontrol: panah + counter + progress + dots (center) --}}
        <div class="testimonial-controls mt-7 flex flex-col items-center gap-4 sm:flex-row sm:justify-center">
            <div class="flex items-center gap-3">
                <button type="button" @click="onPrev()" aria-label="Dokumentasi sebelumnya" class="testimonial-prev testimonial-arrow">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                </button>
                <span class="font-display text-sm font-bold tabular-nums text-navy-900 dark:text-white">
                    <span x-text="String(index + 1).padStart(2, '0')">01</span>
                    <span class="text-navy-400"> / <span x-text="String(stops).padStart(2, '0')">01</span></span>
                </span>
                <button type="button" @click="onNext()" aria-label="Dokumentasi berikutnya" class="testimonial-next testimonial-arrow">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </button>
            </div>

            <div class="testimonial-progress h-1 w-40 flex-none overflow-hidden rounded-full bg-navy-200 sm:w-52 dark:bg-white/10">
                <div class="h-full rounded-full bg-emerald-brand transition-[width] duration-500 ease-out"
                     {{-- Bentuk objek, bukan string: Alpine menulis string lewat
                          setAttribute('style') yang ditolak CSP; objek dipasang
                          lewat setProperty sehingga aman. --}}
                     :style="{ width: `${((index + 1) / stops) * 100}%` }"></div>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-1.5">
                <template x-for="i in stops" :key="i">
                    <button type="button" @click="onDot(i - 1)"
                            :aria-label="`Ke dokumentasi ${i}`"
                            :aria-current="index === i - 1 ? 'true' : 'false'"
                            class="testimonial-dot"
                            :class="index === i - 1 ? 'testimonial-dot--active' : ''"></button>
                </template>
            </div>
        </div>
        </div>{{-- /testimonial-viewport --}}
    </div>
</section>
@endif
