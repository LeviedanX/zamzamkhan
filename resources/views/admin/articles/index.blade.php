@extends('layouts.admin')
@section('title', 'Artikel & Insight')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <p class="admin-page-kicker">Konten Edukasi</p>
        <h1 class="mt-2 font-display text-2xl font-bold text-navy-900 sm:text-3xl">Artikel &amp; Insight</h1>
        <p class="mt-2 text-sm text-navy-500">Kelola artikel yang tampil pada homepage dan halaman artikel publik.</p>
    </div>
    <a href="{{ route('admin.articles.create') }}" class="btn-primary !py-2.5">+ Tambah Artikel</a>
</div>

<form method="GET" action="{{ route('admin.articles.index') }}" class="mb-5 flex max-w-xl flex-col gap-2 sm:flex-row sm:items-center">
    <input name="q" value="{{ $q }}" placeholder="Cari judul artikel..."
           class="w-full rounded-xl border border-navy-200 bg-white px-4 py-2.5 text-sm text-navy-900 focus:border-emerald-brand focus:outline-none focus:ring-2 focus:ring-emerald-brand/20">
    <button type="submit" class="btn-outline !py-2.5">Cari</button>
    @if ($q)
        <a href="{{ route('admin.articles.index') }}" class="inline-flex items-center justify-center px-2 text-sm font-semibold text-navy-500 hover:text-emerald-brand">Reset</a>
    @endif
</form>

@if ($articles->isEmpty())
    @include('admin.partials.empty-state', [
        'title' => $q ? 'Artikel tidak ditemukan' : 'Belum ada artikel',
        'description' => $q ? 'Tidak ada artikel dengan judul "'.$q.'".' : 'Tambahkan artikel pertama untuk mengisi halaman artikel dan section insight website.',
        'action' => $q ? null : ['href' => route('admin.articles.create'), 'label' => 'Tambah Artikel'],
        'icon' => 'M5 4h10l4 4v12H5V4Zm10 0v5h5M8 13h8M8 17h6',
    ])
@else
    <div class="admin-table-card admin-table-card--responsive overflow-x-auto">
        <table class="admin-responsive-table w-full min-w-[720px] text-left text-sm">
            <thead>
                <tr class="border-b border-navy-100 text-xs uppercase tracking-wide text-navy-400">
                    <th class="px-4 py-3 font-semibold">Artikel</th>
                    <th class="px-4 py-3 font-semibold">Kategori</th>
                    <th class="px-4 py-3 font-semibold">Status</th>
                    <th class="px-4 py-3 font-semibold">Publikasi</th>
                    <th class="px-4 py-3 font-semibold">Diperbarui</th>
                    <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-navy-100">
                @foreach ($articles as $a)
                    <tr>
                        <td data-label="Artikel" class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="h-12 w-20 flex-none overflow-hidden rounded-lg border border-navy-100 bg-navy-950">
                                    @if ($a->cover_image)
                                        <img src="{{ asset('storage/'.$a->cover_image) }}" alt="{{ $a->cover_alt ?: $a->title }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="admin-article-cover-fallback flex h-full w-full items-center justify-center text-[10px] font-bold tracking-widest text-white/70">ZZK</div>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-navy-900">{{ $a->title }}</p>
                                    <p class="truncate text-xs text-navy-400">/artikel/{{ $a->slug }}</p>
                                </div>
                            </div>
                        </td>
                        <td data-label="Kategori" class="px-4 py-3 text-navy-600">{{ $a->category?->name ?? '-' }}</td>
                        <td data-label="Status" class="px-4 py-3">
                            @if ($a->status === 'published')
                                <span class="admin-status-badge admin-status-badge--active"><span></span>Terbit</span>
                            @else
                                <span class="admin-status-badge admin-status-badge--inactive"><span></span>Draft</span>
                            @endif
                        </td>
                        <td data-label="Publikasi" class="px-4 py-3 text-navy-600">{{ $a->published_at?->translatedFormat('d M Y') ?? '-' }}</td>
                        <td data-label="Diperbarui" class="px-4 py-3 text-navy-500">{{ $a->updated_at?->translatedFormat('d M Y') }}</td>
                        <td data-label="Aksi" class="px-4 py-3">
                            @include('admin.partials.row-actions', ['edit' => route('admin.articles.edit', $a), 'delete' => route('admin.articles.destroy', $a), 'name' => $a->title])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $articles->links() }}</div>
@endif
@endsection
