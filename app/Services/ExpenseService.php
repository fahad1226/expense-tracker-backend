<?php

namespace App\Services;

use App\Models\User;

class ExpenseService
{
    public function create(array $data, User $user)
    {
        return $user->expenses()->create($data);
    }

    public function generateMonthlyExpensesReport(User $user)
    {
        return $user->expenses()
            ->where('date', '>=', now()->startOfMonth())
            ->where('date', '<=', now()->endOfMonth())
            ->get();

        return [
            'total_expenses' => $expenses->sum('amount'),
            'average_expense' => $expenses->avg('amount'),
            'highest_expense' => $expenses->max('amount'),
            'lowest_expense' => $expenses->min('amount'),
        ];
    }
}
