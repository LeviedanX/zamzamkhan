@extends('layouts.admin')
@section('title', $client->exists ? 'Edit Klien' : 'Tambah Klien')

@php
    $clientLogoUrl = $client->exists ? \App\Support\PublicMedia::previewUrl($client->logo_path) : null;
@endphp

@section('content')
<x-admin.page-header
    eyebrow="Konten Website"
    :title="$client->exists ? 'Edit Klien' : 'Tambah Klien'"
    description="Lengkapi identitas dan logo klien yang akan ditampilkan pada website."
/>

<form class="admin-form-shell admin-form-standard" enctype="multipart/form-data" method="POST" action="{{ $client->exists ? route('admin.clients.update', $client) : route('admin.clients.store') }}">
    @csrf
    @if ($client->exists) @method('PUT') @endif

    <section class="admin-form-surface">
        <div class="admin-form-section">
            <div>
                <h2 class="admin-form-section__title">Identitas Klien</h2>
                <p class="admin-form-section__description">Gunakan nama resmi dan informasi singkat yang mudah dikenali.</p>
            </div>
            <div class="admin-form-grid admin-form-grid--2">
                <label class="admin-field">
                    <span>Nama klien <b aria-hidden="true">*</b></span>
                    <input name="name" value="{{ old('name', $client->name) }}" required maxlength="160">
                </label>
                <label class="admin-field">
                    <span>Industri</span>
                    <input name="industry" value="{{ old('industry', $client->industry) }}" maxlength="100">
                </label>
            </div>
            <label class="admin-field">
                <span>Website</span>
                <input type="url" name="website_url" value="{{ old('website_url', $client->website_url) }}" placeholder="https://contoh.com">
            </label>
            <label class="admin-field">
                <span>Logo {{ $client->exists ? '(opsional)' : '*' }}</span>
                @if ($clientLogoUrl)
                    <span class="mb-1 text-xs font-semibold text-navy-500">Logo saat ini:</span>
                    <img src="{{ $clientLogoUrl }}" alt="Logo {{ $client->name }} saat ini" class="mb-1 h-16 w-16 rounded-xl border border-navy-100 bg-white object-contain p-1">
                @endif
                <input type="file" name="logo" accept="image/jpeg,image/png,image/webp" @required(!$client->exists)>
                <small>JPG, PNG, atau WEBP maksimal 2 MB. Gunakan gambar dengan latar transparan jika tersedia.</small>
            </label>
        </div>

        <div class="admin-form-section">
            <div class="admin-form-grid admin-form-grid--2">
                <label class="admin-field">
                    <span>Urutan tampil</span>
                    <input type="number" min="1" max="{{ $maxOrder }}" name="display_order" value="{{ old('display_order', $client->display_order ?: $maxOrder) }}" required>
                </label>
                <label class="admin-toggle-field">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $client->exists ? $client->is_active : true))>
                    <span><strong>Tampilkan di website</strong><span>Klien aktif akan muncul pada bagian bukti sosial.</span></span>
                </label>
            </div>
        </div>

        <div class="admin-form-actions">
            <a class="btn-outline" href="{{ route('admin.clients.index') }}">Batal</a>
            <button type="submit" class="btn-primary">Simpan Klien</button>
        </div>
    </section>
</form>
@endsection

