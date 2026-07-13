<?php

namespace App\Console\Commands;

use App\Models\ReportExport;
use App\Models\WebVisit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeOperationalData extends Command
{
    protected $signature = 'operational:purge {--dry-run : Hitung tanpa menghapus data atau file}';

    protected $description = 'Hapus analytics dan export laporan yang melewati masa retensi.';

    public function handle(): int
    {
        $visitDays = max(30, (int) config('admin.retention.web_visits_days', 400));
        $exportDays = max(1, (int) config('admin.retention.report_exports_days', 30));
        $visitCutoff = now()->subDays($visitDays);
        $exportCutoff = now()->subDays($exportDays);

        $visitQuery = WebVisit::where('visited_at', '<', $visitCutoff);
        $exports = ReportExport::where('generated_at', '<', $exportCutoff)->get();
        $visitCount = (clone $visitQuery)->count();
        $exportCount = $exports->count();
        $orphanPaths = $this->expiredOrphanPaths($exportCutoff->timestamp);

        if (! $this->option('dry-run')) {
            $visitQuery->delete();

            foreach ($exports as $export) {
                $path = $export->file_path;
                $export->delete();
                if ($path) {
                    Storage::disk('local')->delete($path);
                }
            }

            foreach ($orphanPaths as $path) {
                Storage::disk('local')->delete($path);
            }
        }

        $mode = $this->option('dry-run') ? 'Ditemukan' : 'Dihapus';
        $this->info("{$mode}: {$visitCount} kunjungan, {$exportCount} histori export, {$orphanPaths->count()} file orphan.");

        return self::SUCCESS;
    }

    private function expiredOrphanPaths(int $cutoffTimestamp)
    {
        $disk = Storage::disk('local');
        $referenced = ReportExport::whereNotNull('file_path')->pluck('file_path')->flip();

        return collect($disk->files('reports'))
            ->reject(fn (string $path) => $referenced->has($path))
            ->filter(function (string $path) use ($disk, $cutoffTimestamp): bool {
                try {
                    return $disk->lastModified($path) < $cutoffTimestamp;
                } catch (\Throwable) {
                    return false;
                }
            })
            ->values();
    }
}
