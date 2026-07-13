@extends('layouts.admin')
@section('title', 'Detail Pengajuan')

@section('content')
<x-admin.page-header
    eyebrow="Operasional Internal"
    :title="$application->applicantName()"
    :description="($application->brand_name ?: 'Merek belum diisi').' · '.($application->category?->name ?? 'Tanpa kategori')"
>
    <x-slot:actions>
        <a class="btn-outline" href="{{ route('admin.applications.index') }}">Kembali</a>
        <a class="btn-primary" href="{{ route('admin.applications.edit', $application) }}">Edit Pengajuan</a>
    </x-slot:actions>
</x-admin.page-header>

<section class="admin-form-surface">
    <dl class="admin-detail-grid">
        <div class="admin-detail-item"><dt>Status</dt><dd><span class="admin-process-badge">{{ $application->process_status }}</span></dd></div>
        <div class="admin-detail-item"><dt>Jenis pemohon</dt><dd>{{ $application->applicantTypeLabel() }}</dd></div>
        <div class="admin-detail-item"><dt>Nomor pendaftaran</dt><dd>{{ $application->registration_number ?: '-' }}</dd></div>
        <div class="admin-detail-item"><dt>Tanggal masuk</dt><dd>{{ $application->submitted_at?->translatedFormat('d M Y') ?? '-' }}</dd></div>
        <div class="admin-detail-item"><dt>Tanggal sertifikat</dt><dd>{{ $application->certificate_issued_at?->translatedFormat('d M Y') ?? '-' }}</dd></div>
        <div class="admin-detail-item"><dt>Diperbarui oleh</dt><dd>{{ $application->updater?->name ?? 'Sistem' }}</dd></div>
    </dl>

    <div class="admin-form-section mt-6">
        <div>
            <h2 class="admin-form-section__title">Catatan</h2>
            <p class="mt-3 whitespace-pre-line text-sm leading-7 text-[var(--admin-ink)]">{{ $application->notes ?: 'Belum ada catatan.' }}</p>
        </div>
    </div>

    <div class="admin-form-section">
        <div>
            <h2 class="admin-form-section__title">Riwayat Status</h2>
            <p class="admin-form-section__description">Jejak perubahan status tersimpan bersama waktu dan administrator.</p>
        </div>
        <div class="grid gap-3">
            @forelse ($application->histories as $history)
                <article class="admin-detail-item">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="font-semibold text-[var(--admin-ink)]">{{ $history->old_status ?: 'Status awal' }} → {{ $history->new_status }}</p>
                        <time class="text-xs text-[var(--admin-muted)]">{{ $history->created_at?->translatedFormat('d M Y, H:i') }}</time>
                    </div>
                    <p class="mt-2 text-sm text-[var(--admin-muted)]">{{ $history->admin?->name ?? 'Sistem' }}</p>
                    @if ($history->note)<p class="mt-2 text-sm leading-6 text-[var(--admin-ink)]">{{ $history->note }}</p>@endif
                </article>
            @empty
                <div class="admin-empty-inline">Belum ada riwayat status.</div>
            @endforelse
        </div>
    </div>

    <div class="admin-form-actions">
        <button type="button" class="admin-danger-button" @click="$dispatch('open-delete-modal',{action:'{{ route('admin.applications.destroy', $application) }}',name:@js($application->applicantName())})">Hapus Pengajuan</button>
    </div>
</section>
@endsection

