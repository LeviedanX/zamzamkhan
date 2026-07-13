@php
    // Peta ikon → modifier CSS kartu (murni presentasi). Seluruh isi teks layanan
    // datang dari database; tidak ada konten cadangan hardcoded di sini agar field
    // yang sengaja dikosongkan admin tidak dihidupkan kembali.
    $serviceCardMods = [
        'halal' => 'halal',
        'halal-reg' => 'halal-reguler',
        'nib' => 'nib',
        'akta' => 'akta',
        'pajak' => 'npwp',
        'bpom' => 'bpom',
        'haki' => 'haki',
        'desain' => 'desain',
    ];
@endphp
@php($serviceWhatsappEnabled = filled(config('company.whatsapp_number')))

@if (! empty(config('company.services')))
<section id="layanan" class="section bg-navy-50/60 dark:bg-navy-900"
         x-data="{ open: false, svc: { title: '', long: '', cocok: '', benefits: [], alur: [], waMessage: '' } }"
         @keydown.escape.window="open = false"
         x-effect="document.body.style.overflow = open ? 'hidden' : ''">
    <div class="container-x">
        <div class="reveal mx-auto max-w-2xl text-center">
            <span class="eyebrow">Layanan Utama</span>
            <h2 class="mt-3 text-3xl font-bold text-navy-900 sm:text-4xl dark:text-white">Solusi Legalitas &amp; Sertifikasi untuk Usaha Anda</h2>
            <p class="mt-4 text-navy-600 dark:text-navy-300">Pilih layanan pendampingan yang sesuai dengan kebutuhan usaha Anda — mulai dari sertifikasi halal hingga desain identitas produk.</p>
            <p class="service-section-note mt-4">Setiap layanan memiliki detail dan alur pendampingan yang dapat dibaca melalui tombol Detail.</p>
        </div>

        <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (config('company.services') as $i => $service)
                @php($mod = $serviceCardMods[$service['icon'] ?? ''] ?? 'halal')
                @php($modal = [
                    'title' => $service['title'],
                    'long' => filled($service['detail'] ?? null) ? $service['detail'] : (string) ($service['desc'] ?? ''),
                    'benefits' => array_values($service['benefits'] ?? []),
                    'cocok' => (string) ($service['suitable_for'] ?? ''),
                    'alur' => array_values($service['workflow_steps'] ?? []),
                    'waMessage' => (string) ($service['whatsapp_message'] ?? ''),
                ])
                <article class="service-card service-card--{{ $mod }} reveal reveal-scale group relative" data-reveal-delay="{{ ($i % 4) * 80 }}">
                    @if (! empty($service['is_featured']))
                        <span class="service-featured-badge">
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m12 2.8 2.7 5.5 6.1.9-4.4 4.3 1 6.1-5.4-2.8-5.4 2.8 1-6.1-4.4-4.3 6.1-.9L12 2.8Z"/></svg>
                            Unggulan
                        </span>
                    @endif
                    <span class="service-watermark" aria-hidden="true">@include('partials.service-icon', ['icon' => $service['icon']])</span>
                    <div class="service-icon-box">@include('partials.service-icon', ['icon' => $service['icon']])</div>
                    <h3 class="service-card__title">{{ $service['title'] }}</h3>
                    @if (filled($service['desc'] ?? null))
                        <p class="service-card__desc">{{ $service['desc'] }}</p>
                    @endif
                    <div class="service-card__actions">
                        <button type="button" class="service-btn service-btn--ghost"
                                @click="svc = @js($modal); open = true"
                                aria-haspopup="dialog">
                            Detail
                        </button>
                        @if ($serviceWhatsappEnabled)
                        <button type="button"
                                data-whatsapp-lead
                                data-mode="service"
                                data-service="{{ $service['title'] }}"
                                data-needs="{{ $service['whatsapp_message'] ?? '' }}"
                                class="service-btn service-btn--primary" aria-label="Konsultasikan {{ $service['title'] }} via WhatsApp">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.9 4.44-9.9 9.9 0 1.75.46 3.45 1.32 4.95L2 22l5.3-1.38a9.86 9.86 0 004.74 1.21h.01c5.46 0 9.9-4.44 9.9-9.9 0-2.64-1.03-5.13-2.9-7A9.82 9.82 0 0012.04 2z"/></svg>
                            Konsultasikan
                        </button>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>

    {{-- ── Modal detail layanan (reusable) ── --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-60 flex items-end justify-center p-0 sm:items-center sm:p-6"
         role="dialog" aria-modal="true" aria-labelledby="service-modal-title">
        <div class="absolute inset-0 bg-navy-950/60 backdrop-blur-sm"
             x-show="open" x-transition.opacity @click="open = false"></div>
        <div class="service-modal relative w-full max-w-lg"
             x-show="open"
             x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0 translate-y-6 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-6 sm:scale-95">
            <button type="button" @click="open = false" aria-label="Tutup detail" class="service-modal__close">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
            </button>

            <span class="eyebrow">Detail Layanan</span>
            <h3 id="service-modal-title" class="mt-2 pr-10 font-display text-2xl font-extrabold text-navy-900 dark:text-white" x-text="svc.title"></h3>

            <template x-if="svc.long">
                <p class="mt-3 text-sm leading-relaxed text-navy-600 dark:text-navy-300" x-text="svc.long"></p>
            </template>

            <template x-if="svc.cocok">
                <div class="service-modal__block mt-5">
                    <p class="service-modal__label">Cocok untuk</p>
                    <p class="mt-1 text-sm text-navy-600 dark:text-navy-300" x-text="svc.cocok"></p>
                </div>
            </template>

            <template x-if="svc.benefits && svc.benefits.length">
                <div class="service-modal__block mt-5">
                    <p class="service-modal__label">Manfaat utama</p>
                    <ul class="mt-2 space-y-2">
                        <template x-for="(benefit, idx) in svc.benefits" :key="idx">
                            <li class="flex items-start gap-2 text-sm text-navy-700 dark:text-navy-200">
                                <span class="mt-1 h-1.5 w-1.5 flex-none rounded-full bg-red-800"></span>
                                <span x-text="benefit"></span>
                            </li>
                        </template>
                    </ul>
                </div>
            </template>

            <template x-if="svc.alur && svc.alur.length">
                <div class="service-modal__block mt-5">
                    <p class="service-modal__label">Alur Layanan</p>
                    <ol class="mt-2 space-y-2">
                        <template x-for="(step, idx) in svc.alur" :key="idx">
                            <li class="flex items-start gap-3 text-sm text-navy-700 dark:text-navy-200">
                                <span class="service-modal__step" x-text="idx + 1"></span>
                                <span x-text="step"></span>
                            </li>
                        </template>
                    </ol>
                </div>
            </template>

            @if ($serviceWhatsappEnabled)
            <button type="button" class="btn-primary mt-6 w-full"
                    @click="$dispatch('open-whatsapp-lead', { mode: 'service', service: svc.title, needs: svc.waMessage || '' }); open = false">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.9 4.44-9.9 9.9 0 1.75.46 3.45 1.32 4.95L2 22l5.3-1.38a9.86 9.86 0 004.74 1.21h.01c5.46 0 9.9-4.44 9.9-9.9 0-2.64-1.03-5.13-2.9-7A9.82 9.82 0 0012.04 2z"/></svg>
                Konsultasikan via WhatsApp
            </button>
            @endif
        </div>
    </div>
</section>
@endif
