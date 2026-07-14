@php($about = config('company.about'))
@if (filled($about))
<section id="tentang" class="about-section section relative overflow-hidden bg-white dark:bg-navy-950">
    {{-- Aksen merah halus (light & dark) --}}
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -top-24 right-4 h-72 w-72 rounded-full bg-emerald-brand/[0.07] blur-3xl dark:bg-emerald-brand/20"></div>
        <div class="absolute -bottom-24 -left-16 h-72 w-72 rounded-full bg-tosca-400/5 blur-3xl dark:bg-tosca-400/10"></div>
    </div>

    <div class="about-grid container-x relative grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
        {{-- ============ KIRI: media card (kolase.webp) ============
             Ganti gambar nanti: ubah src pada .about-media-image ke asset('images/NAMA.png'). --}}
        <div class="about-media reveal reveal-left relative order-1 lg:order-0">
            <div class="about-media-card group relative overflow-hidden rounded-[1.75rem] border border-black/10 bg-linear-to-br from-navy-950 via-navy-900 to-[#2a0f12] shadow-2xl shadow-navy-900/25 ring-1 ring-black/5 dark:border-white/10 dark:ring-white/5">
                <div class="relative aspect-4/5 w-full overflow-hidden">
                    <img src="{{ asset('images/kolase.webp') }}"
                         alt="Dokumentasi proses pendampingan, konsultasi, dan penguatan sertifikasi halal bersama PT Zam Zam Khan"
                         width="1122" height="1402" loading="lazy" decoding="async"
                         class="about-media-image h-full w-full object-cover object-center transition duration-700 group-hover:scale-[1.04]">

                    {{-- overlay gradient tipis: atas untuk badge, bawah untuk teks --}}
                    <div class="pointer-events-none absolute inset-x-0 top-0 h-24 bg-linear-to-b from-black/45 to-transparent"></div>
                    <div class="pointer-events-none absolute inset-x-0 bottom-0 h-2/5 bg-linear-to-t from-black/80 via-black/35 to-transparent"></div>


                    {{-- overlay text bawah --}}
                    <div class="absolute inset-x-0 bottom-0 p-5 sm:p-6">
                        <p class="font-display text-lg font-bold leading-snug text-white sm:text-xl">Konsultasi, Pendampingan &amp; Sertifikasi</p>
                        <p class="mt-1.5 max-w-md text-xs leading-relaxed text-white/80 sm:text-sm">Dokumentasi kegiatan pendampingan, diskusi, dan penguatan sertifikasi halal.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============ KANAN: konten ============ --}}
        <div class="about-content reveal reveal-right order-2 lg:order-0" data-reveal-delay="120">
            <span class="eyebrow">Tentang Kami</span>
            <h2 class="mt-3 max-w-xl font-display text-3xl font-extrabold leading-[1.15] text-navy-900 sm:text-4xl dark:text-white">
                Mitra Pendamping Halal, Legalitas &amp; Sertifikasi Usaha Anda
            </h2>

            <div class="mt-5 max-w-xl space-y-4 leading-relaxed text-navy-600 dark:text-navy-300">
                @foreach (preg_split('/\r\n|\r|\n/', $about, -1, PREG_SPLIT_NO_EMPTY) as $para)
                    <p>{{ $para }}</p>
                @endforeach
            </div>

            {{-- trust line callout --}}
            <div class="about-trust mt-6 flex items-start gap-3 rounded-xl border border-emerald-brand/15 bg-emerald-brand/4 px-4 py-3 dark:border-emerald-brand/25 dark:bg-emerald-brand/10">
                <svg class="mt-0.5 h-5 w-5 flex-none text-emerald-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm font-medium text-navy-700 dark:text-navy-200">Pendampingan dilakukan dari konsultasi awal, pengecekan kebutuhan, persiapan dokumen, hingga arahan proses lanjutan.</p>
            </div>

            {{-- checklist --}}
            <div class="about-checklist mt-7 grid gap-3 sm:grid-cols-2">
                @foreach ([
                    'Fokus pada halal, legalitas, BPOM, HAKI, dan perpajakan',
                    'Cocok untuk UMKM, restoran, catering, dan produsen makanan',
                    'Pendampingan lebih jelas dari konsultasi awal sampai proses selesai',
                    'Berbasis di Kota Malang dengan layanan konsultasi terarah',
                ] as $point)
                    <div class="flex items-start gap-2.5">
                        <span class="mt-0.5 flex h-6 w-6 flex-none items-center justify-center rounded-full bg-emerald-brand/10 text-emerald-brand dark:bg-emerald-brand/20">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <span class="text-sm font-medium text-navy-700 dark:text-navy-200">{{ $point }}</span>
                    </div>
                @endforeach
            </div>

            {{-- mini stats --}}
            <div class="about-stats mt-8 grid grid-cols-3 gap-3">
                <div class="rounded-2xl border border-navy-100 bg-navy-50/60 p-4 dark:border-white/10 dark:bg-white/5">
                    <p class="font-display text-2xl font-extrabold text-navy-900 dark:text-white"><span data-count="500" data-suffix="++">500++</span></p>
                    <p class="mt-1 text-xs font-semibold text-navy-700 dark:text-navy-200">Pelaku Usaha</p>
                    <p class="text-[11px] leading-tight text-navy-500 dark:text-navy-400">telah didampingi</p>
                </div>
                <div class="rounded-2xl border border-navy-100 bg-navy-50/60 p-4 dark:border-white/10 dark:bg-white/5">
                    <p class="font-display text-2xl font-extrabold text-navy-900 dark:text-white"><span data-count="6" data-suffix="+">6+</span></p>
                    <p class="mt-1 text-xs font-semibold text-navy-700 dark:text-navy-200">Area Layanan</p>
                    <p class="text-[11px] leading-tight text-navy-500 dark:text-navy-400">halal, legalitas, BPOM, HAKI, pajak, label</p>
                </div>
                <div class="rounded-2xl border border-navy-100 bg-navy-50/60 p-4 dark:border-white/10 dark:bg-white/5">
                    <p class="font-display text-xl font-extrabold text-navy-900 dark:text-white">Malang</p>
                    <p class="mt-1 text-xs font-semibold text-navy-700 dark:text-navy-200">&nbsp;</p>
                    <p class="text-[11px] leading-tight text-navy-500 dark:text-navy-400">basis layanan konsultasi</p>
                </div>
            </div>

        </div>
    </div>
</section>
@endif
