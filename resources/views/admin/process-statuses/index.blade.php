@extends('layouts.admin')
@section('title', 'Status Proses')

@section('content')
<x-admin.page-header
    eyebrow="Operasional Internal"
    title="Status Proses Pengajuan"
    description="Kelola pilihan status yang tersedia pada Tambah dan Edit Pengajuan. Menu ini terpisah dari Data Pengajuan."
/>

<section class="admin-form-surface mb-6">
    <div class="admin-form-section">
        <div>
            <h2 class="admin-form-section__title">Tambah Jenis Status</h2>
            <p class="admin-form-section__description">Kategori status menjaga perhitungan pengajuan berjalan dan sertifikat terbit tetap akurat.</p>
        </div>
        <form method="POST" action="{{ route('admin.process-statuses.store') }}">
            @csrf
            <div class="admin-form-grid admin-form-grid--3">
                <label class="admin-field">
                    <span>Nama status <b aria-hidden="true">*</b></span>
                    <input name="name" value="{{ old('name') }}" required maxlength="50" placeholder="Contoh: Verifikasi Dokumen">
                </label>
                <label class="admin-field">
                    <span>Kategori perhitungan <b aria-hidden="true">*</b></span>
                    <select name="type" required>
                        @foreach (\App\Models\BusinessProcessStatus::TYPES as $value => $label)
                            <option value="{{ $value }}" @selected(old('type', 'ongoing') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="admin-field">
                    <span>Urutan tampil <b aria-hidden="true">*</b></span>
                    <input type="number" name="display_order" value="{{ old('display_order', ($statuses->max('display_order') ?? 0) + 1) }}" min="1" max="999" required>
                </label>
            </div>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <label class="admin-toggle-field">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', true))>
                    <span><strong>Status aktif</strong><span>Tersedia pada pengajuan baru.</span></span>
                </label>
                <label class="admin-toggle-field">
                    <input type="hidden" name="is_default" value="0">
                    <input type="checkbox" name="is_default" value="1" @checked((bool) old('is_default'))>
                    <span><strong>Jadikan default</strong><span>Terpilih otomatis saat menambah pengajuan.</span></span>
                </label>
            </div>
            <div class="admin-form-actions mt-4">
                <button type="submit" class="btn-primary">Tambah Status</button>
            </div>
        </form>
    </div>
</section>

@if ($statuses->isEmpty())
    <div class="admin-empty-inline">Belum ada jenis status proses.</div>
@else
    <div class="admin-record-grid">
        @foreach ($statuses as $status)
            @php($usageCount = $status->usageCount())
            @php($historyCount = $status->new_status_histories_count + $status->old_status_histories_count)
            <form method="POST" action="{{ route('admin.process-statuses.update', $status) }}" class="admin-record-card">
                @csrf
                @method('PUT')
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="text-xs font-bold uppercase tracking-wide text-[var(--admin-muted)]">Urutan {{ $status->display_order }}</span>
                    @if ($status->is_default)
                        <span class="admin-status-badge admin-status-badge--active"><span></span>Default</span>
                    @elseif (! $status->is_active)
                        <span class="admin-status-badge admin-status-badge--inactive"><span></span>Nonaktif</span>
                    @endif
                </div>

                <label class="admin-field mt-4">
                    <span>Nama status</span>
                    <input name="name" value="{{ $status->name }}" required maxlength="50" @readonly($usageCount > 0)>
                    @if ($usageCount > 0)
                        <small>Nama dikunci karena sudah dipakai. Status tetap dapat dinonaktifkan.</small>
                    @endif
                </label>
                <div class="admin-form-grid admin-form-grid--2 mt-4">
                    <label class="admin-field">
                        <span>Kategori perhitungan</span>
                        <select name="type" required>
                            @foreach (\App\Models\BusinessProcessStatus::TYPES as $value => $label)
                                <option value="{{ $value }}" @selected($status->type === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="admin-field">
                        <span>Urutan tampil</span>
                        <input type="number" name="display_order" value="{{ $status->display_order }}" min="1" max="999" required>
                    </label>
                </div>

                <p class="admin-record-card__meta">{{ $status->applications_count }} pengajuan dan {{ $historyCount }} catatan riwayat menggunakan status ini.</p>

                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <label class="admin-toggle-field">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked($status->is_active) @disabled($status->is_default)>
                        @if ($status->is_default)<input type="hidden" name="is_active" value="1">@endif
                        <span><strong>Status aktif</strong><span>Tersedia pada pengajuan baru.</span></span>
                    </label>
                    <label class="admin-toggle-field">
                        <input type="hidden" name="is_default" value="0">
                        <input type="checkbox" name="is_default" value="1" @checked($status->is_default) @disabled($status->is_default)>
                        @if ($status->is_default)<input type="hidden" name="is_default" value="1">@endif
                        <span><strong>Status default</strong><span>Default hanya boleh satu.</span></span>
                    </label>
                </div>

                <div class="admin-record-actions">
                    <button type="submit" class="btn-outline">Simpan</button>
                    <button
                        type="button"
                        class="admin-danger-button"
                        @disabled($usageCount > 0 || $status->is_default)
                        title="{{ $status->is_default ? 'Status default tidak dapat dihapus.' : ($usageCount > 0 ? 'Status masih digunakan dan hanya dapat dinonaktifkan.' : 'Hapus status') }}"
                        @if ($usageCount === 0 && ! $status->is_default)
                            @click="$dispatch('open-delete-modal',{action:'{{ route('admin.process-statuses.destroy', $status) }}',name:@js($status->name)})"
                        @endif
                    >Hapus</button>
                </div>
            </form>
        @endforeach
    </div>
@endif
@endsection
