@extends('layouts.admin')
@section('title', 'Laporan')

@section('content')
<x-admin.page-header
    eyebrow="Operasional Internal"
    title="Laporan Data Pengajuan"
    description="Filter data pengajuan, ekspor CSV untuk Excel, atau cetak laporan siap PDF."
/>

<form method="GET" action="{{ route('admin.reports.index') }}" class="admin-form-surface mb-6">
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
            <a href="{{ route('admin.reports.index') }}" class="btn-outline">Reset</a>
        @endif
        <button type="submit" class="btn-primary">Terapkan Filter</button>
    </div>
</form>

<div class="admin-page-header__actions mb-6">
    <form method="POST" action="{{ route('admin.reports.excel') }}" data-download-form>
        @csrf
        @foreach (['keyword', 'applicant_type', 'process_status', 'business_category_id', 'date_from', 'date_to'] as $filterName)
            @if (filled($filters[$filterName] ?? null))
                <input type="hidden" name="{{ $filterName }}" value="{{ $filters[$filterName] }}">
            @endif
        @endforeach
        <button type="submit" class="btn-primary">Export Excel (.xlsx)</button>
    </form>
    <a target="_blank" rel="noopener" class="btn-outline" href="{{ route('admin.reports.print', request()->query()) }}">Cetak / Simpan PDF</a>
    {{-- CSV tetap tersedia untuk kebutuhan data mentah (impor ke sistem lain). --}}
    <form method="POST" action="{{ route('admin.reports.csv') }}" data-download-form>
        @csrf
        @foreach (['keyword', 'applicant_type', 'process_status', 'business_category_id', 'date_from', 'date_to'] as $filterName)
            @if (filled($filters[$filterName] ?? null))
                <input type="hidden" name="{{ $filterName }}" value="{{ $filters[$filterName] }}">
            @endif
        @endforeach
        <button type="submit" class="btn-outline">Export CSV (data mentah)</button>
    </form>
</div>

<div class="admin-summary-grid mb-6">
    <div class="admin-summary-card"><span>Total pengajuan</span><strong>{{ $summary['total'] }}</strong></div>
    <div class="admin-summary-card"><span>Sedang berjalan</span><strong>{{ $summary['ongoing'] }}</strong></div>
    <div class="admin-summary-card"><span>Sertifikat terbit</span><strong>{{ $summary['issued'] }}</strong></div>
</div>

@if ($results->isEmpty())
    <div class="admin-empty-inline mb-6">Tidak ada data yang sesuai dengan filter.</div>
@else
    <div class="admin-table-card admin-table-card--responsive overflow-x-auto">
        <table class="admin-responsive-table w-full min-w-180 text-sm">
            <thead>
                <tr>
                    <th class="px-4 py-3 text-left">Pemohon</th>
                    <th class="px-4 py-3 text-left">Kategori</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Tanggal masuk</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($results as $row)
                    <tr class="border-t">
                        <td data-label="Pemohon" class="px-4 py-3 font-semibold">{{ $row->applicantName() }}</td>
                        <td data-label="Kategori" class="px-4 py-3">{{ $row->category?->name ?? '-' }}</td>
                        <td data-label="Status" class="px-4 py-3"><span class="admin-process-badge">{{ $row->process_status }}</span></td>
                        <td data-label="Tanggal masuk" class="px-4 py-3">{{ $row->submitted_at?->translatedFormat('d M Y') ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="my-6">{{ $results->links() }}</div>
@endif

<section class="admin-form-surface">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="admin-form-section__title">Riwayat Export</h2>
            <p class="admin-form-section__description">Maksimal 20 file export terbaru.</p>
        </div>
        @if ($history->isNotEmpty())
            <button
                type="button"
                class="admin-danger-button"
                @click="$dispatch('open-delete-modal',{action:'{{ route('admin.reports.history.clear') }}',name:'seluruh riwayat export'})"
            >Hapus Riwayat</button>
        @endif
    </div>
    <div class="mt-4 grid gap-2">
        @forelse ($history as $item)
            <div class="admin-detail-item flex items-center justify-between gap-3">
                <a download data-no-prefetch class="flex flex-1 items-center justify-between gap-3 transition hover:text-(--admin-maroon)" href="{{ route('admin.reports.download', $item) }}">
                    <span class="font-semibold">{{ $item->generated_at?->translatedFormat('d M Y, H:i') }}</span>
                    <span class="admin-process-badge">{{ strtoupper($item->format) }}</span>
                </a>
                <button
                    type="button"
                    class="admin-danger-button"
                    title="Hapus riwayat ini"
                    @click="$dispatch('open-delete-modal',{action:'{{ route('admin.reports.history.destroy', $item) }}',name:@js('export '.$item->generated_at?->translatedFormat('d M Y, H:i'))})"
                >Hapus</button>
            </div>
        @empty
            <div class="admin-empty-inline">Belum ada export.</div>
        @endforelse
    </div>
</section>
@endsection
