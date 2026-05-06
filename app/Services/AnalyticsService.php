<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    private const MAX_RANGE_DAYS = 1096; // ~3 years

    /**
     * @return array<string, mixed>
     */
    public function build(
        User $user,
        Carbon $start,
        Carbon $end,
        string $granularity,
        bool $compare,
    ): array {
        $startStr = $start->toDateString();
        $endStr = $end->toDateString();

        $days = $start->diffInDays($end) + 1;
        if ($days > self::MAX_RANGE_DAYS) {
            abort(422, 'Date range cannot exceed ' . self::MAX_RANGE_DAYS . ' days.');
        }

        $current = $this->snapshot($user, $startStr, $endStr, $granularity);

        $comparePeriod = null;
        $prior = null;
        if ($compare) {
            $priorEnd = $start->copy()->subDay();
            $priorStart = $priorEnd->copy()->subDays($days - 1);
            $comparePeriod = [
                'start' => $priorStart->toDateString(),
                'end' => $priorEnd->toDateString(),
            ];
            $prior = $this->snapshot(
                $user,
                $comparePeriod['start'],
                $comparePeriod['end'],
                $granularity,
            );
        }

        return [
            'period' => [
                'start' => $startStr,
                'end' => $endStr,
                'days' => $days,
            ],
            'comparePeriod' => $compare ? $comparePeriod : null,
            'granularity' => $granularity,
            'summary' => $this->summaryWithDeltas($current, $prior),
            'timeSeries' => $current['timeSeries'],
            'categoryMix' => $current['categoryMix'],
            'dayOfWeek' => $current['dayOfWeek'],
            'topDescriptions' => $current['topDescriptions'],
            'recurringVsVariable' => [
                'recurringAmount' => 0.0,
                'variableAmount' => $current['totals']['totalSpend'],
                'hasRecurringData' => false,
                'message' => 'Recurring expenses are not stored on the server yet; totals are one-off expenses only.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(User $user, string $start, string $end, string $granularity): array
    {
        $totalSpend = (float) $user->expenses()->whereBetween('date', [$start, $end])->sum('amount');
        $transactionCount = (int) $user->expenses()->whereBetween('date', [$start, $end])->count();
        $rangeDays = (int) Carbon::parse($start)->diffInDays(Carbon::parse($end)) + 1;
        $rangeDays = max(1, $rangeDays);
        $avgPerDay = round($totalSpend / $rangeDays, 2);

        $categoryMixRaw = DB::table('expenses')
            ->join('categories', 'expenses.category_id', '=', 'categories.id')
            ->where('expenses.user_id', $user->id)
            ->whereBetween('expenses.date', [$start, $end])
            ->select(
                'categories.id as categoryId',
                'categories.name',
                DB::raw('SUM(expenses.amount) as amount'),
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('amount')
            ->get();

        $categoryMix = [];
        foreach ($categoryMixRaw as $row) {
            $amt = (float) $row->amount;
            $share = $totalSpend > 0 ? round(($amt / $totalSpend) * 100, 2) : 0.0;
            $categoryMix[] = [
                'categoryId' => (int) $row->categoryId,
                'name' => $row->name,
                'amount' => round($amt, 2),
                'sharePercent' => $share,
            ];
        }

        $topCategory = null;
        if (count($categoryMix) > 0) {
            $first = $categoryMix[0];
            $topCategory = [
                'categoryId' => $first['categoryId'],
                'name' => $first['name'],
                'amount' => $first['amount'],
                'sharePercent' => $first['sharePercent'],
            ];
        }

        $dailyRows = $user->expenses()
            ->whereBetween('date', [$start, $end])
            ->selectRaw('date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $byDay = [];
        foreach ($dailyRows as $r) {
            $d = $r->date instanceof Carbon ? $r->date->format('Y-m-d') : (string) $r->date;
            $byDay[$d] = (float) $r->total;
        }

        $timeSeries = $this->buildTimeSeries($start, $end, $byDay, $granularity);

        $dayOfWeek = $this->buildDayOfWeek($byDay);

        $topDescriptions = $this->topDescriptions($user, $start, $end);

        return [
            'totals' => [
                'totalSpend' => round($totalSpend, 2),
                'avgPerDay' => $avgPerDay,
                'transactionCount' => $transactionCount,
                'topCategory' => $topCategory,
            ],
            'timeSeries' => $timeSeries,
            'categoryMix' => $categoryMix,
            'dayOfWeek' => $dayOfWeek,
            'topDescriptions' => $topDescriptions,
        ];
    }

    /**
     * @param  array<string, float>  $byDay
     * @return array{granularity: string, points: list<array{periodStart: string, label: string, total: float}>}
     */
    private function buildTimeSeries(string $start, string $end, array $byDay, string $granularity): array
    {
        $startC = Carbon::parse($start)->startOfDay();
        $endC = Carbon::parse($end)->startOfDay();

        if ($granularity === 'week') {
            $buckets = [];
            $period = \Carbon\CarbonPeriod::create($startC, $endC);
            foreach ($period as $date) {
                /** @var Carbon $date */
                $dStr = $date->format('Y-m-d');
                $amount = $byDay[$dStr] ?? 0.0;
                $weekStart = $date->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
                $buckets[$weekStart] = ($buckets[$weekStart] ?? 0.0) + $amount;
            }
            ksort($buckets);

            $points = [];
            foreach ($buckets as $weekStart => $total) {
                $ws = Carbon::parse($weekStart);
                $we = $ws->copy()->endOfWeek(Carbon::MONDAY);
                $points[] = [
                    'periodStart' => $weekStart,
                    'label' => $ws->format('M j') . ' – ' . $we->format('M j'),
                    'total' => round((float) $total, 2),
                ];
            }

            return ['granularity' => 'week', 'points' => $points];
        }

        $points = [];
        $period = \Carbon\CarbonPeriod::create($startC, $endC);
        foreach ($period as $date) {
            /** @var Carbon $date */
            $dStr = $date->format('Y-m-d');
            $points[] = [
                'periodStart' => $dStr,
                'label' => $date->format('M j'),
                'total' => round($byDay[$dStr] ?? 0.0, 2),
            ];
        }

        return ['granularity' => 'day', 'points' => $points];
    }

    /**
     * @param  array<string, float>  $byDay
     * @return list<array{day: int, label: string, total: float}>
     */
    private function buildDayOfWeek(array $byDay): array
    {
        $labels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $totals = array_fill(0, 7, 0.0);
        foreach ($byDay as $dStr => $amt) {
            $w = (int) Carbon::parse($dStr)->format('w');
            $totals[$w] += $amt;
        }

        $out = [];
        // Order Mon–Sun for readability
        $order = [1, 2, 3, 4, 5, 6, 0];
        foreach ($order as $d) {
            $out[] = [
                'day' => $d,
                'label' => $labels[$d],
                'total' => round($totals[$d], 2),
            ];
        }

        return $out;
    }

    /**
     * @return list<array{description: string, transactionCount: int, totalAmount: float}>
     */
    private function topDescriptions(User $user, string $start, string $end): array
    {
        $expr = "COALESCE(NULLIF(TRIM(note), ''), '(no description)')";

        $rows = DB::table('expenses')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
            ->select(
                DB::raw("$expr as description"),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(amount) as total_amount'),
            )
            ->groupBy(DB::raw($expr))
            ->orderByDesc('total_amount')
            ->limit(10)
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'description' => (string) $r->description,
                'transactionCount' => (int) $r->transaction_count,
                'totalAmount' => round((float) $r->total_amount, 2),
            ];
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $current
     * @param  array<string, mixed>|null  $prior
     * @return array<string, mixed>
     */
    private function summaryWithDeltas(array $current, ?array $prior): array
    {
        $t = $current['totals'];
        $summary = [
            'totalSpend' => $t['totalSpend'],
            'avgPerDay' => $t['avgPerDay'],
            'transactionCount' => $t['transactionCount'],
            'topCategory' => $t['topCategory'],
            'deltas' => null,
        ];

        if ($prior !== null) {
            $p = $prior['totals'];
            $summary['deltas'] = [
                'totalSpendPercent' => $this->deltaPercent($t['totalSpend'], $p['totalSpend']),
                'avgPerDayPercent' => $this->deltaPercent($t['avgPerDay'], $p['avgPerDay']),
                'transactionCountPercent' => $this->deltaPercent($t['transactionCount'], $p['transactionCount']),
            ];
        }

        return $summary;
    }

    private function deltaPercent(float|int $current, float|int $prior): ?float
    {
        $prior = (float) $prior;
        if ($prior === 0.0) {
            return null;
        }

        return round((($current - $prior) / $prior) * 100, 2);
    }
}
