
@extends('layouts.admin')
@section('title', $article->exists ? 'Edit Artikel' : 'Tambah Artikel')

@php($inp = 'w-full rounded-xl border border-navy-200 bg-white/95 px-4 py-3 text-sm text-navy-900 shadow-sm shadow-navy-900/5 focus:border-emerald-brand focus:outline-none focus:ring-2 focus:ring-emerald-brand/20')
@php($lbl = 'mb-1.5 block text-sm font-semibold text-navy-800')
@php($articleCoverUrl = \App\Support\PublicMedia::previewUrl($article->cover_image))
@php($articleOgImageUrl = \App\Support\PublicMedia::previewUrl($article->og_image_path))

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
                    @if ($articleCoverUrl)
                        <div class="mb-2">
                            <p class="mb-1 text-xs font-semibold text-navy-500">Cover saat ini:</p>
                            <img src="{{ $articleCoverUrl }}" alt="Cover artikel saat ini" class="aspect-video w-full max-w-xs rounded-xl border border-navy-100 object-cover">
                        </div>
                    @endif
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
                <div>
                    <h2 class="font-display text-sm font-bold uppercase tracking-wide text-navy-500">SEO Artikel</h2>
                    <p class="mt-1 text-xs leading-relaxed text-navy-400">Pengaturan ini hanya berlaku untuk artikel ini. Field kosong memakai judul, ringkasan, dan cover artikel.</p>
                </div>
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
                <div>
                    <label class="{{ $lbl }}">Canonical URL</label>
                    <input type="url" name="canonical_url" value="{{ old('canonical_url', $article->canonical_url) }}" maxlength="2048" class="{{ $inp }}" placeholder="otomatis memakai URL artikel">
                    <p class="mt-1 text-xs text-navy-400">Biarkan kosong kecuali artikel ini merupakan salinan dari URL utama lain.</p>
                </div>
                <div>
                    <label class="{{ $lbl }}">Instruksi mesin pencari</label>
                    <select name="seo_robots" required class="{{ $inp }}">
                        @foreach (['index, follow' => 'Index & ikuti tautan', 'noindex, follow' => 'Jangan index, tetap ikuti tautan', 'noindex, nofollow' => 'Jangan index dan jangan ikuti tautan'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('seo_robots', $article->seo_robots ?: 'index, follow') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <label class="flex items-start gap-2 text-sm text-navy-700">
                    <input type="hidden" name="exclude_from_sitemap" value="0">
                    <input type="checkbox" name="exclude_from_sitemap" value="1" class="mt-1" @checked((bool) old('exclude_from_sitemap', $article->exclude_from_sitemap))>
                    <span>Keluarkan artikel ini dari sitemap XML</span>
                </label>

                <div class="border-t border-navy-100 pt-4">
                    <h3 class="text-sm font-bold text-navy-700">Tampilan saat dibagikan</h3>
                </div>
                <div>
                    <label class="{{ $lbl }}">Judul sosial / Open Graph</label>
                    <input name="og_title" value="{{ old('og_title', $article->og_title) }}" maxlength="120" class="{{ $inp }}">
                    <p class="mt-1 text-xs text-navy-400">Kosong = pakai meta title.</p>
                </div>
                <div>
                    <label class="{{ $lbl }}">Deskripsi sosial / Open Graph</label>
                    <textarea name="og_description" rows="3" maxlength="200" class="{{ $inp }}">{{ old('og_description', $article->og_description) }}</textarea>
                    <p class="mt-1 text-xs text-navy-400">Kosong = pakai meta description.</p>
                </div>
                <div>
                    <label class="{{ $lbl }}">Gambar sosial</label>
                    @if ($articleOgImageUrl)
                        <img src="{{ $articleOgImageUrl }}" alt="Gambar sosial artikel saat ini" class="mb-2 aspect-[1.91/1] w-full rounded-xl border border-navy-100 object-cover">
                    @endif
                    <input type="file" name="og_image" accept="image/jpeg,image/png,image/webp" class="{{ $inp }}">
                    <p class="mt-1 text-xs text-navy-400">Disarankan 1200 × 630 px, JPG/PNG/WEBP, maks 3 MB. Kosong = pakai cover.</p>
                    @if ($article->og_image_path)
                        <label class="mt-3 flex items-center gap-2 text-sm text-red-800">
                            <input type="checkbox" name="remove_og_image" value="1">
                            Hapus gambar sosial saat menyimpan
                        </label>
                    @endif
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
