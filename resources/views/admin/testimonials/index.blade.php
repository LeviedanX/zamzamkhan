@extends('layouts.admin')
@section('title', 'Testimoni')

@section('content')
<x-admin.page-header
    eyebrow="Konten Website"
    title="Testimoni"
    description="Kelola pengalaman klien yang ditampilkan sebagai bukti sosial pada website."
>
    <x-slot:actions>
        <a class="btn-primary" href="{{ route('admin.testimonials.create') }}">+ Tambah Testimoni</a>
    </x-slot:actions>
</x-admin.page-header>

@if ($testimonials->isEmpty())
    @include('admin.partials.empty-state', [
        'title' => 'Belum ada testimoni',
        'description' => 'Tambahkan testimoni pertama untuk memperkuat kepercayaan calon klien.',
        'action' => ['href' => route('admin.testimonials.create'), 'label' => 'Tambah Testimoni'],
        'icon' => 'M5 6h14v10H9l-4 3V6Z',
    ])
@else
    <div class="admin-record-grid">
        @foreach ($testimonials as $item)
            <article class="admin-record-card">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="admin-record-card__title">{{ $item->client_name }}</h2>
                        <p class="admin-record-card__meta">{{ $item->service_name ?: 'Layanan belum diisi' }} · urutan {{ $item->display_order }}</p>
                    </div>
                    @include('admin.partials.status-badge', ['active' => $item->is_active])
                </div>
                <p class="admin-record-card__body">“{{ $item->content }}”</p>
                <div class="admin-record-actions">
                    @include('admin.partials.row-actions', [
                        'edit' => route('admin.testimonials.edit', $item),
                        'delete' => route('admin.testimonials.destroy', $item),
                        'name' => $item->client_name,
                    ])
                </div>
            </article>
        @endforeach
    </div>
@endif
@endsection

