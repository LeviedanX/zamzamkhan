@php($eduCards = [
    ['img' => 'card1.png', 'alt' => 'Edukasi layanan tentang pengertian sertifikat halal'],
    ['img' => 'card2.png', 'alt' => 'Edukasi layanan tentang pentingnya sertifikat halal di Indonesia'],
    ['img' => 'card3.png', 'alt' => 'Edukasi layanan tentang manfaat sertifikat halal untuk produk usaha'],
])
<section id="edukasi-halal" class="education-section section relative overflow-hidden border-t border-navy-100 bg-white dark:border-white/10 dark:bg-navy-950">
    {{-- Background decorative subtle --}}
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <div class="vm-glow-pulse absolute -top-24 right-1/4 h-72 w-72 rounded-full bg-emerald-brand/6 blur-3xl dark:bg-emerald-brand/20"></div>
        <div class="vm-glow-pulse absolute -bottom-24 left-0 h-72 w-72 rounded-full bg-tosca-400/5 blur-3xl dark:bg-tosca-400/10"></div>
        <div class="hero-grid absolute inset-0 opacity-[0.03] dark:opacity-[0.05]"></div>
    </div>

    <div class="container-x relative">
        {{-- Header --}}
        <div class="education-header reveal mx-auto max-w-2xl text-center">
            <span class="eyebrow">Edukasi Layanan</span>
            <h2 class="mt-3 font-display text-3xl font-extrabold leading-tight text-navy-900 sm:text-4xl dark:text-white">
                Mengapa Sertifikasi Halal Penting untuk Usaha Anda?
            </h2>
            <p class="mt-4 leading-relaxed text-navy-600 dark:text-navy-300">
                Kami membantu pelaku usaha memahami fungsi, urgensi, dan manfaat sertifikasi halal sebagai bagian dari pertumbuhan bisnis yang legal, aman, dan terpercaya.
            </p>
        </div>

        {{-- 3 card edukasi — rasio dikunci 3:4, tinggi & lebar konsisten --}}
        <div class="education-grid mx-auto mt-10 grid max-w-sm grid-cols-1 items-stretch gap-6 sm:max-w-2xl sm:grid-cols-2 lg:max-w-5xl lg:grid-cols-3">
            @foreach ($eduCards as $i => $c)
                <article class="education-card reveal reveal-scale" data-reveal-delay="{{ $i * 90 }}">
                    <div class="education-card__media">
                        <img src="{{ asset('images/card/'.$c['img']) }}"
                             alt="{{ $c['alt'] }}"
                             width="1086" height="1448"
                             loading="lazy" decoding="async"
                             class="education-card__image">
                    </div>
                </article>
            @endforeach
        </div>

        {{-- CTA sekunder --}}
        <div class="mt-10 flex justify-center">
            <a href="#layanan" class="btn-outline">Lihat Layanan Kami</a>
        </div>
    </div>
</section>
