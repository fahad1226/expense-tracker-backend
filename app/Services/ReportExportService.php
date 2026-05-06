<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportService
{
    public function __construct(
        private AnalyticsService $analytics,
    ) {}

    /**
     * @param  array{start_date: string, end_date: string, format: string}  $validated
     * @param  array{include_category_breakdown: bool, include_transactions: bool, include_compare: bool}  $flags
     */
    public function download(User $user, array $validated, array $flags): StreamedResponse|\Illuminate\Http\Response
    {
        $data = $this->buildExportData($user, $validated, $flags);

        return match ($validated['format']) {
            'csv' => $this->csvResponse($data),
            'pdf' => $this->pdfResponse($data),
            default => abort(422),
        };
    }

    /**
     * @param  array{start_date: string, end_date: string}  $validated
     * @param  array{include_category_breakdown: bool, include_transactions: bool, include_compare: bool}  $flags
     * @return array<string, mixed>
     */
    public function buildExportData(User $user, array $validated, array $flags): array
    {
        $start = Carbon::parse($validated['start_date'])->startOfDay();
        $end = Carbon::parse($validated['end_date'])->startOfDay();

        $analyticsPayload = $this->analytics->build(
            $user,
            $start,
            $end,
            'week',
            $flags['include_compare'],
        );

        $transactions = collect();
        if ($flags['include_transactions']) {
            /** @var Collection<int, Expense> $transactions */
            $transactions = $user->expenses()
                ->with('category:id,name')
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->orderBy('date')
                ->orderBy('id')
                ->get();
        }

        $categoryMix = $flags['include_category_breakdown']
            ? $analyticsPayload['categoryMix']
            : [];

        return [
            'generatedAt' => now(config('app.timezone'))->format('Y-m-d H:i:s'),
            'userName' => $user->name,
            'period' => $analyticsPayload['period'],
            'comparePeriod' => $flags['include_compare'] ? $analyticsPayload['comparePeriod'] : null,
            'summary' => $analyticsPayload['summary'],
            'categoryMix' => $categoryMix,
            'transactions' => $transactions->map(fn (Expense $t) => [
                'date' => $t->date instanceof Carbon ? $t->date->format('Y-m-d') : (string) $t->date,
                'category' => $t->category?->name ?? '',
                'description' => $t->note ?? '',
                'amount' => (float) $t->amount,
            ])->all(),
            'includeCategory' => $flags['include_category_breakdown'],
            'includeTransactions' => $flags['include_transactions'],
            'includeCompare' => $flags['include_compare'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function csvResponse(array $data): StreamedResponse
    {
        $filename = sprintf(
            'expense-report-%s-to-%s.csv',
            $data['period']['start'],
            $data['period']['end'],
        );

        return response()->streamDownload(function () use ($data): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }

            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($out, ['Expense report']);
            fputcsv($out, ['Name', $data['userName']]);
            fputcsv($out, ['Period', $data['period']['start'].' — '.$data['period']['end']]);
            fputcsv($out, ['Generated', $data['generatedAt']]);
            fputcsv($out, []);

            fputcsv($out, ['SUMMARY']);
            fputcsv($out, ['Total spend', $data['summary']['totalSpend']]);
            fputcsv($out, ['Average per day', $data['summary']['avgPerDay']]);
            fputcsv($out, ['Transaction count', $data['summary']['transactionCount']]);
            $top = $data['summary']['topCategory'];
            fputcsv($out, [
                'Top category',
                $top ? $top['name'].' ('.$top['sharePercent'].'%)' : '—',
            ]);

            if ($data['includeCompare'] && $data['comparePeriod'] && $data['summary']['deltas']) {
                fputcsv($out, []);
                fputcsv($out, ['COMPARISON']);
                fputcsv($out, [
                    'Prior period',
                    $data['comparePeriod']['start'].' — '.$data['comparePeriod']['end'],
                ]);
                $d = $data['summary']['deltas'];
                fputcsv($out, [
                    'Total spend change %',
                    $d['totalSpendPercent'] ?? 'n/a',
                ]);
                fputcsv($out, [
                    'Avg per day change %',
                    $d['avgPerDayPercent'] ?? 'n/a',
                ]);
                fputcsv($out, [
                    'Transaction count change %',
                    $d['transactionCountPercent'] ?? 'n/a',
                ]);
            }

            if ($data['includeCategory'] && count($data['categoryMix']) > 0) {
                fputcsv($out, []);
                fputcsv($out, ['CATEGORY BREAKDOWN']);
                fputcsv($out, ['Category', 'Amount', 'Share %']);
                foreach ($data['categoryMix'] as $row) {
                    fputcsv($out, [$row['name'], $row['amount'], $row['sharePercent']]);
                }
            }

            if ($data['includeTransactions'] && count($data['transactions']) > 0) {
                fputcsv($out, []);
                fputcsv($out, ['TRANSACTIONS']);
                fputcsv($out, ['Date', 'Category', 'Description', 'Amount']);
                foreach ($data['transactions'] as $t) {
                    fputcsv($out, [
                        $t['date'],
                        $t['category'],
                        $t['description'],
                        $t['amount'],
                    ]);
                }
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function pdfResponse(array $data): \Illuminate\Http\Response
    {
        $filename = sprintf(
            'expense-report-%s-to-%s.pdf',
            $data['period']['start'],
            $data['period']['end'],
        );

        $pdf = Pdf::loadView('reports.export', ['data' => $data])
            ->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }
}
