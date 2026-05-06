<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportExportRequest;
use App\Services\AnalyticsService;
use App\Services\ReportExportService;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * JSON analytics-style payload for a month (legacy) or explicit date range.
     */
    public function summary(Request $request, AnalyticsService $analytics)
    {
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start = Carbon::parse($request->query('start_date'))->startOfDay();
            $end = Carbon::parse($request->query('end_date'))->startOfDay();
        } else {
            $month = $request->query('month', now()->format('Y-m'));
            try {
                $anchor = Carbon::createFromFormat('Y-m-d', $month.'-01');
                if ($anchor === false) {
                    return response()->json(['message' => 'Invalid month. Use YYYY-MM.'], 422);
                }
                $start = $anchor->copy()->startOfMonth()->startOfDay();
                $end = $anchor->copy()->endOfMonth()->startOfDay();
            } catch (\Throwable) {
                return response()->json(['message' => 'Invalid month. Use YYYY-MM.'], 422);
            }
        }

        return response()->json(
            $analytics->build(
                $request->user(),
                $start,
                $end,
                'day',
                $request->boolean('compare', true),
            ),
        );
    }

    public function export(
        ReportExportRequest $request,
        ReportExportService $exporter,
    ): StreamedResponse|Response {
        $flags = [
            'include_category_breakdown' => $request->boolean('include_category_breakdown', true),
            'include_transactions' => $request->boolean('include_transactions', true),
            'include_compare' => $request->boolean('include_compare', false),
        ];

        return $exporter->download($request->user(), $request->validated(), $flags);
    }
}
