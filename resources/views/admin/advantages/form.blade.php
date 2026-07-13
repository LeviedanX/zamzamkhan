@extends('layouts.admin')
@section('title', $advantage->exists ? 'Edit Keunggulan' : 'Tambah Keunggulan')

@section('content')
<x-admin.page-header
    eyebrow="Konten Website"
    :title="$advantage->exists ? 'Edit Keunggulan' : 'Tambah Keunggulan'"
    description="Kelola alasan utama yang memperkuat kepercayaan calon klien."
/>

<form class="admin-form-shell admin-form-standard" method="POST" action="{{ $advantage->exists ? route('admin.advantages.update', $advantage) : route('admin.advantages.store') }}">
    @csrf
    @if ($advantage->exists) @method('PUT') @endif

    <section class="admin-form-surface">
        <div class="admin-form-section">
            <div>
                <h2 class="admin-form-section__title">Informasi Keunggulan</h2>
                <p class="admin-form-section__description">Judul dan deskripsi akan tampil pada bagian Keunggulan website.</p>
            </div>
            <label class="admin-field">
                <span>Judul <b aria-hidden="true">*</b></span>
                <input name="title" value="{{ old('title', $advantage->title) }}" required maxlength="160">
            </label>
            <div class="admin-field" x-data="{ icon: @js(old('icon', $advantage->icon) ?? '') }">
                <span id="advantage-icon-label">Ikon</span>
                <div class="admin-icon-picker">
                    <span class="admin-icon-picker__preview" aria-hidden="true">
                        @foreach (\App\Models\Advantage::ICONS as $key => $label)
                            <span x-show="icon === '{{ $key }}'" x-cloak>
                                <x-admin.advantage-icon :name="$key" />
                            </span>
                        @endforeach
                        {{-- Belum memilih ikon → tampilkan ikon bawaan (sama dengan yang dirender homepage). --}}
                        <span x-show="! icon" x-cloak>
                            <x-admin.advantage-icon name="default" />
                        </span>
                    </span>
                    <select name="icon" x-model="icon" aria-labelledby="advantage-icon-label">
                        <option value="">Ikon bawaan (Lokasi &amp; Jangkauan)</option>
                        @foreach (\App\Models\Advantage::ICONS as $key => $label)
                            <option value="{{ $key }}" @selected(old('icon', $advantage->icon) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <small>Ikon tampil di kartu Keunggulan pada homepage. Pratinjau di kiri mengikuti pilihanmu.</small>
            </div>
            <label class="admin-field">
                <span>Deskripsi <b aria-hidden="true">*</b></span>
                <textarea name="description" rows="5" required maxlength="1000">{{ old('description', $advantage->description) }}</textarea>
            </label>
        </div>

        <div class="admin-form-section">
            <div class="admin-form-grid admin-form-grid--2">
                <label class="admin-field">
                    <span>Urutan tampil</span>
                    <input type="number" min="1" max="{{ $maxOrder }}" name="display_order" value="{{ old('display_order', $advantage->display_order ?: $maxOrder) }}" required>
                </label>
                <label class="admin-toggle-field">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $advantage->exists ? $advantage->is_active : true))>
                    <span><strong>Tampilkan di website</strong><span>Keunggulan aktif akan muncul di homepage.</span></span>
                </label>
            </div>
        </div>

        <div class="admin-form-actions">
            <a class="btn-outline" href="{{ route('admin.advantages.index') }}">Batal</a>
            <button type="submit" class="btn-primary">Simpan Keunggulan</button>
        </div>
    </section>
</form>
@endsection

