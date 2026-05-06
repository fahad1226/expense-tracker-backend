<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShowBudgetRequest;
use App\Http\Requests\UpsertBudgetRequest;
use App\Services\BudgetService;
use Illuminate\Http\JsonResponse;

class BudgetController extends Controller
{
    public function show(ShowBudgetRequest $request, BudgetService $budgets): JsonResponse
    {
        $month = $request->validated('month');

        return response()->json($budgets->overview($request->user(), $month));
    }

    public function upsert(UpsertBudgetRequest $request, BudgetService $budgets): JsonResponse
    {
        $validated = $request->validated();
        $month = $validated['month'];
        $amount = (float) $validated['amount'];

        $budgets->upsertMonthly($request->user(), $month, $amount);

        return response()->json($budgets->overview($request->user(), $month));
    }
}
