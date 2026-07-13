@extends('layouts.admin')
@section('title', 'FAQ')

@section('content')
<div class="mb-6 flex items-center justify-between gap-3">
    <div>
        <p class="admin-page-kicker">Bantuan Pengunjung</p>
        <h1 class="mt-2 font-display text-2xl font-bold text-navy-900 sm:text-3xl">FAQ</h1>
        <p class="mt-2 text-sm text-navy-500">Kelola pertanyaan umum yang tampil pada accordion FAQ website.</p>
    </div>
    <a href="{{ route('admin.faqs.create') }}" class="btn-primary !py-2.5">+ Tambah FAQ</a>
</div>

@if ($faqs->isEmpty())
    @include('admin.partials.empty-state', [
        'title' => 'Belum ada FAQ',
        'description' => 'Tambahkan pertanyaan yang sering ditanyakan calon klien agar pengunjung cepat menemukan jawaban.',
        'action' => ['href' => route('admin.faqs.create'), 'label' => 'Tambah FAQ'],
        'icon' => 'M12 18h.01M9.1 9a3 3 0 1 1 5.8 1c0 2-2.9 2.2-2.9 4',
    ])
@else
    <div class="admin-table-card admin-table-card--responsive overflow-x-auto">
        <table class="admin-responsive-table w-full min-w-[640px] text-sm">
            <thead class="bg-navy-50 text-left text-xs uppercase tracking-wide text-navy-500">
                <tr>
                    <th class="px-4 py-3">Urutan</th>
                    <th class="px-4 py-3">Pertanyaan</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-navy-100">
                @foreach ($faqs as $faq)
                    <tr>
                        <td data-label="Urutan" class="px-4 py-3 text-navy-500">{{ $faq->display_order }}</td>
                        <td data-label="Pertanyaan" class="px-4 py-3 font-medium text-navy-900">{{ $faq->question }}</td>
                        <td data-label="Status" class="px-4 py-3">@include('admin.partials.status-badge', ['active' => $faq->is_active])</td>
                        <td data-label="Aksi" class="px-4 py-3">@include('admin.partials.row-actions', ['edit' => route('admin.faqs.edit', $faq), 'delete' => route('admin.faqs.destroy', $faq), 'name' => $faq->question])</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
