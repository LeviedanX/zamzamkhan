@extends('layouts.admin')
@section('title', $faq->exists ? 'Edit FAQ' : 'Tambah FAQ')

@php($inp = 'w-full rounded-xl border border-navy-200 bg-white/95 px-4 py-3 text-sm text-navy-900 shadow-sm shadow-navy-900/5 focus:border-emerald-brand focus:outline-none focus:ring-2 focus:ring-emerald-brand/20')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.faqs.index') }}" class="admin-back-link">← Kembali</a>
    <p class="admin-page-kicker mt-4">FAQ Website</p>
    <h1 class="mt-2 font-display text-2xl font-bold text-navy-900 sm:text-3xl">{{ $faq->exists ? 'Edit' : 'Tambah' }} FAQ</h1>
    <p class="mt-2 max-w-2xl text-sm leading-relaxed text-navy-500">Kelola pertanyaan yang sering muncul agar pengunjung mendapat jawaban cepat dan konsisten.</p>
</div>

<form method="POST" action="{{ $faq->exists ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}" class="admin-form-shell max-w-4xl">
    @csrf
    @if ($faq->exists) @method('PUT') @endif

    <section class="admin-form-card space-y-5 rounded-3xl border border-navy-100 bg-white p-5 sm:p-6">
        <header>
            <h2 class="font-display text-lg font-bold text-navy-900">Konten FAQ</h2>
            <p class="mt-1 text-sm text-navy-500">Pertanyaan dan jawaban yang tampil pada website publik.</p>
        </header>
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-navy-800">Pertanyaan <span class="text-red-700">*</span></label>
            <input name="question" value="{{ old('question', $faq->question) }}" class="{{ $inp }}" required>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-navy-800">Jawaban <span class="text-red-700">*</span></label>
            <textarea name="answer" rows="5" class="{{ $inp }}" required>{{ old('answer', $faq->answer) }}</textarea>
        </div>
    </section>

    <section class="admin-form-card rounded-3xl border border-navy-100 bg-white p-5 sm:p-6">
        <header class="mb-5">
            <h2 class="font-display text-lg font-bold text-navy-900">Urutan & Status</h2>
            <p class="mt-1 text-sm text-navy-500">Atur prioritas tampil dan visibilitas FAQ.</p>
        </header>
        <div class="grid gap-5 sm:grid-cols-[minmax(0,1fr)_260px]">
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-navy-800">Urutan tampil</label>
                <input name="display_order" type="number" min="1" max="{{ $maxOrder }}" value="{{ old('display_order', $faq->display_order ?: $maxOrder) }}" class="{{ $inp }}" required>
            </div>
            <label class="admin-toggle-card flex items-start gap-3 rounded-2xl p-4 text-sm text-navy-700">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $faq->is_active ?? true) ? 'checked' : '' }} class="mt-1 h-4 w-4 rounded border-navy-300 text-red-800">
                <span><span class="block font-bold text-navy-900">Tampilkan</span><span class="mt-1 block text-xs text-navy-500">FAQ aktif tampil pada website publik.</span></span>
            </label>
        </div>
    </section>

    <div class="admin-savebar flex flex-wrap gap-3 rounded-3xl border border-navy-100 bg-white/90 p-4">
        <button type="submit" class="btn-primary">Simpan FAQ</button>
        <a href="{{ route('admin.faqs.index') }}" class="btn-outline">Batal</a>
    </div>
</form>
@endsection
