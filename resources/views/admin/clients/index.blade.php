@extends('layouts.admin')
@section('title', 'Klien')

@section('content')
<x-admin.page-header
    eyebrow="Konten Website"
    title="Klien"
    description="Kelola logo dan identitas klien yang menjadi bukti kepercayaan terhadap perusahaan."
>
    <x-slot:actions>
        <a class="btn-primary" href="{{ route('admin.clients.create') }}">+ Tambah Klien</a>
    </x-slot:actions>
</x-admin.page-header>

@if ($clients->isEmpty())
    @include('admin.partials.empty-state', [
        'title' => 'Belum ada klien',
        'description' => 'Tambahkan logo klien pertama untuk membangun bukti sosial pada website.',
        'action' => ['href' => route('admin.clients.create'), 'label' => 'Tambah Klien'],
        'icon' => 'M8 11a3 3 0 1 0 0-6M3 20a5 5 0 0 1 10 0',
    ])
@else
    <div class="admin-record-grid">
        @foreach ($clients as $client)
            <article class="admin-record-card">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex h-16 w-20 items-center justify-center overflow-hidden rounded-xl border border-[var(--admin-border)] bg-white p-2">
                        <img class="max-h-full max-w-full object-contain" src="{{ $client->logoUrl() }}" alt="Logo {{ $client->name }}">
                    </div>
                    @include('admin.partials.status-badge', ['active' => $client->is_active])
                </div>
                <h2 class="admin-record-card__title mt-4">{{ $client->name }}</h2>
                <p class="admin-record-card__meta">{{ $client->industry ?: 'Industri belum diisi' }} · urutan {{ $client->display_order }}</p>
                @if ($client->website_url)
                    <a class="mt-2 truncate text-sm font-semibold text-[var(--admin-maroon)] hover:underline" href="{{ $client->website_url }}" target="_blank" rel="noopener">{{ $client->website_url }}</a>
                @endif
                <div class="admin-record-actions">
                    @include('admin.partials.row-actions', [
                        'edit' => route('admin.clients.edit', $client),
                        'delete' => route('admin.clients.destroy', $client),
                        'name' => $client->name,
                    ])
                </div>
            </article>
        @endforeach
    </div>
@endif
@endsection

