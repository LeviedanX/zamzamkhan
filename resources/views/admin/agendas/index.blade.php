@extends('layouts.admin')
@section('title', 'Agenda')

@section('content')
<x-admin.page-header
    eyebrow="Konten Website"
    title="Agenda Publik"
    description="Kelola agenda aktif dan jadwal kegiatan yang akan ditampilkan pada homepage."
>
    <x-slot:actions>
        <a class="btn-primary" href="{{ route('admin.agendas.create') }}">+ Tambah Agenda</a>
    </x-slot:actions>
</x-admin.page-header>

@if ($agendas->isEmpty())
    @include('admin.partials.empty-state', [
        'title' => 'Belum ada agenda',
        'description' => 'Tambahkan agenda pertama agar jadwal kegiatan dapat ditampilkan pada website.',
        'action' => ['href' => route('admin.agendas.create'), 'label' => 'Tambah Agenda'],
        'icon' => 'M6 3v3M18 3v3M4 9h16M5 5h14v15H5V5Z',
    ])
@else
    <div class="admin-record-grid">
        @foreach ($agendas as $agenda)
            <article class="admin-record-card">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="admin-record-card__title">{{ $agenda->title }}</h2>
                        <p class="admin-record-card__meta">{{ $agenda->starts_at?->translatedFormat('d M Y, H:i') }} · {{ $agenda->venue ?: 'Lokasi belum ditentukan' }}</p>
                    </div>
                    @include('admin.partials.status-badge', ['active' => $agenda->is_active])
                </div>
                @if ($agenda->summary)
                    <p class="admin-record-card__body">{{ $agenda->summary }}</p>
                @endif
                <div class="admin-record-actions">
                    @include('admin.partials.row-actions', [
                        'edit' => route('admin.agendas.edit', $agenda),
                        'delete' => route('admin.agendas.destroy', $agenda),
                        'name' => $agenda->title,
                    ])
                </div>
            </article>
        @endforeach
    </div>
@endif
@endsection

