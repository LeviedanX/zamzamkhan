@extends('layouts.admin')
@section('title', 'Layanan')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <p class="admin-page-kicker">Konten Website</p>
        <h1 class="mt-2 font-display text-2xl font-bold text-navy-900 sm:text-3xl">Layanan</h1>
        <p class="mt-2 text-sm text-navy-500">Atur layanan aktif yang tampil di section Layanan website publik.</p>
    </div>
    <a href="{{ route('admin.services.create') }}" class="btn-primary !py-2.5">+ Tambah Layanan</a>
</div>

@if ($services->isEmpty())
    @include('admin.partials.empty-state', [
        'title' => 'Belum ada layanan',
        'description' => 'Tambahkan layanan agar section Layanan di website memiliki konten dari CMS.',
        'action' => ['href' => route('admin.services.create'), 'label' => 'Tambah Layanan'],
        'icon' => 'M9 6h6M9 12h6M9 18h6M5 6h.01M5 12h.01M5 18h.01M19 6h.01M19 12h.01M19 18h.01',
    ])
@else
<div class="admin-table-card admin-table-card--responsive overflow-x-auto">
    <table class="admin-responsive-table w-full min-w-[640px] text-sm">
        <thead class="bg-navy-50 text-left text-xs uppercase tracking-wide text-navy-500">
            <tr>
                <th class="px-4 py-3">Urutan</th>
                <th class="px-4 py-3">Judul</th>
                <th class="px-4 py-3">Ikon</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-navy-100">
            @forelse ($services as $service)
                <tr>
                    <td data-label="Urutan" class="px-4 py-3 text-navy-500">{{ $service->display_order }}</td>
                    <td data-label="Judul" class="px-4 py-3 font-medium text-navy-900">{{ $service->title }}</td>
                    <td data-label="Ikon" class="px-4 py-3 text-navy-500">{{ $service->icon }}</td>
                    <td data-label="Status" class="px-4 py-3">
                        @include('admin.partials.status-badge', ['active' => $service->is_active])
                    </td>
                    <td data-label="Aksi" class="px-4 py-3">
                        @include('admin.partials.row-actions', ['edit' => route('admin.services.edit', $service), 'delete' => route('admin.services.destroy', $service), 'name' => $service->title])
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-10 text-center text-navy-500">Belum ada layanan. Tambahkan layanan agar section Layanan di website memiliki konten dari CMS.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endif
@endsection
