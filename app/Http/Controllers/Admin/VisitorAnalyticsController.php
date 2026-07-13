<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebVisit;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisitorAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $period = in_array($request->query('period'), ['day', 'week', 'month', 'year', 'overall'], true)
            ? $request->query('period')
            : 'month';

        [$start, $end, $labels, $periodLabel] = $this->periodDefinition($period);
        $query = WebVisit::query()->where('visited_at', '<=', $end);
        if ($start) {
            $query->where('visited_at', '>=', $start);
        }

        $pageViews = (clone $query)->count();
        $uniqueCount = (clone $query)->distinct()->count('visitor_key');
        $bucket = $this->bucketExpression($period);
        $bucketRows = (clone $query)
            ->selectRaw("{$bucket} as bucket, COUNT(*) as page_views, COUNT(DISTINCT visitor_key) as unique_visitors")
            ->groupByRaw($bucket)
            ->get()
            ->keyBy(fn ($row) => (string) $row->bucket);
        $series = [
            'labels' => $labels->values()->all(),
            'pageViews' => $labels->keys()->map(fn ($key) => (int) ($bucketRows->get((string) $key)?->page_views ?? 0))->all(),
            'uniqueVisitors' => $labels->keys()->map(fn ($key) => (int) ($bucketRows->get((string) $key)?->unique_visitors ?? 0))->all(),
        ];
        $summary = [
            'pageViews' => $pageViews,
            'uniqueVisitors' => $uniqueCount,
            'pagesPerVisitor' => $uniqueCount ? round($pageViews / $uniqueCount, 1) : 0,
            'today' => WebVisit::whereBetween('visited_at', [now()->startOfDay(), now()->endOfDay()])->count(),
            'overall' => WebVisit::count(),
        ];

        $topPages = (clone $query)
            ->selectRaw('path, COUNT(*) as views, COUNT(DISTINCT visitor_key) as visitors')
            ->groupBy('path')->orderByDesc('views')->limit(8)->get();

        $deviceCounts = (clone $query)
            ->selectRaw('device_type, COUNT(*) as total')
            ->groupBy('device_type')->pluck('total', 'device_type');
        $devices = collect(['desktop' => 0, 'mobile' => 0, 'tablet' => 0])
            ->merge($deviceCounts->map(fn ($value) => (int) $value));

        $referrerExpression = "COALESCE(referrer_host, 'Akses langsung')";
        $referrers = (clone $query)
            ->selectRaw("{$referrerExpression} as referrer, COUNT(*) as total")
            ->groupByRaw($referrerExpression)->orderByDesc('total')->limit(6)
            ->pluck('total', 'referrer')->map(fn ($value) => (int) $value);

        return view('admin.analytics.index', compact(
            'period', 'periodLabel', 'series', 'summary', 'topPages', 'devices', 'referrers'
        ));
    }

    private function periodDefinition(string $period): array
    {
        $now = now();

        return match ($period) {
            'day' => [
                $now->copy()->startOfDay(), $now->copy()->endOfDay(),
                collect(range(0, 23))->mapWithKeys(fn ($hour) => [sprintf('%02d', $hour) => sprintf('%02d.00', $hour)]),
                'Hari ini',
            ],
            'week' => [
                $now->copy()->startOfWeek(), $now->copy()->endOfWeek(),
                collect(CarbonPeriod::create($now->copy()->startOfWeek(), $now->copy()->endOfWeek()))
                    ->mapWithKeys(fn (Carbon $date) => [$date->format('Y-m-d') => $date->locale('id')->translatedFormat('D, d M')]),
                'Minggu ini',
            ],
            'month' => [
                $now->copy()->startOfMonth(), $now->copy()->endOfMonth(),
                collect(CarbonPeriod::create($now->copy()->startOfMonth(), $now->copy()->endOfMonth()))
                    ->mapWithKeys(fn (Carbon $date) => [$date->format('Y-m-d') => $date->format('d')]),
                $now->locale('id')->translatedFormat('F Y'),
            ],
            'year' => [
                $now->copy()->startOfYear(), $now->copy()->endOfYear(),
                collect(range(1, 12))->mapWithKeys(fn ($month) => [sprintf('%04d-%02d', $now->year, $month) => Carbon::create($now->year, $month)->locale('id')->translatedFormat('M')]),
                'Tahun '.$now->year,
            ],
            default => $this->overallDefinition($now),
        };
    }

    private function overallDefinition(Carbon $now): array
    {
        $first = WebVisit::min('visited_at');
        $firstYear = $first ? Carbon::parse($first)->year : $now->year;
        $labels = collect(range($firstYear, $now->year))->mapWithKeys(fn ($year) => [(string) $year => (string) $year]);

        return [null, $now->copy()->endOfDay(), $labels, 'Keseluruhan'];
    }

    private function bucketExpression(string $period): string
    {
        $format = match ($period) {
            'day' => '%H',
            'week', 'month' => '%Y-%m-%d',
            'year' => '%Y-%m',
            default => '%Y',
        };

        return match (DB::connection()->getDriverName()) {
            'sqlite' => "strftime('{$format}', visited_at)",
            'pgsql' => "to_char(visited_at, '".strtr($format, ['%Y' => 'YYYY', '%m' => 'MM', '%d' => 'DD', '%H' => 'HH24'])."')",
            default => "DATE_FORMAT(visited_at, '{$format}')",
        };
    }
}
