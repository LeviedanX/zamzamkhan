
@extends('layouts.admin')
@section('title', $service->exists ? 'Edit Layanan' : 'Tambah Layanan')

@php
    $inp = 'w-full rounded-xl border border-navy-200 bg-white/95 px-4 py-3 text-sm text-navy-900 shadow-sm shadow-navy-900/5 focus:border-emerald-brand focus:outline-none focus:ring-2 focus:ring-emerald-brand/20';
    $lbl = 'mb-1.5 block text-sm font-semibold text-navy-800';
    $help = 'mt-1 text-xs leading-relaxed text-navy-400';
    $benefitItems = collect(preg_split('/\r\n|\r|\n/', old('benefits', $service->benefits) ?: ''))->map(fn ($x) => trim($x))->filter()->values();
    $workflowItems = collect(preg_split('/\r\n|\r|\n/', old('workflow_steps', $service->workflow_steps) ?: ''))->map(fn ($x) => trim($x))->filter()->values();
@endphp

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.services.index') }}" class="admin-back-link">Kembali</a>
    <p class="admin-page-kicker mt-4">Modul Layanan</p>
    <h1 class="mt-2 font-display text-2xl font-bold text-navy-900 sm:text-3xl">{{ $service->exists ? 'Edit' : 'Tambah' }} Layanan</h1>
    <p class="mt-2 max-w-2xl text-sm leading-relaxed text-navy-500">Kelola seluruh konten layanan yang tampil di card, modal detail, dan tombol konsultasi website publik.</p>
</div>

<form method="POST" action="{{ $service->exists ? route('admin.services.update', $service) : route('admin.services.store') }}" class="admin-form-shell"
      x-data="{
          title: @js(old('title', $service->title)),
          icon: @js(old('icon', $service->icon ?: 'halal')),
          summary: @js(old('summary', $service->summary)),
          description: @js(old('description', $service->description)),
          suitableFor: @js(old('suitable_for', $service->suitable_for)),
          whatsappMessage: @js(old('whatsapp_message', $service->whatsapp_message)),
          benefits: @js($benefitItems),
          benefitDraft: '',
          workflowSteps: @js($workflowItems),
          workflowDraft: '',
          isActive: @js((bool) old('is_active', $service->exists ? $service->is_active : true)),
          isFeatured: @js((bool) old('is_featured', $service->is_featured ?? false)),
          iconOptions: ['halal', 'halal-reg', 'nib', 'akta', 'pajak', 'bpom', 'haki', 'desain'],
          cleanList(items) {
              return items.map((item) => (item || '').trim()).filter(Boolean);
          },
          benefitPayload() {
              return this.cleanList(this.benefits).join('\n');
          },
          workflowPayload() {
              return this.cleanList(this.workflowSteps).join('\n');
          },
          addBenefit() {
              const value = this.benefitDraft.trim();
              if (! value) return;
              this.benefits.push(value);
              this.benefitDraft = '';
          },
          removeBenefit(index) {
              this.benefits.splice(index, 1);
          },
          addWorkflow() {
              const value = this.workflowDraft.trim();
              if (! value) return;
              this.workflowSteps.push(value);
              this.workflowDraft = '';
          },
          removeWorkflow(index) {
              this.workflowSteps.splice(index, 1);
          },
      }">
    @csrf
    @if ($service->exists) @method('PUT') @endif

    <section class="admin-form-card space-y-5 rounded-3xl border border-navy-100 bg-white p-5 sm:p-6">
        <header>
            <h2 class="font-display text-lg font-bold text-navy-900">Informasi Card</h2>
            <p class="mt-1 text-sm text-navy-500">Bagian yang terlihat langsung pada section Layanan.</p>
        </header>
        <div>
            <label class="{{ $lbl }}">Judul <span class="text-red-700">*</span></label>
            <input name="title" x-model="title" class="{{ $inp }}" required>
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label class="{{ $lbl }}">Ikon</label>
                <select name="icon" x-model="icon" class="{{ $inp }}">
                    <template x-for="option in iconOptions" :key="option">
                        <option :value="option" x-text="option"></option>
                    </template>
                </select>
                <p class="{{ $help }}">Kode ikon yang tersedia pada website publik.</p>
            </div>
            <div>
                <label class="{{ $lbl }}">Urutan tampil</label>
                <input name="display_order" type="number" min="1" max="{{ $maxOrder }}" value="{{ old('display_order', $service->display_order ?: $maxOrder) }}" class="{{ $inp }}" required>
            </div>
        </div>
        <div>
            <label class="{{ $lbl }}">Ringkasan card</label>
            <textarea name="summary" rows="3" x-model="summary" class="{{ $inp }}" placeholder="Ringkasan singkat yang tampil di card layanan."></textarea>
        </div>
    </section>

    <section class="admin-form-card space-y-5 rounded-3xl border border-navy-100 bg-white p-5 sm:p-6">
        <header>
            <h2 class="font-display text-lg font-bold text-navy-900">Detail Layanan</h2>
            <p class="mt-1 text-sm text-navy-500">Bagian yang tampil di modal detail layanan pada website publik.</p>
        </header>
        <div>
            <label class="{{ $lbl }}">Deskripsi detail</label>
            <textarea name="description" rows="5" x-model="description" class="{{ $inp }}" placeholder="Penjelasan lengkap layanan."></textarea>
        </div>
        <div>
            <label class="{{ $lbl }}">Cocok untuk</label>
            <textarea name="suitable_for" rows="3" x-model="suitableFor" class="{{ $inp }}" placeholder="Segmentasi klien atau jenis usaha yang cocok."></textarea>
        </div>
        <div>
            <div class="mb-2 flex items-center justify-between gap-3">
                <label class="{{ $lbl }} !mb-0">Manfaat utama</label>
                <span class="text-xs font-semibold text-navy-400" x-text="`${cleanList(benefits).length} poin`"></span>
            </div>
            <input type="hidden" name="benefits" :value="benefitPayload()">
            <div class="space-y-2">
                <template x-for="(benefit, index) in benefits" :key="index">
                    <div class="flex items-center gap-2 rounded-2xl border border-navy-100 bg-white/70 p-2">
                        <span class="inline-flex h-8 w-8 flex-none items-center justify-center rounded-full bg-red-900 text-xs font-bold text-white" x-text="index + 1"></span>
                        <input type="text" x-model="benefits[index]" class="{{ $inp }} !py-2" :aria-label="`Manfaat ${index + 1}`">
                        <button type="button" class="rounded-xl border border-red-900/20 px-3 py-2 text-xs font-bold text-red-800 hover:bg-red-900/5" @click="removeBenefit(index)">Hapus</button>
                    </div>
                </template>
            </div>
            <div class="mt-3 flex flex-col gap-2 sm:flex-row">
                <input type="text" x-model="benefitDraft" class="{{ $inp }} !py-2" placeholder="Tambah manfaat" @keydown.enter.prevent="addBenefit">
                <button type="button" class="btn-outline shrink-0 !py-2" @click="addBenefit">Tambah Manfaat</button>
            </div>
        </div>
        <div>
            <div class="mb-2 flex items-center justify-between gap-3">
                <label class="{{ $lbl }} !mb-0">Alur singkat</label>
                <span class="text-xs font-semibold text-navy-400" x-text="`${cleanList(workflowSteps).length} tahap`"></span>
            </div>
            <input type="hidden" name="workflow_steps" :value="workflowPayload()">
            <div class="space-y-2">
                <template x-for="(step, index) in workflowSteps" :key="index">
                    <div class="flex items-center gap-2 rounded-2xl border border-navy-100 bg-white/70 p-2">
                        <span class="inline-flex h-8 w-8 flex-none items-center justify-center rounded-full bg-red-900 text-xs font-bold text-white" x-text="index + 1"></span>
                        <input type="text" x-model="workflowSteps[index]" class="{{ $inp }} !py-2" :aria-label="`Alur ${index + 1}`">
                        <button type="button" class="rounded-xl border border-red-900/20 px-3 py-2 text-xs font-bold text-red-800 hover:bg-red-900/5" @click="removeWorkflow(index)">Hapus</button>
                    </div>
                </template>
            </div>
            <div class="mt-3 flex flex-col gap-2 sm:flex-row">
                <input type="text" x-model="workflowDraft" class="{{ $inp }} !py-2" placeholder="Tambah tahap alur" @keydown.enter.prevent="addWorkflow">
                <button type="button" class="btn-outline shrink-0 !py-2" @click="addWorkflow">Tambah Tahap</button>
            </div>
        </div>
        <div>
            <label class="{{ $lbl }}">Kebutuhan awal (prefill konsultasi)</label>
            <textarea name="whatsapp_message" rows="3" x-model="whatsappMessage" class="{{ $inp }}" placeholder="mis. Saya ingin mengurus sertifikat halal untuk usaha makanan ringan."></textarea>
            <p class="{{ $help }}">Bila diisi, teks ini otomatis mengisi kolom "Kebutuhan" pada form konsultasi ketika pengunjung menekan tombol Konsultasikan pada layanan ini. Kosongkan untuk membiarkan pengunjung menulis sendiri.</p>
        </div>
    </section>

    <section class="admin-form-card rounded-3xl border border-navy-100 bg-white p-5 sm:p-6">
        <header class="mb-5">
            <h2 class="font-display text-lg font-bold text-navy-900">Status Publikasi</h2>
            <p class="mt-1 text-sm text-navy-500">Kontrol visibilitas layanan pada website publik.</p>
        </header>
        <div class="grid gap-4 sm:grid-cols-2">
            <label class="admin-toggle-card flex items-start gap-3 rounded-2xl p-4 text-sm text-navy-700">
                <input type="checkbox" name="is_active" value="1" x-model="isActive" class="mt-1 h-4 w-4 rounded border-navy-300 text-red-800">
                <span><span class="block font-bold text-navy-900">Tampilkan di website</span><span class="mt-1 block text-xs text-navy-500">Layanan aktif muncul pada section Layanan.</span></span>
            </label>
            <label class="admin-toggle-card flex items-start gap-3 rounded-2xl p-4 text-sm text-navy-700">
                <input type="checkbox" name="is_featured" value="1" x-model="isFeatured" class="mt-1 h-4 w-4 rounded border-navy-300 text-red-800">
                <span><span class="block font-bold text-navy-900">Unggulan</span><span class="mt-1 block text-xs text-navy-500">Tandai layanan prioritas untuk ditonjolkan.</span></span>
            </label>
        </div>
    </section>

    <div class="admin-savebar flex flex-col gap-3 rounded-3xl border border-navy-100 bg-white/90 p-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm leading-relaxed text-navy-500">Simpan perubahan untuk memperbarui layanan di website publik.</p>
        <div class="flex flex-col gap-2 sm:flex-row">
            <button type="submit" class="btn-primary justify-center px-6">Simpan</button>
            <a href="{{ route('admin.services.index') }}" class="btn-outline justify-center px-6">Batal</a>
        </div>
    </div>
</form>
@endsection

