<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessApplication;
use App\Models\BusinessCategory;
use App\Models\ReportExport;
use App\Support\ReportExcelWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public const COLUMNS = [
        'brand_name'=>'Merek', 'business_name'=>'Perusahaan', 'owner_name'=>'Pemilik',
        'registration_number'=>'No. Daftar', 'applicant_type'=>'Jenis Pemohon',
        'business_category'=>'Kategori Bisnis', 'process_status'=>'Status',
        'submitted_at'=>'Tanggal Masuk', 'certificate_issued_at'=>'Tanggal Sertifikat',
    ];

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $query = BusinessApplication::filtered($filters)->with('category');
        $results = (clone $query)->latest('submitted_at')->latest('id')->paginate(25)->withQueryString();
        $summary = $this->summary(clone $query);
        $categories = BusinessCategory::where('is_active',true)->orderBy('name')->get();
        $history = ReportExport::with('admin')->latest('generated_at')->limit(20)->get();

        return view('admin.reports.index', compact('results','summary','filters','categories','history'));
    }

    public function exportCsv(Request $request)
    {
        $filters = $this->filters($request);
        $columns = $request->input('columns', array_keys(self::COLUMNS));
        abort_if(empty($columns), 422, 'Pilih minimal satu kolom.');
        $rows = BusinessApplication::filtered($filters)->with('category')->latest('submitted_at')->latest('id')->get();
        $path = $this->newExportPath('csv');
        $stream = fopen('php://temp', 'r+');

        // BOM: Excel mengenali file sebagai UTF-8 (tanpa ini huruf beraksen rusak).
        fwrite($stream, "\xEF\xBB\xBF");
        // Excel memakai pemisah daftar sesuai locale Windows (koma ATAU titik koma),
        // jadi file dengan delimiter tetap sering tumpah ke satu kolom. Direktif
        // "sep=" memaksa Excel memakai koma apa pun locale-nya, sehingga hasilnya
        // selalu terbaca sebagai tabel yang rapi. Excel tidak menampilkan baris ini.
        fwrite($stream, "sep=,\r\n");

        // escape: '' — PHP 8.4 mendeprekasi nilai default; sekaligus mematikan
        // escape backslash ala PHP yang bukan CSV standar dan bisa merusak sel.
        fputcsv($stream, array_map(fn ($c) => self::COLUMNS[$c], $columns), ',', '"', '');
        foreach ($rows as $row) fputcsv($stream, array_map(fn ($c) => $this->csvValue($this->value($row, $c)), $columns), ',', '"', '');
        rewind($stream);
        $stored = Storage::disk('local')->put($path, stream_get_contents($stream));
        fclose($stream);

        if (! $stored) {
            throw new \RuntimeException('Berkas CSV gagal disimpan.');
        }

        try {
            $this->record($filters, $columns, 'csv', $path);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }

        return Storage::disk('local')->download($path, basename($path), ['Content-Type'=>'text/csv; charset=UTF-8']);
    }

    public function exportExcel(Request $request)
    {
        $filters = $this->filters($request);
        $columns = $request->input('columns', array_keys(self::COLUMNS));
        abort_if(empty($columns), 422, 'Pilih minimal satu kolom.');
        $rows = BusinessApplication::filtered($filters)->with('category')->latest('submitted_at')->latest('id')->get();

        $path = $this->newExportPath('xlsx');
        $absolute = Storage::disk('local')->path($path);
        if (! Storage::disk('local')->makeDirectory(dirname($path))) {
            throw new \RuntimeException('Direktori export gagal disiapkan.');
        }

        try {
            ReportExcelWriter::write(
                $absolute,
                'Laporan Data Pengajuan',
                $this->subtitle($filters, $rows->count()),
                array_map(fn ($c) => self::COLUMNS[$c], $columns),
                $rows->map(fn ($row) => array_map(fn ($c) => $this->value($row, $c), $columns))->all(),
            );

            $this->record($filters, $columns, 'xlsx', $path);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }

        return Storage::disk('local')->download($path, basename($path));
    }

    /** Keterangan filter yang dicetak di bawah judul laporan. */
    private function subtitle(array $filters, int $total): string
    {
        $parts = ['Dibuat '.now()->locale('id')->translatedFormat('d F Y, H:i').' WIB', $total.' data'];

        if (filled($filters['keyword'] ?? null)) $parts[] = 'Kata kunci: '.$filters['keyword'];
        if (filled($filters['process_status'] ?? null)) $parts[] = 'Status: '.$filters['process_status'];
        if (filled($filters['applicant_type'] ?? null)) $parts[] = 'Jenis: '.($filters['applicant_type'] === 'company' ? 'Badan Usaha' : 'Perorangan');
        if (filled($filters['business_category_id'] ?? null)) $parts[] = 'Kategori: '.(BusinessCategory::find($filters['business_category_id'])?->name ?? '-');
        if (filled($filters['date_from'] ?? null) || filled($filters['date_to'] ?? null)) {
            $parts[] = 'Periode: '.($filters['date_from'] ?? '…').' s/d '.($filters['date_to'] ?? '…');
        }

        return implode('  ·  ', $parts);
    }

    public function printView(Request $request)
    {
        $filters = $this->filters($request);
        $columns = $request->input('columns', array_keys(self::COLUMNS));
        $data = BusinessApplication::filtered($filters)->with('category')->latest('submitted_at')->latest('id')->get();
        $summary = $this->summary(BusinessApplication::filtered($filters));

        return view('admin.reports.print', compact('filters','columns','data','summary'));
    }

    public function download(ReportExport $reportExport)
    {
        abort_unless($reportExport->file_path && Storage::disk('local')->exists($reportExport->file_path), 404);
        return Storage::disk('local')->download($reportExport->file_path);
    }

    public function destroyHistory(ReportExport $reportExport)
    {
        $this->forgetExport($reportExport);

        return back()->with('ok', 'Riwayat export dihapus.');
    }

    public function clearHistory()
    {
        // Sengaja mengambil semua baris, bukan hanya 20 yang tampil di layar,
        // supaya tidak ada file export lama yang tertinggal di storage.
        $exports = ReportExport::all();

        foreach ($exports as $export) {
            $this->forgetExport($export);
        }

        return back()->with('ok', $exports->isEmpty()
            ? 'Tidak ada riwayat export yang perlu dihapus.'
            : $exports->count().' riwayat export dihapus.');
    }

    /** Hapus catatan riwayat sekaligus berkasnya agar storage tidak menumpuk. */
    private function forgetExport(ReportExport $export): void
    {
        if ($export->file_path && Storage::disk('local')->exists($export->file_path)) {
            Storage::disk('local')->delete($export->file_path);
        }

        $export->delete();
    }

    private function filters(Request $request): array
    {
        return $request->validate([
            'keyword'=>['nullable','string','max:255'], 'applicant_type'=>['nullable',Rule::in(['company','individual'])],
            'process_status'=>['nullable',Rule::in(BusinessApplication::STATUSES)],
            'business_category_id'=>['nullable','integer',Rule::exists('business_categories','id')],
            'date_from'=>['nullable','date'], 'date_to'=>['nullable','date','after_or_equal:date_from'],
            'columns'=>['nullable','array'], 'columns.*'=>[Rule::in(array_keys(self::COLUMNS))],
        ]);
    }

    private function summary($query): array
    {
        return ['total'=>(clone $query)->count(), 'issued'=>(clone $query)->where('process_status','Sertifikat Terbit')->count(), 'ongoing'=>(clone $query)->whereNotIn('process_status',['Sertifikat Terbit','Batal'])->count()];
    }

    private function value(BusinessApplication $row, string $column): string
    {
        return match ($column) {
            'business_category' => $row->category?->name ?? '-',
            'applicant_type' => $row->applicantTypeLabel(),
            'submitted_at','certificate_issued_at' => $row->{$column}?->format('d-m-Y') ?? '-',
            default => (string) ($row->{$column} ?? '-'),
        };
    }

    private function csvValue(string $value): string
    {
        return preg_match('/^[=+\-@]/', ltrim($value)) ? "'".$value : $value;
    }

    private function newExportPath(string $extension): string
    {
        return 'reports/laporan-pengajuan-'.now()->format('Y-m-d-His').'-'.Str::lower((string) Str::ulid()).'.'.$extension;
    }

    private function record(array $filters, array $columns, string $format, string $path): void
    {
        ReportExport::create(['title'=>'Laporan Data Pengajuan','report_type'=>'applications','filters_json'=>$filters,'columns_json'=>$columns,'format'=>$format,'file_path'=>$path,'generated_by'=>auth('admin')->id(),'generated_at'=>now()]);
    }
}
