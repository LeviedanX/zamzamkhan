@extends('layouts.admin')
@section('title', 'Analitik Pengunjung')

@section('content')
<x-admin.page-header
    eyebrow="Operasional Internal"
    title="Analitik Pengunjung"
    description="Lihat berapa kali halaman website dibuka dan berapa sesi pengunjung yang terdeteksi. Bot, halaman admin, dan alamat IP mentah tidak dihitung."
/>

<section class="analytics-toolbar admin-panel" aria-label="Filter periode analitik">
    <div>
        <span class="analytics-toolbar__label">Periode aktif</span>
        <strong>{{ $periodLabel }}</strong>
    </div>
    <nav class="analytics-periods" aria-label="Pilih periode">
        @foreach (['day' => 'Hari', 'week' => 'Minggu', 'month' => 'Bulan', 'year' => 'Tahun', 'overall' => 'Semua waktu'] as $key => $label)
            <a href="{{ route('admin.analytics.index', ['period' => $key]) }}"
               class="{{ $period === $key ? 'is-active' : '' }}"
               @if($period === $key) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </nav>
</section>

<section class="analytics-kpi-grid" aria-label="Ringkasan pengunjung">
    <article class="analytics-kpi analytics-kpi--primary">
        <span>Total halaman dibuka</span><strong>{{ number_format($summary['pageViews']) }}</strong>
        <small>Jumlah seluruh halaman publik yang dibuka pada periode ini</small>
    </article>
    <article class="analytics-kpi analytics-kpi--teal">
        <span>Sesi pengunjung</span><strong>{{ number_format($summary['uniqueVisitors']) }}</strong>
        <small>Perkiraan kunjungan berbeda berdasarkan sesi browser</small>
    </article>
    <article class="analytics-kpi">
        <span>Rata-rata halaman per sesi</span><strong>{{ number_format($summary['pagesPerVisitor'], 1) }}</strong>
        <small>Total halaman dibuka dibagi jumlah sesi pengunjung</small>
    </article>
    <article class="analytics-kpi">
        <span>Hari ini / sejak awal</span><strong>{{ number_format($summary['today']) }} <em>/ {{ number_format($summary['overall']) }}</em></strong>
        <small>Halaman dibuka hari ini dibanding seluruh data tersimpan</small>
    </article>
</section>

<aside class="admin-panel mt-4 px-5 py-4 text-sm leading-6 text-[var(--admin-muted)]" aria-label="Cara membaca data pengunjung">
    <strong class="text-[var(--admin-ink)]">Cara membaca:</strong>
    satu sesi browser dihitung satu kali sebagai sesi pengunjung, meskipun membuka banyak halaman. Orang yang sama dapat dihitung lagi jika memakai browser, perangkat, mode samaran, atau sesi baru.
</aside>

<div id="visitor-analytics" class="analytics-grid">
    <section class="analytics-chart-card admin-panel analytics-chart-card--main">
        <header class="analytics-card-header">
            <div>
                <span class="analytics-card-kicker">Tren kunjungan</span>
                <h2>Halaman dibuka dan sesi pengunjung</h2>
            </div>
            <div class="analytics-chart-types" role="group" aria-label="Jenis visualisasi">
                <button type="button" class="is-active" data-chart-type="line">Garis</button>
                <button type="button" data-chart-type="bar">Batang</button>
                <button type="button" data-chart-type="area">Area</button>
            </div>
        </header>
        <div class="analytics-canvas-wrap"><canvas id="visitor-trend-chart" aria-label="Grafik tren kunjungan"></canvas></div>
        <div class="analytics-legend-note"><span><i class="is-maroon"></i>Halaman dibuka</span><span><i class="is-teal"></i>Sesi pengunjung</span></div>
    </section>

    <section class="analytics-chart-card admin-panel">
        <header class="analytics-card-header">
            <div><span class="analytics-card-kicker">Jenis perangkat</span><h2>Halaman dibuka menurut perangkat</h2></div>
        </header>
        <div class="analytics-canvas-wrap analytics-canvas-wrap--donut"><canvas id="visitor-device-chart" aria-label="Grafik perangkat pengunjung"></canvas></div>
        <div class="analytics-device-list">
            @foreach (['desktop' => 'Desktop', 'mobile' => 'Mobile', 'tablet' => 'Tablet'] as $key => $label)
                <span><b>{{ $label }}</b><strong>{{ number_format($devices[$key] ?? 0) }}</strong></span>
            @endforeach
        </div>
    </section>
</div>

<div class="analytics-detail-grid">
    <section class="analytics-list-card admin-panel">
        <header class="analytics-card-header">
            <div><span class="analytics-card-kicker">Konten teratas</span><h2>Halaman paling sering dibuka</h2></div>
        </header>
        @forelse ($topPages as $page)
            <div class="analytics-list-row">
                <span title="{{ $page['path'] }}">{{ $page['path'] }}</span>
                <span><strong>{{ number_format($page['views']) }} kali</strong><small>{{ number_format($page['visitors']) }} sesi pengunjung</small></span>
            </div>
        @empty
            <div class="admin-empty-inline">Belum ada kunjungan publik pada periode ini.</div>
        @endforelse
    </section>

    <section class="analytics-list-card admin-panel">
        <header class="analytics-card-header">
            <div><span class="analytics-card-kicker">Sumber kunjungan</span><h2>Dari mana halaman dibuka</h2></div>
        </header>
        @forelse ($referrers as $referrer => $count)
            <div class="analytics-list-row">
                <span title="{{ $referrer }}">{{ $referrer }}</span><strong>{{ number_format($count) }}</strong>
            </div>
        @empty
            <div class="admin-empty-inline">Belum ada sumber kunjungan untuk ditampilkan.</div>
        @endforelse
    </section>
</div>

<details class="analytics-data-table admin-panel">
    <summary>Lihat data grafik dalam tabel</summary>
    <div class="admin-table-card--responsive overflow-x-auto">
        <table class="admin-responsive-table">
            <thead><tr><th>Waktu</th><th>Halaman dibuka</th><th>Sesi pengunjung</th></tr></thead>
            <tbody>
                @foreach ($series['labels'] as $index => $label)
                    <tr><td data-label="Waktu">{{ $label }}</td><td data-label="Halaman dibuka">{{ $series['pageViews'][$index] }}</td><td data-label="Sesi pengunjung">{{ $series['uniqueVisitors'][$index] }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</details>

<script type="application/json" id="visitor-analytics-data" nonce="{{ $cspNonce }}">{!! json_encode([
    'series' => $series,
    'devices' => [
        'labels' => ['Desktop', 'Mobile', 'Tablet'],
        'values' => [$devices['desktop'] ?? 0, $devices['mobile'] ?? 0, $devices['tablet'] ?? 0],
    ],
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endsection
