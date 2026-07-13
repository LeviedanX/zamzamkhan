@extends('layouts.admin')
@php($article = $type === 'article')
@php($base = $article ? 'article-categories' : 'business-categories')
@section('title', $article ? 'Kategori Artikel' : 'Kategori Bisnis')

@section('content')
<x-admin.page-header
    :eyebrow="$article ? 'Konten Website' : 'Operasional Internal'"
    :title="$article ? 'Kategori Artikel' : 'Kategori Bisnis'"
    :description="$article ? 'Kelola klasifikasi artikel agar konten lebih mudah ditemukan.' : 'Kelola kategori bisnis yang digunakan pada data pengajuan.'"
/>

<section class="admin-form-surface mb-6">
    <form method="POST" action="{{ route('admin.'.$base.'.store') }}" class="admin-form-grid sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end">
        @csrf
        <label class="admin-field">
            <span>Nama kategori baru</span>
            <input name="name" value="{{ old('name') }}" required maxlength="{{ $article ? 120 : 255 }}" placeholder="Masukkan nama kategori">
        </label>
        <button type="submit" class="btn-primary">Tambah Kategori</button>
    </form>
</section>

@if ($items->isEmpty())
    <div class="admin-empty-inline">Belum ada kategori.</div>
@else
    <div class="admin-record-grid">
        @foreach ($items as $item)
            @php($usedCount = $item->articles_count ?? $item->applications_count ?? 0)
            <form method="POST" action="{{ route('admin.'.$base.'.update', $item) }}" class="admin-record-card">
                @csrf
                @method('PUT')
                <label class="admin-field">
                    <span>Nama kategori</span>
                    <input name="name" value="{{ $item->name }}" required maxlength="{{ $article ? 120 : 255 }}">
                </label>

                <p class="admin-record-card__meta">{{ $usedCount }} data menggunakan kategori ini.</p>

                @unless ($article)
                    <label class="admin-toggle-field mt-4">
                        <input type="checkbox" name="is_active" value="1" @checked($item->is_active)>
                        <span><strong>Kategori aktif</strong><span>Tersedia saat mengelola data pengajuan.</span></span>
                    </label>
                @endunless

                <div class="admin-record-actions">
                    <button type="submit" class="btn-outline">Simpan</button>
                    <button
                        type="button"
                        class="admin-danger-button"
                        @disabled($usedCount > 0)
                        title="{{ $usedCount > 0 ? 'Kategori masih digunakan dan tidak dapat dihapus.' : 'Hapus kategori' }}"
                        @if ($usedCount === 0)
                            @click="$dispatch('open-delete-modal',{action:'{{ route('admin.'.$base.'.destroy', $item) }}',name:@js($item->name)})"
                        @endif
                    >Hapus</button>
                </div>
            </form>
        @endforeach
    </div>
@endif
@endsection

