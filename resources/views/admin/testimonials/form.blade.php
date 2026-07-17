@extends('layouts.admin')
@section('title', $testimonial->exists ? 'Edit Testimoni' : 'Tambah Testimoni')

@php
    $testimonialImageUrl = $testimonial->exists ? \App\Support\PublicMedia::previewUrl($testimonial->image_path) : null;
@endphp

@section('content')
<x-admin.page-header
    eyebrow="Konten Website"
    :title="$testimonial->exists ? 'Edit Testimoni' : 'Tambah Testimoni'"
    description="Masukkan testimoni yang autentik, ringkas, dan relevan dengan layanan."
/>

<form class="admin-form-shell admin-form-standard" enctype="multipart/form-data" method="POST" action="{{ $testimonial->exists ? route('admin.testimonials.update', $testimonial) : route('admin.testimonials.store') }}">
    @csrf
    @if ($testimonial->exists) @method('PUT') @endif

    <section class="admin-form-surface">
        <div class="admin-form-section">
            <div>
                <h2 class="admin-form-section__title">Informasi Testimoni</h2>
                <p class="admin-form-section__description">Nama klien dan isi testimoni akan tampil pada website publik.</p>
            </div>
            <div class="admin-form-grid admin-form-grid--2">
                <label class="admin-field">
                    <span>Nama klien <b aria-hidden="true">*</b></span>
                    <input name="client_name" value="{{ old('client_name', $testimonial->client_name) }}" required maxlength="160">
                </label>
                <label class="admin-field">
                    <span>Layanan</span>
                    <input name="service_name" value="{{ old('service_name', $testimonial->service_name) }}" maxlength="160">
                </label>
            </div>
            <label class="admin-field">
                <span>Testimoni <b aria-hidden="true">*</b></span>
                <textarea name="content" rows="6" required maxlength="1500">{{ old('content', $testimonial->content) }}</textarea>
            </label>
            <div class="admin-form-grid admin-form-grid--2">
                <label class="admin-field">
                    <span>Dokumentasi {{ $testimonial->exists ? '(opsional)' : '*' }}</span>
                    @if ($testimonialImageUrl)
                        <span class="mb-1 text-xs font-semibold text-navy-500">Gambar saat ini:</span>
                        <img src="{{ $testimonialImageUrl }}" alt="Dokumentasi {{ $testimonial->client_name }} saat ini" class="mb-1 h-20 w-28 rounded-xl border border-navy-100 object-cover">
                    @endif
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp" @required(!$testimonial->exists)>
                    <small>JPG, PNG, atau WEBP maksimal 4 MB.</small>
                </label>
                <label class="admin-field">
                    <span>Teks alternatif gambar</span>
                    <input name="image_alt" value="{{ old('image_alt', $testimonial->image_alt) }}" maxlength="255">
                </label>
            </div>
        </div>

        <div class="admin-form-section">
            <div class="admin-form-grid admin-form-grid--2">
                <label class="admin-field">
                    <span>Urutan tampil</span>
                    <input type="number" min="1" max="{{ $maxOrder }}" name="display_order" value="{{ old('display_order', $testimonial->display_order ?: $maxOrder) }}" required>
                </label>
                <label class="admin-toggle-field">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $testimonial->exists ? $testimonial->is_active : true))>
                    <span><strong>Tampilkan di website</strong><span>Testimoni aktif akan muncul pada homepage.</span></span>
                </label>
            </div>
        </div>

        <div class="admin-form-actions">
            <a class="btn-outline" href="{{ route('admin.testimonials.index') }}">Batal</a>
            <button type="submit" class="btn-primary">Simpan Testimoni</button>
        </div>
    </section>
</form>
@endsection

