@php
    // Nomor bisa null (admin mengosongkan field) → key ada, sehingga default config() tidak berlaku.
    $contactWaNumber = preg_replace('/\D+/', '', (string) (config('company.whatsapp_number') ?: config('company.phone_raw')));
    $contactServices = collect(config('company.services', []))
        ->pluck('title')
        ->filter()
        ->unique()
        ->values();
    $hasWhatsapp = filled($contactWaNumber);
    if ($hasWhatsapp && $contactServices->isEmpty()) {
        $contactServices = collect(['Konsultasi umum']);
    }
    $hasAddress = filled(config('company.address'));
    $hasPhone = filled(config('company.phone_display'));
    $hasEmail = filled(config('company.email'));
    $hasMapsUrl = filled(config('company.maps_url'));
    $hasMapsEmbed = filled(config('company.maps_embed'));
    $hasContactSection = $hasWhatsapp || $hasAddress || $hasPhone || $hasEmail || $hasMapsUrl || $hasMapsEmbed || filled(config('company.operating_hours'));
@endphp

@if ($hasContactSection)
<section id="kontak" class="contact-section section relative overflow-hidden pb-32 sm:pb-28">
    {{-- Background premium: gradient + red glow + grid halus + garis aksen --}}
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <div class="contact-glow contact-glow--1"></div>
        <div class="contact-glow contact-glow--2"></div>
        <div class="absolute inset-0 hero-grid opacity-[0.04] dark:opacity-[0.06]"></div>
        <div class="contact-accent-line"></div>
    </div>

    <div class="container-x contact-container relative grid gap-10 {{ $hasWhatsapp ? 'lg:grid-cols-2' : 'lg:grid-cols-1' }} lg:gap-14">
        {{-- ── Kiri: info kontak ── --}}
        <div class="reveal reveal-left">
            <span class="contact-badge"><span class="contact-badge-dot"></span> Kontak</span>
            <h2 class="mt-4 font-display text-3xl font-extrabold leading-tight text-navy-900 sm:text-4xl dark:text-white">
                Hubungi <span class="text-gradient">{{ config('company.name', 'PT Zam Zam Khan') }}</span>
            </h2>
            <p class="mt-4 max-w-lg leading-relaxed text-navy-600 dark:text-navy-300">
                Tim PT Zam Zam Khan siap membantu konsultasi legalitas usaha, sertifikasi halal, dan kebutuhan administrasi bisnis Anda — melalui WhatsApp, email, atau kunjungan langsung ke kantor.
            </p>

            <ul class="mt-8 space-y-4">
                @if ($hasAddress)
                <li class="contact-card group">
                    <span class="contact-card-icon">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="font-display text-sm font-bold text-navy-900 dark:text-white">Alamat Kantor {{ config('company.name', 'PT Zam Zam Khan') }}</p>
                        <p class="mt-1 text-sm leading-relaxed text-navy-600 dark:text-navy-300">{{ config('company.address') }}</p>
                        @if ($hasMapsUrl)
                            <a href="{{ config('company.maps_url') }}" target="_blank" rel="noopener noreferrer" class="contact-link mt-1.5">Lihat di Google Maps →</a>
                        @endif
                    </div>
                </li>
                @endif
                @if ($hasPhone)
                <li class="contact-card group">
                    <span class="contact-card-icon">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11 11 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="font-display text-sm font-bold text-navy-900 dark:text-white">Telepon / WhatsApp</p>
                        <a href="tel:{{ config('company.phone_raw') }}" class="mt-1 inline-block text-sm text-navy-600 transition-colors hover:text-emerald-brand dark:text-navy-300">{{ config('company.phone_display') }}</a>
                    </div>
                </li>
                @endif
                @if ($hasEmail)
                <li class="contact-card group">
                    <span class="contact-card-icon">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="font-display text-sm font-bold text-navy-900 dark:text-white">Email</p>
                        <a href="mailto:{{ config('company.email') }}" class="mt-1 inline-block break-all text-sm text-navy-600 transition-colors hover:text-emerald-brand dark:text-navy-300">{{ config('company.email') }}</a>
                    </div>
                </li>
                @endif
                @if (filled(config('company.operating_hours')))
                <li class="contact-card group">
                    <span class="contact-card-icon">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="font-display text-sm font-bold text-navy-900 dark:text-white">Jam Operasional</p>
                        <p class="mt-1 text-sm leading-relaxed text-navy-600 dark:text-navy-300">{{ config('company.operating_hours') }}</p>
                    </div>
                </li>
                @endif
            </ul>

            {{-- Trust mini badges --}}
            @if ($hasWhatsapp)
            <div class="mt-6 flex flex-wrap gap-2.5">
                @foreach (['Respon cepat via WhatsApp', 'Konsultasi awal lebih mudah', 'Pendampingan lebih terarah'] as $trust)
                    <span class="contact-trust">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        {{ $trust }}
                    </span>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Kanan: konsultasi langsung menuju WhatsApp. --}}
        @if ($hasWhatsapp)
        <div class="reveal reveal-right" data-reveal-delay="120">
            <div class="contact-quick-card contact-consultation-card"
                 x-data="whatsappLeadForm({
                    waNumber: @js($contactWaNumber),
                    services: @js($contactServices),
                    inline: true,
                 })">
                <div class="contact-consultation-card__header">
                    <span class="contact-quick-card__eyebrow">Konsultasi WhatsApp</span>
                    <span class="contact-consultation-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2c-5.46 0-9.9 4.44-9.9 9.9 0 1.75.46 3.45 1.32 4.95L2 22l5.3-1.38a9.86 9.86 0 004.74 1.21h.01c5.46 0 9.9-4.44 9.9-9.9 0-2.64-1.03-5.13-2.9-7A9.82 9.82 0 0012.04 2z"/></svg>
                    </span>
                </div>
                <h3 class="mt-3 font-display text-2xl font-extrabold leading-tight text-navy-900 dark:text-white">Konsultasikan kebutuhan usaha Anda</h3>
                <p class="mt-3 text-sm leading-relaxed text-navy-600 dark:text-navy-300">
                    Isi data singkat berikut untuk melanjutkan konsultasi langsung dengan tim kami melalui WhatsApp.
                </p>

                <form class="mt-6 grid gap-4" @submit.prevent="submit" novalidate aria-label="Formulir konsultasi WhatsApp">
                    <div class="wa-lead-alert !mt-0" x-show="formError" x-transition x-cloak role="alert" aria-live="assertive">
                        <svg class="h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                        <span x-text="formError"></span>
                    </div>

                    <div class="wa-lead-field">
                        <label for="contact-wa-name" class="wa-lead-label">Nama <span>*</span></label>
                        <input id="contact-wa-name" x-ref="name" x-model.trim="values.name" @input="clearError('name')"
                               type="text" maxlength="80" autocomplete="name" class="wa-lead-input"
                               :class="{ 'wa-lead-input--error': errors.name }" :aria-invalid="errors.name ? 'true' : 'false'"
                               placeholder="Nama lengkap Anda" required>
                        <p class="wa-lead-error" x-show="errors.name" x-text="errors.name" x-cloak></p>
                    </div>

                    <div class="wa-lead-field">
                        <label for="contact-wa-service" class="wa-lead-label">Layanan yang dibutuhkan <span>*</span></label>
                        <div class="wa-lead-select-wrap">
                            <select id="contact-wa-service" x-model="values.service" @change="clearError('service')"
                                    class="wa-lead-input wa-lead-select"
                                    :class="{ 'wa-lead-input--error': errors.service }"
                                    :aria-invalid="errors.service ? 'true' : 'false'" required>
                                <option value="">Pilih salah satu layanan</option>
                                @foreach ($contactServices as $service)
                                    <option value="{{ $service }}">{{ $service }}</option>
                                @endforeach
                            </select>
                            <svg class="wa-lead-select-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <p class="wa-lead-error" x-show="errors.service" x-text="errors.service" x-cloak></p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="wa-lead-field">
                            <label for="contact-wa-business" class="wa-lead-label">Jenis usaha/produk</label>
                            <input id="contact-wa-business" x-model.trim="values.business" @input="clearError('business')"
                                   type="text" maxlength="90" class="wa-lead-input"
                                   :class="{ 'wa-lead-input--error': errors.business }" :aria-invalid="errors.business ? 'true' : 'false'"
                                   placeholder="Contoh: makanan ringan">
                            <p class="wa-lead-error" x-show="errors.business" x-text="errors.business" x-cloak></p>
                        </div>
                        <div class="wa-lead-field">
                            <label for="contact-wa-domicile" class="wa-lead-label">Domisili</label>
                            <input id="contact-wa-domicile" x-model.trim="values.domicile" @input="clearError('domicile')"
                                   type="text" maxlength="80" autocomplete="address-level2" class="wa-lead-input"
                                   :class="{ 'wa-lead-input--error': errors.domicile }" :aria-invalid="errors.domicile ? 'true' : 'false'"
                                   placeholder="Contoh: Malang">
                            <p class="wa-lead-error" x-show="errors.domicile" x-text="errors.domicile" x-cloak></p>
                        </div>
                    </div>

                    <div class="wa-lead-field">
                        <div class="flex items-center justify-between gap-3">
                            <label for="contact-wa-needs" class="wa-lead-label">Ceritakan kebutuhan atau kendala usaha <span>*</span></label>
                            <span class="wa-lead-count" x-text="`${values.needs.length}/280`">0/280</span>
                        </div>
                        <textarea id="contact-wa-needs" x-model.trim="values.needs" @input="clearError('needs')"
                                  rows="4" maxlength="280" class="wa-lead-input wa-lead-textarea"
                                  :class="{ 'wa-lead-input--error': errors.needs }" :aria-invalid="errors.needs ? 'true' : 'false'"
                                  placeholder="Ceritakan kondisi usaha atau kendala yang sedang dihadapi" required></textarea>
                        <p class="wa-lead-error" x-show="errors.needs" x-text="errors.needs" x-cloak></p>
                    </div>

                    <button type="submit" class="btn-primary contact-consultation-card__submit w-full">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4"><path d="M12.04 2c-5.46 0-9.9 4.44-9.9 9.9 0 1.75.46 3.45 1.32 4.95L2 22l5.3-1.38a9.86 9.86 0 004.74 1.21h.01c5.46 0 9.9-4.44 9.9-9.9 0-2.64-1.03-5.13-2.9-7A9.82 9.82 0 0012.04 2z"/></svg>
                        Lanjut ke WhatsApp
                    </button>
                </form>
            </div>

        </div>

        {{-- Lokasi Kami — peta interaktif (zoom in/out), full width --}}
        @endif
        @if ($hasMapsUrl || $hasMapsEmbed)
        <div class="reveal mt-10 lg:col-span-2" data-reveal-delay="80">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="font-display text-xl font-bold text-navy-900 dark:text-white">Lokasi Kami</h3>
                    <p class="mt-1 text-sm text-navy-500 dark:text-navy-300">Kunjungi kantor PT Zam Zam Khan di Dinoyo, Kota Malang.</p>
                </div>
                @if ($hasMapsUrl)
                <a href="{{ config('company.maps_url') }}" target="_blank" rel="noopener noreferrer"
                   class="btn-outline !py-2.5 self-start sm:self-auto">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Buka di Google Maps
                </a>
                @endif
            </div>
            @if ($hasMapsEmbed)
            <div class="overflow-hidden rounded-2xl border border-navy-100 shadow-xl shadow-navy-900/5 dark:border-navy-800">
                <iframe
                    src="{{ config('company.maps_embed') }}"
                    title="Peta lokasi PT Zam Zam Khan, Jl. MT Haryono Gang 6B No.949, Dinoyo, Lowokwaru, Kota Malang"
                    class="block h-[280px] w-full border-0 sm:h-[360px] lg:h-[420px]"
                    loading="lazy"
                    allowfullscreen
                    referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
            @endif
        </div>
        @endif
    </div>
</section>
@endif
