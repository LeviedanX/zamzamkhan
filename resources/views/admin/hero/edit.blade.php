
@extends('layouts.admin')
@section('title', 'Hero Utama')

@php
    $inp = 'w-full rounded-xl border border-navy-200 bg-white/95 px-4 py-3 text-sm text-navy-900 shadow-sm shadow-navy-900/5 focus:border-emerald-brand focus:outline-none focus:ring-2 focus:ring-emerald-brand/20';
    $label = 'mb-1.5 block text-sm font-semibold text-navy-800';
    $help = 'mt-1.5 text-xs leading-relaxed text-navy-400';
    $defaultChips = ['Sertifikasi Halal', 'Legalitas Usaha', 'BPOM & HAKI', 'Logo & Label Kemasan'];
    $chipItems = collect(preg_split('/\r\n|\r|\n/', old('service_chips', $hero->service_chips) ?: implode("\n", $defaultChips)))->map(fn ($x) => trim($x))->filter()->values();
@endphp

@section('content')
<div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
    <div>
        <p class="mb-2 inline-flex rounded-full border border-red-900/10 bg-red-900/5 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-red-900">Homepage Hero</p>
        <h1 class="font-display text-2xl font-bold text-navy-900 sm:text-3xl">Hero Utama</h1>
        <p class="mt-2 max-w-2xl text-sm leading-relaxed text-navy-500">Edit seluruh elemen hero yang benar-benar tampil di homepage.</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.hero.update') }}" enctype="multipart/form-data" class="space-y-6"
      x-data="{
          chips: @js($chipItems->values()),
          chipDraft: '',
          defaultChips: @js($defaultChips),
          normalizedChips() {
              return this.chips.map((chip) => (chip || '').trim()).filter(Boolean);
          },
          chipPayload() {
              return this.normalizedChips().join('\n');
          },
          addChip() {
              const value = this.chipDraft.trim();
              if (! value) return;
              this.chips.push(value);
              this.chipDraft = '';
          },
          removeChip(index) {
              this.chips.splice(index, 1);
          }
      }">
    @csrf @method('PUT')

    <div class="admin-form-shell">
        <div class="space-y-5">
            <section class="admin-hero-form-card rounded-3xl border border-navy-100 bg-white p-5 sm:p-6">
                <header class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="font-display text-lg font-bold text-navy-900">Konten Utama</h2>
                        <p class="mt-1 text-sm text-navy-500">Headline dan narasi pembuka yang langsung terlihat oleh pengunjung.</p>
                    </div>
                    <span class="rounded-full bg-red-900 px-3 py-1 text-xs font-bold text-white">Wajib</span>
                </header>
                <div class="space-y-5">
                    <div>
                        <label class="{{ $label }}">Judul utama <span class="text-red-700">*</span></label>
                        <input name="title" value="{{ old('title', $hero->title) }}" class="{{ $inp }}" required>
                    </div>
                    <div>
                        <label class="{{ $label }}">Subjudul</label>
                        <textarea name="subtitle" rows="4" class="{{ $inp }}">{{ old('subtitle', $hero->subtitle) }}</textarea>
                        <p class="{{ $help }}">Gunakan kalimat ringkas, konkret, dan tetap mudah dibaca di atas background gelap.</p>
                    </div>
                </div>
            </section>

            <section class="admin-hero-form-card rounded-3xl border border-navy-100 bg-white p-5 sm:p-6">
                <header class="mb-5">
                    <h2 class="font-display text-lg font-bold text-navy-900">Tombol Layanan</h2>
                    <p class="mt-1 text-sm text-navy-500">Tombol ini tampil di hero dan selalu mengarahkan pengunjung ke section layanan (<code>#layanan</code>).</p>
                </header>
                <div>
                    <label class="{{ $label }}">Teks tombol</label>
                    <input name="secondary_button_text" value="{{ old('secondary_button_text', $hero->secondary_button_text) }}" class="{{ $inp }}">
                </div>
            </section>

            <section class="admin-hero-form-card rounded-3xl border border-navy-100 bg-white p-5 sm:p-6">
                <header class="mb-5">
                    <h2 class="font-display text-lg font-bold text-navy-900">Badge, Trust, & Chip</h2>
                    <p class="mt-1 text-sm text-navy-500">Elemen kecil di hero public: badge atas, trust line, dan chip layanan.</p>
                </header>
                <div class="grid gap-5 lg:grid-cols-2">
                    <div>
                        <label class="{{ $label }}">Badge atas</label>
                        <input name="badge_text" value="{{ old('badge_text', $hero->badge_text) }}" class="{{ $inp }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">Trust line</label>
                        <input name="trust_text" value="{{ old('trust_text', $hero->trust_text) }}" class="{{ $inp }}">
                    </div>
                </div>
                <div class="mt-5">
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <label class="{{ $label }} mb-0!">Chip layanan</label>
                        <span class="text-xs font-semibold text-navy-400" x-text="`${normalizedChips().length} chip`"></span>
                    </div>
                    <input type="hidden" name="service_chips" :value="chipPayload()">
                    <div class="space-y-2">
                        <template x-for="(chip, index) in chips" :key="index">
                            <div class="flex items-center gap-2 rounded-2xl border border-navy-100 bg-white/70 p-2">
                                <input type="text" x-model="chips[index]" class="{{ $inp }} py-2!" :aria-label="`Chip layanan ${index + 1}`">
                                <button type="button" class="rounded-xl border border-red-900/20 px-3 py-2 text-xs font-bold text-red-800 hover:bg-red-900/5" @click="removeChip(index)">Hapus</button>
                            </div>
                        </template>
                    </div>
                    <div class="mt-3 flex flex-col gap-2 sm:flex-row">
                        <input type="text" x-model="chipDraft" class="{{ $inp }} py-2!" placeholder="Tambah chip layanan" @keydown.enter.prevent="addChip">
                        <button type="button" class="btn-outline shrink-0 py-2!" @click="addChip">Tambah Chip</button>
                    </div>
                    <p class="{{ $help }}">Tambah, ubah, atau hapus chip dari sini. Data disimpan ke database sebagai daftar chip layanan hero.</p>
                </div>
            </section>

            <section class="admin-hero-form-card rounded-3xl border border-navy-100 bg-white p-5 sm:p-6">
                <header class="mb-5">
                    <h2 class="font-display text-lg font-bold text-navy-900">Media & Figur</h2>
                    <p class="mt-1 text-sm text-navy-500">Atur background hero, foto figur direktur, dan caption yang tampil di homepage.</p>
                </header>
                <div class="grid gap-5 lg:grid-cols-2">
                    <div>
                        <label class="{{ $label }}">Gambar latar hero (opsional)</label>
                        <input name="image" type="file" accept="image/jpeg,image/png,image/webp" class="{{ $inp }}">
                        <p class="{{ $help }}">JPG/PNG/WEBP, maksimal 4 MB. Upload baru akan mengganti gambar latar lama.</p>
                        @if ($hero->image_path)
                            <label class="mt-3 flex items-start gap-2 text-xs font-semibold text-navy-600">
                                <input type="checkbox" name="remove_image" value="1" class="mt-0.5 h-4 w-4 rounded border-navy-300 text-red-800 focus:ring-red-800/20">
                                <span>Hapus gambar latar saat ini dan pakai visual bawaan tema.</span>
                            </label>
                        @endif
                    </div>
                    <div>
                        <label class="{{ $label }}">Gambar figur direktur</label>
                        <input name="portrait" type="file" accept="image/jpeg,image/png,image/webp" class="{{ $inp }}">
                        <p class="{{ $help }}">Upload cutout/portrait PNG atau WEBP. Upload baru akan mengganti figur lama.</p>
                        @if ($hero->portrait_path)
                            <label class="mt-3 flex items-start gap-2 text-xs font-semibold text-navy-600">
                                <input type="checkbox" name="remove_portrait" value="1" class="mt-0.5 h-4 w-4 rounded border-navy-300 text-red-800 focus:ring-red-800/20">
                                <span>Hapus gambar figur saat ini dan pakai figur bawaan.</span>
                            </label>
                        @endif
                    </div>
                    <div>
                        <label class="{{ $label }}">Alt gambar figur</label>
                        <input name="portrait_alt" value="{{ old('portrait_alt', $hero->portrait_alt) }}" class="{{ $inp }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">Role figur</label>
                        <input name="portrait_role" value="{{ old('portrait_role', $hero->portrait_role) }}" class="{{ $inp }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">Nama figur</label>
                        <input name="portrait_name" value="{{ old('portrait_name', $hero->portrait_name) }}" class="{{ $inp }}">
                    </div>
                </div>
            </section>
        </div>

    </div>

    <div class="admin-hero-actionbar flex flex-col gap-3 rounded-3xl border border-navy-100 bg-white/90 p-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm leading-relaxed text-navy-500">Simpan perubahan untuk memperbarui elemen hero di website publik.</p>
        <div class="flex flex-col gap-2 sm:flex-row">
            <button type="submit" class="btn-primary justify-center px-6">Simpan Hero Utama</button>
        </div>
    </div>
</form>
@endsection
