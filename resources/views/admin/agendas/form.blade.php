@extends('layouts.admin')
@section('title', $agenda->exists ? 'Edit Agenda' : 'Tambah Agenda')

@section('content')
<x-admin.page-header
    eyebrow="Konten Website"
    :title="$agenda->exists ? 'Edit Agenda' : 'Tambah Agenda'"
    description="Atur informasi kegiatan, jadwal, lokasi, dan status publikasi agenda."
/>

<form class="admin-form-shell admin-form-standard" enctype="multipart/form-data" method="POST" action="{{ $agenda->exists ? route('admin.agendas.update', $agenda) : route('admin.agendas.store') }}">
    @csrf
    @if ($agenda->exists) @method('PUT') @endif

    <section class="admin-form-surface">
        <div class="admin-form-section">
            <div>
                <h2 class="admin-form-section__title">Informasi Agenda</h2>
                <p class="admin-form-section__description">Judul dan ringkasan menjadi informasi utama yang dilihat pengunjung.</p>
            </div>
            <label class="admin-field">
                <span>Judul <b aria-hidden="true">*</b></span>
                <input name="title" value="{{ old('title', $agenda->title) }}" required maxlength="255">
            </label>
            <label class="admin-field">
                <span>Ringkasan</span>
                <textarea name="summary" rows="3" maxlength="500">{{ old('summary', $agenda->summary) }}</textarea>
            </label>
        </div>

        <div class="admin-form-section">
            <div>
                <h2 class="admin-form-section__title">Jadwal dan Lokasi</h2>
                <p class="admin-form-section__description">Tanggal selesai tidak boleh lebih awal dari tanggal mulai.</p>
            </div>
            <label class="admin-field">
                <span>Lokasi</span>
                <input name="venue" value="{{ old('venue', $agenda->venue) }}" maxlength="255">
            </label>
            @php
                // Batas bawah kalender. Agenda yang sudah berjalan tetap boleh disunting,
                // jadi jangan paksa waktu mulainya maju ke "sekarang".
                $now = now()->format('Y-m-d\TH:i');
                $startValue = old('starts_at', $agenda->starts_at?->format('Y-m-d\TH:i'));
                $startMin = $agenda->exists && $agenda->starts_at && $agenda->starts_at->isPast()
                    ? $agenda->starts_at->format('Y-m-d\TH:i')
                    : $now;
            @endphp
            <div class="admin-form-grid admin-form-grid--2"
                 x-data="{ startsAt: @js($startValue ?? ''), min: @js($startMin) }">
                <label class="admin-field">
                    <span>Mulai <b aria-hidden="true">*</b></span>
                    <input type="datetime-local" name="starts_at" x-model="startsAt" :min="min" required>
                    <small>Tidak boleh dijadwalkan ke waktu yang sudah lewat.</small>
                </label>
                <label class="admin-field">
                    <span>Selesai <b aria-hidden="true">*</b></span>
                    <input type="datetime-local" name="ends_at" value="{{ old('ends_at', $agenda->ends_at?->format('Y-m-d\TH:i')) }}" :min="startsAt || min" required>
                    <small>Harus lebih besar dari waktu mulai. Agenda dihapus otomatis setelah waktu ini lewat.</small>
                </label>
            </div>
            <label class="admin-field">
                <span>URL pendaftaran</span>
                <input type="url" name="registration_url" value="{{ old('registration_url', $agenda->registration_url) }}" placeholder="https://">
            </label>
            <label class="admin-field">
                <span>Gambar</span>
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
                <small>JPG, PNG, atau WEBP maksimal 4 MB.</small>
                @if ($agenda->image_path)
                    <label class="mt-3 flex items-center gap-2 text-sm text-red-800">
                        <input type="checkbox" name="remove_image" value="1">
                        Hapus gambar saat menyimpan
                    </label>
                @endif
            </label>
        </div>

        <div class="admin-form-section">
            <div class="admin-form-grid admin-form-grid--2">
                <label class="admin-field">
                    <span>Urutan tampil</span>
                    <input type="number" min="1" max="{{ $maxOrder }}" name="display_order" value="{{ old('display_order', $agenda->display_order ?: $maxOrder) }}" required>
                </label>
                <label class="admin-toggle-field">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $agenda->exists ? $agenda->is_active : true))>
                    <span><strong>Tampilkan di website</strong><span>Agenda aktif dan belum lewat akan tampil pada homepage.</span></span>
                </label>
            </div>
        </div>

        <div class="admin-form-actions">
            <a class="btn-outline" href="{{ route('admin.agendas.index') }}">Batal</a>
            <button type="submit" class="btn-primary">Simpan Agenda</button>
        </div>
    </section>
</form>
@endsection
