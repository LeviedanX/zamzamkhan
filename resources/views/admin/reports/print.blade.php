<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Data Pengajuan</title>
    <style nonce="{{ $cspNonce }}">
        body { font: 12px Arial; margin: 24px; color: #222; }
        h1 { margin-bottom: 4px; }
        .summary { margin: 16px 0; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #aaa; padding: 6px; text-align: left; }
        th { background: #7f1d1d; color: #fff; }
        @media print { button { display: none; } }
    </style>
</head>
<body>
    <button type="button" data-print-report>Cetak / Simpan PDF</button>
    <h1>Laporan Data Pengajuan</h1>
    <p>Dibuat {{ now()->format('d-m-Y H:i') }}</p>
    <p class="summary">
        Total: {{ $summary['total'] }} &middot;
        Berjalan: {{ $summary['ongoing'] }} &middot;
        Sertifikat terbit: {{ $summary['issued'] }}
    </p>
    <table>
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th>{{ \App\Http\Controllers\Admin\ReportController::COLUMNS[$column] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $row)
                <tr>
                    @foreach ($columns as $column)
                        <td>
                            @switch($column)
                                @case('business_category')
                                    {{ $row->category?->name ?? '-' }}
                                    @break
                                @case('applicant_type')
                                    {{ $row->applicantTypeLabel() }}
                                    @break
                                @case('submitted_at')
                                @case('certificate_issued_at')
                                    {{ $row->{$column}?->format('d-m-Y') ?? '-' }}
                                    @break
                                @default
                                    {{ $row->{$column} ?? '-' }}
                            @endswitch
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
    <script nonce="{{ $cspNonce }}">
        document.querySelector('[data-print-report]').addEventListener('click', function () {
            window.print();
        });
    </script>
</body>
</html>
