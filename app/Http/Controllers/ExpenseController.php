<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyticsRequest;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $expenses = $request->user()
            ->expenses()
            ->with('category:id,name')
            ->latest()
            ->get();

        return ExpenseResource::collection($expenses);
    }

    public function dashboard(Request $request)
    {

        $requestedMonth = $request->input('month');

        $startOfMonth = Carbon::create($requestedMonth)->startOfMonth()->toDateString();
        $endOfMonth = Carbon::create($requestedMonth)->endOfMonth()->toDateString();


        // $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        // $endOfMonth = Carbon::now()->endOfMonth()->toDateString();


        $totalExpenses = $request->user()->expenses()->sum('amount');
        $totalCategories = $request->user()->categories()->count();

        $totalExpensesThisMonth = $request->user()
            ->expenses()
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $mostExpensiveCategory = $request->user()
            ->categories()
            ->withSum([
                'expenses as monthly_expenses_sum' => function ($query) use ($startOfMonth, $endOfMonth) {
                    $query->whereBetween('date', [$startOfMonth, $endOfMonth]);
                },
            ], 'amount')
            ->orderByDesc('monthly_expenses_sum')
            ->first();

        $expenses = $request->user()
            ->expenses()
            ->with('category:id,name')
            ->where('date', '>=', $startOfMonth)
            ->where('date', '<=', $endOfMonth)
            ->get();

        return response()->json([
            'totalExpenses' => $totalExpenses,
            'totalCategories' => $totalCategories,
            'totalExpensesThisMonth' => (float) $totalExpensesThisMonth,
            'mostExpensiveCategory' => $mostExpensiveCategory ? [
                'id' => $mostExpensiveCategory->id,
                'name' => $mostExpensiveCategory->name,
                'totalSpent' => (float) $mostExpensiveCategory->monthly_expenses_sum,
            ] : null,
            'expenses' => ExpenseResource::collection($expenses),
        ]);
    }

    public function analytics(AnalyticsRequest $request, AnalyticsService $analytics)
    {
        $start = Carbon::parse($request->validated('start_date'))->startOfDay();
        $end = Carbon::parse($request->validated('end_date'))->startOfDay();
        $granularity = $request->validated('granularity') ?? 'week';
        $compare = $request->boolean('compare', false);

        return response()->json(
            $analytics->build($request->user(), $start, $end, $granularity, $compare),
        );
    }

    public function monthlyExpenses(Request $request)
    {
        $requestedMonth = $request->input('month');

        $startMonth = Carbon::create($requestedMonth)->startOfMonth()->toDateString();
        $endMonth = Carbon::create($requestedMonth)->endOfMonth()->toDateString();

        $monthlyExpenses = $request->user()
            ->expenses()
            ->with('category:id,name')
            ->where('date', '>=', $startMonth)
            ->where('date', '<=', $endMonth)
            ->get();

        return response()->json($monthlyExpenses);
    }


    public function store(StoreExpenseRequest $request)
    {
        $expense = $request->user()
            ->expenses()
            ->create($request->validated());
        $expense->load('category:id,name');

        return (new ExpenseResource($expense))->response()->setStatusCode(201);
    }

    // private methods for authorization expenses
    private function authorizeExpense($request, $expense)
    {
        if ($expense->user_id !== $request->user()->id) {
            abort(403);
        }
    }

    public function update(StoreExpenseRequest $request, Expense $expense)
    {
        $this->authorizeExpense($request, $expense);

        $expense->update($request->validated());
        $expense->load('category:id,name');

        return new ExpenseResource($expense);
    }

    public function destroy(Request $request, Expense $expense)
    {
        $this->authorizeExpense($request, $expense);

        $expense->delete();

        return response()->json(['message' => 'Expense deleted']);
    }

    // public function monthlyExpenses(Request $request)
    // {
    //     $report = (new ExpenseService)->generateMonthlyExpensesReport($request->user());

    //     return response()->json($report);
    // }
}
