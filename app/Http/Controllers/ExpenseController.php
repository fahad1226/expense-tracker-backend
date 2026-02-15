<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()
            ->expenses()
            ->latest()
            ->get();
    }

    public function store(StoreExpenseRequest $request)
    {
        $expense = $request->user()
            ->expenses()
            ->create($request->validated());

        return response()->json($expense, 201);
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

        return response()->json($expense);
    }

    public function destroy(Request $request, Expense $expense)
    {
        $this->authorizeExpense($request, $expense);

        $expense->delete();

        return response()->json(['message' => 'Expense deleted']);
    }
}
