@if (! empty(config('company.faq')))
<section id="faq" class="faq-section section relative overflow-hidden">
    {{-- Background premium: gradient lembut + red glow + grid halus + garis aksen --}}
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <div class="faq-glow faq-glow--1"></div>
        <div class="faq-glow faq-glow--2"></div>
        <div class="absolute inset-0 hero-grid opacity-[0.05] dark:opacity-[0.07]"></div>
        <div class="faq-accent-line"></div>
    </div>

    <div class="container-x relative grid items-center gap-10 lg:grid-cols-12 lg:gap-14">
        {{-- Kiri: intro FAQ --}}
        <div class="reveal reveal-left lg:col-span-4 lg:pr-2">
            <span class="faq-badge">
                <span class="faq-badge-dot"></span> FAQ
            </span>
            <h2 class="mt-4 font-display text-3xl font-extrabold leading-tight text-navy-900 sm:text-4xl dark:text-white">
                Pertanyaan yang <span class="text-gradient">Sering Diajukan</span>
            </h2>
            <p class="mt-4 max-w-md leading-relaxed text-navy-600 dark:text-navy-300">
                Temukan jawaban atas pertanyaan umum seputar layanan konsultasi halal dan legalitas usaha PT Zam Zam Khan.
            </p>

        </div>

        {{-- ── Kanan: accordion card premium ── --}}
        <div class="reveal reveal-right lg:col-span-8" data-reveal-delay="120" x-data="{ active: 0 }">
            <div class="faq-card">
                @foreach (config('company.faq') as $i => $item)
                    <div class="faq-item" :class="active === {{ $i }} ? 'faq-item--active' : ''"
                         data-delay="{{ $i * 70 }}">
                        <h3>
                            <button type="button" @click="active === {{ $i }} ? active = null : active = {{ $i }}"
                                    class="faq-q" :aria-expanded="active === {{ $i }} ? 'true' : 'false'"
                                    aria-controls="faq-panel-{{ $i }}">
                                <span class="faq-num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="faq-q-text">{{ $item['q'] }}</span>
                                <span class="faq-icon" :class="active === {{ $i }} ? 'faq-icon--active' : ''" aria-hidden="true"></span>
                            </button>
                        </h3>
                        <div id="faq-panel-{{ $i }}" x-show="active === {{ $i }}" x-collapse x-cloak>
                            <p class="faq-a">{{ $item['a'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
