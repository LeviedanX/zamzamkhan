
@extends('layouts.admin')
@section('title', $article->exists ? 'Edit Artikel' : 'Tambah Artikel')

@php($inp = 'w-full rounded-xl border border-navy-200 bg-white/95 px-4 py-3 text-sm text-navy-900 shadow-sm shadow-navy-900/5 focus:border-emerald-brand focus:outline-none focus:ring-2 focus:ring-emerald-brand/20')
@php($lbl = 'mb-1.5 block text-sm font-semibold text-navy-800')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.articles.index') }}" class="admin-back-link">← Kembali</a>
    <h1 class="mt-2 font-display text-2xl font-bold text-navy-900">{{ $article->exists ? 'Edit' : 'Tambah' }} Artikel</h1>
</div>

<form method="POST"
      action="{{ $article->exists ? route('admin.articles.update', $article) : route('admin.articles.store') }}"
      enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($article->exists) @method('PUT') @endif

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Kolom utama --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Konten --}}
            <section class="admin-form-card space-y-5 rounded-3xl border border-navy-100 bg-white p-5 sm:p-6">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-navy-500">Konten</h2>
                <div>
                    <label class="{{ $lbl }}">Judul <span class="text-tosca-500">*</span></label>
                    <input name="title" value="{{ old('title', $article->title) }}" maxlength="180" required class="{{ $inp }}">
                </div>
                <div>
                    <label class="{{ $lbl }}">Slug</label>
                    <input name="slug" value="{{ old('slug', $article->slug) }}" class="{{ $inp }}" placeholder="otomatis dari judul bila dikosongkan">
                    <p class="mt-1 text-xs text-navy-400">Huruf, angka, dan tanda hubung. Dikosongkan = dibuat otomatis dari judul.</p>
                </div>
                <div>
                    <label class="{{ $lbl }}">Kategori <span class="text-tosca-500">*</span></label>
                    <select name="article_category_id" required class="{{ $inp }}">
                        <option value="">— Pilih kategori —</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected((int) old('article_category_id', $article->article_category_id) === $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $lbl }}">Ringkasan (excerpt) <span class="text-tosca-500">*</span></label>
                    <textarea name="excerpt" rows="2" maxlength="350" required class="{{ $inp }}">{{ old('excerpt', $article->excerpt) }}</textarea>
                    <p class="mt-1 text-xs text-navy-400">Maksimal 350 karakter. Tampil di kartu artikel &amp; meta description bila kosong.</p>
                </div>
                <div>
                    <label class="{{ $lbl }}">Isi Artikel <span class="text-tosca-500">*</span></label>
                    <textarea name="content" rows="14" required class="{{ $inp }} font-mono text-[13px] leading-relaxed">{{ old('content', $article->content) }}</textarea>
                    <p class="mt-1 text-xs text-navy-400">Teks biasa. Pisahkan paragraf dengan baris kosong. HTML tidak dirender (aman).</p>
                </div>
            </section>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Publikasi --}}
            <section class="admin-form-card space-y-4 rounded-3xl border border-navy-100 bg-white p-5 sm:p-6">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-navy-500">Publikasi</h2>
                <div>
                    <label class="{{ $lbl }}">Status <span class="text-tosca-500">*</span></label>
                    <select name="status" required class="{{ $inp }}">
                        @foreach (['draft' => 'Draft', 'published' => 'Terbit'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('status', $article->status ?? 'draft') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $lbl }}">Tanggal publikasi</label>
                    <input type="date" name="published_at"
                           value="{{ old('published_at', optional($article->published_at)->format('Y-m-d')) }}" class="{{ $inp }}">
                    <p class="mt-1 text-xs text-navy-400">Kosong = otomatis diisi saat pertama dipublikasikan.</p>
                </div>
            </section>

            {{-- Media --}}
            <section class="admin-form-card space-y-4 rounded-3xl border border-navy-100 bg-white p-5 sm:p-6">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-navy-500">Media / Cover</h2>
                <div>
                    <label class="{{ $lbl }}">Cover (16:9)</label>
                    <input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp" class="{{ $inp }}">
                    <p class="mt-1 text-xs text-navy-400">JPG/PNG/WEBP, maks 3 MB.@if($article->cover_image) Kosongkan bila tak diganti.@endif</p>
                    @if ($article->cover_image)
                        <label class="mt-3 flex items-center gap-2 text-sm text-red-800">
                            <input type="checkbox" name="remove_cover_image" value="1">
                            Hapus cover saat menyimpan
                        </label>
                    @endif
                </div>
                <div>
                    <label class="{{ $lbl }}">Teks alternatif (alt)</label>
                    <input name="cover_alt" value="{{ old('cover_alt', $article->cover_alt) }}" maxlength="180" class="{{ $inp }}">
                </div>
            </section>

            {{-- SEO --}}
            <section class="admin-form-card space-y-4 rounded-3xl border border-navy-100 bg-white p-5 sm:p-6">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-navy-500">SEO</h2>
                <div>
                    <label class="{{ $lbl }}">Meta title</label>
                    <input name="meta_title" value="{{ old('meta_title', $article->meta_title) }}" maxlength="70" class="{{ $inp }}">
                    <p class="mt-1 text-xs text-navy-400">Maks 70 karakter. Kosong = pakai judul.</p>
                </div>
                <div>
                    <label class="{{ $lbl }}">Meta description</label>
                    <textarea name="meta_description" rows="3" maxlength="160" class="{{ $inp }}">{{ old('meta_description', $article->meta_description) }}</textarea>
                    <p class="mt-1 text-xs text-navy-400">Maks 160 karakter. Kosong = pakai ringkasan.</p>
                </div>
            </section>
        </div>
    </div>

    <div class="admin-savebar flex flex-wrap gap-3 rounded-3xl border border-navy-100 bg-white/90 p-4">
        <button type="submit" class="btn-primary">Simpan Artikel</button>
        <a href="{{ route('admin.articles.index') }}" class="btn-outline">Batal</a>
    </div>
</form>
@endsection
