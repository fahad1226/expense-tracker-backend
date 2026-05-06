<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Expense;
use App\Models\User;
use Carbon\Carbon;
use InvalidArgumentException;

/**
 * Builds budget overview for a calendar month: cap, spend, status, breakdown.
 * Uses {@see AnalyticsService} for category totals so numbers match analytics.
 */
class BudgetService
{
    /** Share of budget used at or above this ratio → "warning" (if not already over). */
    private const WARNING_THRESHOLD = 0.8;

    public function __construct(
        private AnalyticsService $analytics,
    ) {}

    /**
     * @return array{
     *   month: string,
     *   budgetAmount: float|null,
     *   hasBudget: bool,
     *   spent: float,
     *   remaining: float|null,
     *   percentUsed: float|null,
     *   status: 'unset'|'ok'|'warning'|'over',
     *   daysRemainingInMonth: int|null,
     *   categoryBreakdown: list<array<string, mixed>>,
     *   topExpenses: list<array<string, mixed>>,
     * }
     */
    public function overview(User $user, string $yearMonth): array
    {
        $this->assertYearMonth($yearMonth);

        [$start, $end] = $this->monthBounds($yearMonth);

        $budgetRow = Budget::query()
            ->where('user_id', $user->id)
            ->where('year_month', $yearMonth)
            ->first();

        $storedAmount = $budgetRow ? (float) $budgetRow->amount : null;
        $budgetAmount = $storedAmount !== null && $storedAmount > 0 ? $storedAmount : null;

        $analyticsPayload = $this->analytics->build(
            $user,
            $start,
            $end,
            'week',
            false,
        );

        $spent = (float) $analyticsPayload['summary']['totalSpend'];
        $status = $this->resolveStatus($budgetAmount, $spent);

        $percentUsed = null;
        if ($budgetAmount !== null && $budgetAmount > 0) {
            $percentUsed = round(($spent / $budgetAmount) * 100, 1);
        }

        return [
            'month' => $yearMonth,
            'budgetAmount' => $storedAmount,
            'hasBudget' => $budgetAmount !== null,
            'spent' => round($spent, 2),
            'remaining' => $budgetAmount !== null
                ? round($budgetAmount - $spent, 2)
                : null,
            'percentUsed' => $percentUsed,
            'status' => $status,
            'daysRemainingInMonth' => $this->daysRemainingInMonth($start, $end),
            'categoryBreakdown' => array_values($analyticsPayload['categoryMix']),
            'topExpenses' => $this->topExpensesForMonth($user, $start, $end, 5),
        ];
    }

    /**
     * Create or replace the monthly budget for the signed-in user.
     */
    public function upsertMonthly(User $user, string $yearMonth, float $amount): Budget
    {
        $this->assertYearMonth($yearMonth);

        if ($amount < 0) {
            throw new InvalidArgumentException('Budget amount cannot be negative.');
        }

        /** @var Budget $budget */
        $budget = Budget::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'year_month' => $yearMonth,
            ],
            ['amount' => $amount],
        );

        return $budget;
    }

    private function assertYearMonth(string $yearMonth): void
    {
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $yearMonth)) {
            throw new InvalidArgumentException('Month must be YYYY-MM.');
        }
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function monthBounds(string $yearMonth): array
    {
        $start = Carbon::createFromFormat('Y-m-d', $yearMonth.'-01')->startOfDay();
        $end = $start->copy()->endOfMonth()->startOfDay();

        return [$start, $end];
    }

    /**
     * @return 'unset'|'ok'|'warning'|'over'
     */
    private function resolveStatus(?float $budgetAmount, float $spent): string
    {
        if ($budgetAmount === null || $budgetAmount <= 0) {
            return 'unset';
        }

        if ($spent > $budgetAmount) {
            return 'over';
        }

        if ($spent >= $budgetAmount * self::WARNING_THRESHOLD) {
            return 'warning';
        }

        return 'ok';
    }

    private function daysRemainingInMonth(Carbon $monthStart, Carbon $monthEnd): ?int
    {
        $today = Carbon::today();

        if ($today->lt($monthStart) || $today->gt($monthEnd)) {
            return null;
        }

        return (int) $today->diffInDays($monthEnd) + 1;
    }

    /**
     * @return list<array{id: string, description: string, categoryName: string, amount: float, date: string}>
     */
    private function topExpensesForMonth(
        User $user,
        Carbon $start,
        Carbon $end,
        int $limit,
    ): array {
        $startStr = $start->toDateString();
        $endStr = $end->toDateString();

        return $user->expenses()
            ->with('category:id,name')
            ->whereBetween('date', [$startStr, $endStr])
            ->orderByDesc('amount')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(function (Expense $expense): array {
                return [
                    'id' => (string) $expense->id,
                    'description' => $expense->note ?? '',
                    'categoryName' => $expense->category?->name ?? '',
                    'amount' => (float) $expense->amount,
                    'date' => $expense->date instanceof Carbon
                        ? $expense->date->format('Y-m-d')
                        : (string) $expense->date,
                ];
            })
            ->values()
            ->all();
    }
}
