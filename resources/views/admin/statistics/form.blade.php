@extends('layouts.admin')
@section('title', $statistic->exists ? 'Edit Statistik' : 'Tambah Statistik')

@section('content')
<x-admin.page-header
    eyebrow="Konten Website"
    :title="$statistic->exists ? 'Edit Statistik' : 'Tambah Statistik'"
    description="Kelola angka pencapaian yang ditampilkan sebagai bukti kredibilitas perusahaan."
/>

<form class="admin-form-shell admin-form-standard" method="POST" action="{{ $statistic->exists ? route('admin.statistics.update', $statistic) : route('admin.statistics.store') }}">
    @csrf
    @if ($statistic->exists) @method('PUT') @endif

    <section class="admin-form-surface">
        <div class="admin-form-section">
            <div>
                <h2 class="admin-form-section__title">Informasi Statistik</h2>
                <p class="admin-form-section__description">Gunakan nilai singkat dan mudah dipahami, misalnya 250+ atau 10 Tahun.</p>
            </div>
            <div class="admin-form-grid admin-form-grid--2">
                <label class="admin-field">
                    <span>Nilai <b aria-hidden="true">*</b></span>
                    <input name="value" value="{{ old('value', $statistic->value) }}" required maxlength="40" placeholder="250+">
                </label>
                <label class="admin-field">
                    <span>Label <b aria-hidden="true">*</b></span>
                    <input name="label" value="{{ old('label', $statistic->label) }}" required maxlength="160" placeholder="Klien terlayani">
                </label>
            </div>
            <label class="admin-field">
                <span>Deskripsi</span>
                <input name="description" value="{{ old('description', $statistic->description) }}" maxlength="255">
            </label>
        </div>

        <div class="admin-form-section">
            <div class="admin-form-grid admin-form-grid--2">
                <label class="admin-field">
                    <span>Urutan tampil</span>
                    <input type="number" min="1" max="{{ $maxOrder }}" name="display_order" value="{{ old('display_order', $statistic->display_order ?: $maxOrder) }}" required>
                </label>
                <label class="admin-toggle-field">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $statistic->exists ? $statistic->is_active : true))>
                    <span><strong>Tampilkan di website</strong><span>Statistik aktif akan muncul di homepage.</span></span>
                </label>
            </div>
        </div>

        <div class="admin-form-actions">
            <a class="btn-outline" href="{{ route('admin.statistics.index') }}">Batal</a>
            <button type="submit" class="btn-primary">Simpan Statistik</button>
        </div>
    </section>
</form>
@endsection

