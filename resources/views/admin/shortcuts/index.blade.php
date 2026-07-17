@extends('layouts.admin')
@section('title', 'Shortcut')

@section('content')
<x-admin.page-header
    eyebrow="Pengaturan"
    title="Shortcut"
    description="Temukan dan ganti frasa pada banyak konten CMS sekaligus. Setiap perubahan wajib melalui pratinjau, dicatat, dan dapat di-undo."
/>

<section class="admin-form-surface mb-6">
    <div class="admin-form-section">
        <div>
            <h2 class="admin-form-section__title">Find & Replace Konten</h2>
            <p class="admin-form-section__description">Pilih kluster agar cakupan tetap terkontrol. Slug, URL, file, status publikasi, dan data pengajuan tidak akan disentuh.</p>
        </div>

        <div class="shortcut-safety-note" role="note">
            <strong>Aman secara bertahap.</strong>
            <span>Pratinjau tidak mengubah data. Perubahan baru dijalankan setelah konfirmasi kedua dan dibatalkan otomatis bila konten berubah sejak pratinjau.</span>
        </div>

        <form method="POST" action="{{ route('admin.shortcuts.preview') }}">
            @csrf
            <div class="admin-form-grid admin-form-grid--2">
                <label class="admin-field">
                    <span>Kluster konten <b aria-hidden="true">*</b></span>
                    <select name="cluster" required>
                        @foreach ($clusters as $value => $label)
                            <option value="{{ $value }}" @selected(old('cluster', $preview['cluster'] ?? 'all') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <small>Gunakan kluster paling spesifik untuk meminimalkan perubahan yang tidak disengaja.</small>
                </label>

                <label class="admin-toggle-field shortcut-case-toggle">
                    <input type="hidden" name="case_sensitive" value="0">
                    <input type="checkbox" name="case_sensitive" value="1" @checked((bool) old('case_sensitive', $preview['case_sensitive'] ?? true))>
                    <span><strong>Bedakan huruf besar/kecil</strong><span>Jika aktif, “UU” tidak dianggap sama dengan “uu”.</span></span>
                </label>
            </div>

            <div class="admin-form-grid admin-form-grid--2 mt-4">
                <label class="admin-field">
                    <span>Teks yang dicari <b aria-hidden="true">*</b></span>
                    <textarea name="search_text" required maxlength="2000" placeholder="Contoh: Undang-Undang Nomor 33 Tahun 2014">{{ old('search_text', $preview['search_text'] ?? '') }}</textarea>
                    <small>Frasa dicari secara literal, bukan regular expression.</small>
                </label>
                <label class="admin-field">
                    <span>Ganti dengan</span>
                    <textarea name="replacement_text" maxlength="5000" placeholder="Contoh: Undang-Undang Nomor 6 Tahun 2023">{{ old('replacement_text', $preview['replacement_text'] ?? '') }}</textarea>
                    <small>Kosongkan untuk menghapus frasa yang ditemukan.</small>
                </label>
            </div>

            <div class="admin-form-actions mt-4">
                <button type="submit" class="btn-primary">Buat Pratinjau</button>
            </div>
        </form>
    </div>
</section>

@if ($preview)
    @php($result = $preview['result'])
    <section class="admin-form-surface mb-6" aria-labelledby="shortcut-preview-title">
        <div class="admin-form-section">
            <div>
                <span class="admin-process-badge">Pratinjau · {{ $preview['cluster_label'] }}</span>
                <h2 id="shortcut-preview-title" class="admin-form-section__title mt-3">Dampak Perubahan</h2>
                <p class="admin-form-section__description">Belum ada data yang diubah. Periksa jumlah dan contoh berikut sebelum menerapkan.</p>
            </div>

            <div class="admin-summary-grid">
                <div class="admin-summary-card"><span>Record terdampak</span><strong>{{ number_format($result['affected_records']) }}</strong></div>
                <div class="admin-summary-card"><span>Kolom terdampak</span><strong>{{ number_format($result['affected_fields']) }}</strong></div>
                <div class="admin-summary-card"><span>Total kemunculan</span><strong>{{ number_format($result['occurrence_count']) }}</strong></div>
            </div>

            <dl class="shortcut-replacement-pair">
                <div><dt>Cari</dt><dd>{{ $preview['search_text'] }}</dd></div>
                <div><dt>Ganti</dt><dd>{{ $preview['replacement_text'] !== '' ? $preview['replacement_text'] : '(hapus teks)' }}</dd></div>
            </dl>

            @if ($result['affected_fields'] === 0)
                <div class="admin-empty-inline">Teks tidak ditemukan pada kluster ini. Tidak ada perubahan yang dapat diterapkan.</div>
            @else
                <div class="shortcut-preview-list">
                    @foreach ($result['samples'] as $sample)
                        <article class="shortcut-preview-item">
                            <div class="shortcut-preview-item__meta">
                                <strong>{{ $sample['source'] }}</strong>
                                <span>Record #{{ $sample['record_id'] }} · {{ $sample['field'] }} · {{ $sample['occurrences'] }} kemunculan</span>
                            </div>
                            <div class="shortcut-preview-item__comparison">
                                <div><span>Sebelum</span><p>{{ $sample['before'] }}</p></div>
                                <div><span>Sesudah</span><p>{{ $sample['after'] }}</p></div>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($result['affected_fields'] > count($result['samples']))
                    <p class="admin-muted text-xs">Menampilkan {{ count($result['samples']) }} contoh dari {{ number_format($result['affected_fields']) }} kolom terdampak.</p>
                @endif

                <form method="POST" action="{{ route('admin.shortcuts.apply') }}" class="admin-form-actions">
                    @csrf
                    <input type="hidden" name="preview_token" value="{{ $preview['token'] }}">
                    <a href="{{ route('admin.shortcuts.index') }}" class="btn-outline">Batalkan</a>
                    <button type="submit" class="btn-primary" onclick="return confirm('Terapkan penggantian ini ke {{ $result['affected_fields'] }} kolom?')">Terapkan {{ number_format($result['occurrence_count']) }} Penggantian</button>
                </form>
            @endif
        </div>
    </section>
@endif

<section aria-labelledby="shortcut-history-title">
    <div class="mb-4">
        <h2 id="shortcut-history-title" class="admin-form-section__title">Riwayat Perubahan</h2>
        <p class="admin-form-section__description">Undo hanya memulihkan kolom yang belum diedit lagi setelah penggantian massal.</p>
    </div>

    @if ($runs->isEmpty())
        <div class="admin-empty-inline">Belum ada penggantian massal.</div>
    @else
        <div class="admin-record-grid">
            @foreach ($runs as $run)
                <article class="admin-record-card">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="admin-process-badge">{{ $clusters[$run->cluster] ?? $run->cluster }}</span>
                        @if ($run->status === 'reverted')
                            <span class="admin-status-badge admin-status-badge--inactive"><span></span>Sudah di-undo</span>
                        @elseif ($run->status === 'partially_reverted')
                            <span class="admin-status-badge admin-status-badge--inactive"><span></span>Undo sebagian</span>
                        @else
                            <span class="admin-status-badge admin-status-badge--active"><span></span>Diterapkan</span>
                        @endif
                    </div>

                    <h3 class="admin-record-card__title mt-4">“{{ \Illuminate\Support\Str::limit($run->search_text, 70) }}”</h3>
                    <p class="admin-record-card__body">Menjadi “{{ $run->replacement_text !== null && $run->replacement_text !== '' ? \Illuminate\Support\Str::limit($run->replacement_text, 90) : '(dihapus)' }}”</p>
                    <p class="admin-record-card__meta">{{ number_format($run->occurrence_count) }} kemunculan · {{ number_format($run->affected_fields) }} kolom · {{ $run->case_sensitive ? 'peka kapital' : 'tanpa peka kapital' }}</p>
                    <p class="admin-record-card__meta">{{ $run->creator?->name ?? 'Admin terhapus' }} · {{ $run->executed_at?->locale('id')->translatedFormat('d M Y, H.i') }}</p>

                    <div class="admin-record-actions">
                        @if ($run->status !== 'reverted')
                            <form method="POST" action="{{ route('admin.shortcuts.undo', $run) }}" onsubmit="return confirm('Undo perubahan ini? Kolom yang sudah diedit lagi akan dilewati.')">
                                @csrf
                                <button type="submit" class="admin-danger-button">Undo</button>
                            </form>
                        @else
                            <span class="admin-muted text-xs">{{ number_format($run->reverted_fields) }} kolom telah dipulihkan.</span>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-5">{{ $runs->links() }}</div>
    @endif
</section>
@endsection
