@extends('layouts.admin')
@section('title', 'Data Pengajuan')

@section('content')
<x-admin.page-header
    eyebrow="Operasional Internal"
    title="Data Pengajuan"
    description="Kelola data pemohon, progres proses, kategori bisnis, dan tanggal penerbitan sertifikat."
>
    <x-slot:actions>
        <a class="btn-primary" href="{{ route('admin.applications.create') }}">+ Tambah Pengajuan</a>
    </x-slot:actions>
</x-admin.page-header>

<div class="admin-summary-grid mb-6">
    <div class="admin-summary-card"><span>Total pengajuan</span><strong>{{ $summary['total'] }}</strong></div>
    <div class="admin-summary-card"><span>Sedang berjalan</span><strong>{{ $summary['ongoing'] }}</strong></div>
    <div class="admin-summary-card"><span>Sertifikat terbit</span><strong>{{ $summary['issued'] }}</strong></div>
</div>

<form method="GET" action="{{ route('admin.applications.index') }}" class="admin-form-surface mb-6">
    <div class="admin-filter-grid">
        <label class="admin-field">
            <span>Kata kunci</span>
            <input name="keyword" value="{{ $filters['keyword'] ?? '' }}" placeholder="Nama, merek, atau no. daftar">
        </label>
        <label class="admin-field">
            <span>Jenis pemohon</span>
            <select name="applicant_type">
                <option value="">Semua jenis</option>
                <option value="company" @selected(($filters['applicant_type'] ?? '') === 'company')>Badan Usaha</option>
                <option value="individual" @selected(($filters['applicant_type'] ?? '') === 'individual')>Perorangan / UMKM</option>
            </select>
        </label>
        <label class="admin-field">
            <span>Status proses</span>
            <select name="process_status">
                <option value="">Semua status</option>
                @foreach (\App\Models\BusinessApplication::STATUSES as $status)
                    <option value="{{ $status }}" @selected(($filters['process_status'] ?? '') === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </label>
        <label class="admin-field">
            <span>Kategori bisnis</span>
            <select name="business_category_id">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(($filters['business_category_id'] ?? null) == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="admin-field">
            <span>Tanggal masuk dari</span>
            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
        </label>
        <label class="admin-field">
            <span>Tanggal masuk sampai</span>
            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
        </label>
    </div>
    <div class="admin-form-actions mt-4">
        @if (array_filter($filters))
            <a href="{{ route('admin.applications.index') }}" class="btn-outline">Reset</a>
        @endif
        <button type="submit" class="btn-primary">Terapkan Filter</button>
    </div>
</form>

@if ($applications->isEmpty())
    <div class="admin-empty-inline">Tidak ada pengajuan yang sesuai dengan filter.</div>
@else
    <div class="admin-table-card admin-table-card--responsive overflow-x-auto">
        <table class="admin-responsive-table w-full min-w-[820px] text-sm">
            <thead>
                <tr>
                    <th class="px-4 py-3 text-left">Pemohon</th>
                    <th class="px-4 py-3 text-left">Jenis</th>
                    <th class="px-4 py-3 text-left">Kategori</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Tanggal masuk</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($applications as $item)
                    <tr class="border-t">
                        <td data-label="Pemohon" class="px-4 py-3">
                            <strong class="block text-[var(--admin-ink)]">{{ $item->applicantName() }}</strong>
                            <span class="text-xs text-[var(--admin-muted)]">{{ $item->brand_name ?: 'Merek belum diisi' }}</span>
                        </td>
                        <td data-label="Jenis" class="px-4 py-3">{{ $item->applicantTypeLabel() }}</td>
                        <td data-label="Kategori" class="px-4 py-3">{{ $item->category?->name ?? '-' }}</td>
                        <td data-label="Status" class="px-4 py-3"><span class="admin-process-badge">{{ $item->process_status }}</span></td>
                        <td data-label="Tanggal masuk" class="px-4 py-3">{{ $item->submitted_at?->translatedFormat('d M Y') ?? '-' }}</td>
                        <td data-label="Aksi" class="px-4 py-3 text-right"><a class="btn-outline" href="{{ route('admin.applications.show', $item) }}">Detail</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $applications->links() }}</div>
@endif
@endsection
