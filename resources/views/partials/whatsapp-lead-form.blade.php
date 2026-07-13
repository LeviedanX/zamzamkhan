@php
    $waNumber = preg_replace('/\D+/', '', (string) (config('company.whatsapp_number') ?: config('company.phone_raw')));
    if (str_starts_with($waNumber, '08')) {
        $waNumber = '62' . substr($waNumber, 1);
    } elseif (str_starts_with($waNumber, '8')) {
        $waNumber = '62' . $waNumber;
    }

    $waServices = collect(config('company.services', []))
        ->pluck('title')
        ->filter()
        ->values();
@endphp

<div x-data="whatsappLeadForm({
        waNumber: @js($waNumber),
        services: @js($waServices),
    })"
     x-show="open"
     x-cloak
     @keydown.escape.window="close()"
     class="wa-lead-shell fixed inset-0 z-[90]"
     role="dialog"
     aria-modal="true"
     aria-labelledby="wa-lead-title">
    <div class="wa-lead-overlay absolute inset-0" x-show="open" x-transition.opacity @click="close()"></div>

    <div class="wa-lead-stage relative flex min-h-dvh items-end justify-center p-0 sm:items-center sm:p-6">
        <form class="wa-lead-panel"
              x-show="open"
              x-transition:enter="transition ease-out duration-250"
              x-transition:enter-start="opacity-0 translate-y-6 sm:scale-95"
              x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
              x-transition:leave="transition ease-in duration-150"
              x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
              x-transition:leave-end="opacity-0 translate-y-6 sm:scale-95"
              @submit.prevent="submit"
              novalidate>
            <div class="wa-lead-header">
                <span class="wa-lead-eyebrow">Konsultasi WhatsApp</span>
                <button type="button" class="wa-lead-close" @click="close()" aria-label="Tutup form konsultasi">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>

            <h2 id="wa-lead-title" class="mt-2 font-display text-2xl font-extrabold leading-tight text-navy-900 dark:text-white"
                x-text="mode === 'service' ? 'Konsultasi layanan' : 'Konsultasikan kebutuhan usaha Anda'">
                Konsultasikan kebutuhan usaha Anda
            </h2>
            <p class="mt-2 text-sm leading-relaxed text-navy-600 dark:text-navy-300"
               x-text="mode === 'service'
                    ? 'Isi data singkat berikut. Layanan sudah dipilih dari card yang Anda klik dan tidak bisa diubah dari form ini.'
                    : 'Pilih layanan dan ceritakan kebutuhan usaha Anda untuk melanjutkan konsultasi melalui WhatsApp.'">
                Pilih layanan dan ceritakan kebutuhan usaha Anda untuk melanjutkan konsultasi melalui WhatsApp.
            </p>

            <div class="wa-lead-alert" x-show="formError" x-transition x-cloak role="alert" aria-live="assertive">
                <svg class="h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                <span x-text="formError"></span>
            </div>

            <div class="mt-6 grid gap-4">
                <div class="wa-lead-field">
                    <label for="wa-lead-name" class="wa-lead-label">Nama <span>*</span></label>
                    <input id="wa-lead-name" x-ref="name" x-model.trim="values.name" @input="clearError('name')"
                           type="text" maxlength="80" autocomplete="name" class="wa-lead-input"
                           :class="{ 'wa-lead-input--error': errors.name }" :aria-invalid="errors.name ? 'true' : 'false'"
                           placeholder="Nama lengkap Anda" required>
                    <p class="wa-lead-error" x-show="errors.name" x-text="errors.name" x-cloak></p>
                </div>

                <div class="wa-lead-field" x-show="mode === 'service'">
                    <span class="wa-lead-label">Layanan dipilih <span>*</span></span>
                    <div class="wa-lead-fixed-service">
                        <svg class="h-4 w-4 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span x-text="values.service || 'Layanan belum terdeteksi'">Layanan belum terdeteksi</span>
                    </div>
                    <p class="wa-lead-error" x-show="errors.service" x-text="errors.service" x-cloak></p>
                </div>

                <div class="wa-lead-field" x-show="mode === 'undecided'" x-cloak>
                    <label for="wa-lead-service" class="wa-lead-label">Layanan yang dibutuhkan <span>*</span></label>
                    <div class="wa-lead-select-wrap">
                        <select id="wa-lead-service" x-model="values.service" @change="clearError('service')"
                                class="wa-lead-input wa-lead-select"
                                :class="{ 'wa-lead-input--error': errors.service }"
                                :aria-invalid="errors.service ? 'true' : 'false'" required>
                            <option value="">Pilih salah satu layanan</option>
                            @foreach ($waServices as $service)
                                <option value="{{ $service }}">{{ $service }}</option>
                            @endforeach
                        </select>
                        <svg class="wa-lead-select-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <p class="wa-lead-error" x-show="errors.service" x-text="errors.service" x-cloak></p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="wa-lead-field">
                        <label for="wa-lead-business" class="wa-lead-label">Jenis usaha/produk</label>
                        <input id="wa-lead-business" x-model.trim="values.business" @input="clearError('business')" type="text" maxlength="90"
                               class="wa-lead-input" :class="{ 'wa-lead-input--error': errors.business }" :aria-invalid="errors.business ? 'true' : 'false'"
                               placeholder="Contoh: makanan ringan">
                        <p class="wa-lead-error" x-show="errors.business" x-text="errors.business" x-cloak></p>
                    </div>
                    <div class="wa-lead-field">
                        <label for="wa-lead-domicile" class="wa-lead-label">Domisili</label>
                        <input id="wa-lead-domicile" x-model.trim="values.domicile" @input="clearError('domicile')" type="text" maxlength="80"
                               autocomplete="address-level2" class="wa-lead-input" :class="{ 'wa-lead-input--error': errors.domicile }" :aria-invalid="errors.domicile ? 'true' : 'false'"
                               placeholder="Contoh: Malang">
                        <p class="wa-lead-error" x-show="errors.domicile" x-text="errors.domicile" x-cloak></p>
                    </div>
                </div>

                <div class="wa-lead-field">
                    <div class="flex items-center justify-between gap-3">
                        <label for="wa-lead-needs" class="wa-lead-label">
                            <span x-text="mode === 'service' ? 'Kebutuhan singkat' : 'Ceritakan kebutuhan atau kendala usaha'">Ceritakan kebutuhan atau kendala usaha</span>
                            <span>*</span>
                        </label>
                        <span class="wa-lead-count" x-text="`${values.needs.length}/280`">0/280</span>
                    </div>
                    <textarea id="wa-lead-needs" x-model.trim="values.needs" @input="clearError('needs')"
                              rows="4" maxlength="280" class="wa-lead-input wa-lead-textarea"
                              :class="{ 'wa-lead-input--error': errors.needs }" :aria-invalid="errors.needs ? 'true' : 'false'"
                              :placeholder="mode === 'service' ? 'Ceritakan kebutuhan utama Anda' : 'Ceritakan kondisi usaha atau kendala yang sedang dihadapi'"
                              required></textarea>
                    <p class="wa-lead-error" x-show="errors.needs" x-text="errors.needs" x-cloak></p>
                </div>
            </div>

            <div class="wa-lead-actions">
                <button type="button" class="btn-outline wa-lead-cancel" @click="close()">Batal</button>
                <button type="submit" class="btn-primary wa-lead-submit">
                    <svg viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4" aria-hidden="true"><path d="M12.04 2c-5.46 0-9.9 4.44-9.9 9.9 0 1.75.46 3.45 1.32 4.95L2 22l5.3-1.38a9.86 9.86 0 004.74 1.21h.01c5.46 0 9.9-4.44 9.9-9.9 0-2.64-1.03-5.13-2.9-7A9.82 9.82 0 0012.04 2z"/></svg>
                    Lanjut ke WhatsApp
                </button>
            </div>
        </form>
    </div>
</div>
