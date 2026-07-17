
@extends('layouts.admin')
@section('title', 'SEO Website')

@php($inp = 'w-full rounded-xl border border-navy-200 bg-white/95 px-4 py-3 text-sm text-navy-900 shadow-sm shadow-navy-900/5 focus:border-emerald-brand focus:outline-none focus:ring-2 focus:ring-emerald-brand/20')
@php($seoOgImageUrl = \App\Support\PublicMedia::previewUrl($seo->og_image_path))

@section('content')
<div class="mb-6">
    <p class="admin-page-kicker">SEO Website</p>
    <h1 class="mt-2 font-display text-2xl font-bold text-navy-900 sm:text-3xl">SEO Website</h1>
    <p class="mt-2 max-w-2xl text-sm leading-relaxed text-navy-500">Metadata global halaman utama dengan fallback aman ke konfigurasi bawaan.</p>
</div>

<form method="POST" action="{{ route('admin.seo.update') }}" enctype="multipart/form-data" class="admin-form-shell max-w-5xl">
    @csrf @method('PUT')

    <div>
        <div class="space-y-5">
            <section class="admin-form-card space-y-5 rounded-3xl border border-navy-100 bg-white p-5 sm:p-6">
                <header>
                    <h2 class="font-display text-lg font-bold text-navy-900">Meta Dasar</h2>
                    <p class="mt-1 text-sm text-navy-500">Title, description, dan keyword utama website.</p>
                </header>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-navy-800">Meta Title <span class="text-red-700">*</span></label>
                    <input name="meta_title" value="{{ old('meta_title', $seo->meta_title) }}" class="{{ $inp }}" required>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-navy-800">Meta Description</label>
                    <textarea name="meta_description" rows="3" maxlength="255" class="{{ $inp }}">{{ old('meta_description', $seo->meta_description) }}</textarea>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-navy-800">Meta Keywords</label>
                    <input name="meta_keywords" value="{{ old('meta_keywords', $seo->meta_keywords) }}" class="{{ $inp }}">
                </div>
            </section>

            <section class="admin-form-card space-y-5 rounded-3xl border border-navy-100 bg-white p-5 sm:p-6">
                <header>
                    <h2 class="font-display text-lg font-bold text-navy-900">Open Graph & Canonical</h2>
                    <p class="mt-1 text-sm text-navy-500">Metadata saat website dibagikan ke platform lain.</p>
                </header>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-navy-800">OG Title</label>
                        <input name="og_title" value="{{ old('og_title', $seo->og_title) }}" class="{{ $inp }}">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-navy-800">Canonical URL</label>
                        <input name="canonical_url" value="{{ old('canonical_url', $seo->canonical_url) }}" class="{{ $inp }}">
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-navy-800">OG Description</label>
                    <textarea name="og_description" rows="3" class="{{ $inp }}">{{ old('og_description', $seo->og_description) }}</textarea>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-navy-800">OG Image (opsional)</label>
                    @if ($seoOgImageUrl)
                        <div class="mb-2">
                            <p class="mb-1 text-xs font-semibold text-navy-500">Gambar saat ini:</p>
                            <img src="{{ $seoOgImageUrl }}" alt="OG image saat ini" class="aspect-video w-full max-w-xs rounded-xl border border-navy-100 object-cover">
                        </div>
                    @endif
                    <input name="og_image" type="file" accept="image/jpeg,image/png,image/webp" class="{{ $inp }}">
                    <p class="mt-1 text-xs text-navy-400">JPG/PNG/WEBP, maksimal 2 MB.</p>
                    @if ($seo->og_image_path)
                        <label class="mt-3 flex items-center gap-2 text-sm text-red-800">
                            <input type="checkbox" name="remove_og_image" value="1">
                            Hapus OG image saat menyimpan
                        </label>
                    @endif
                </div>
            </section>
        </div>

    </div>

    <div class="admin-savebar flex flex-wrap gap-3 rounded-3xl border border-navy-100 bg-white/90 p-4">
        <button type="submit" class="btn-primary">Simpan SEO</button>
    </div>
</form>
@endsection

