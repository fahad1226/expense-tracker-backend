<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        $userCount = User::query()->where('is_admin', false)->count();
        $adminCount = User::query()->where('is_admin', true)->count();
        $totalUsers = User::query()->count();

        $openTickets = SupportTicket::query()
            ->whereIn('status', [SupportTicket::STATUS_OPEN, SupportTicket::STATUS_IN_PROGRESS])
            ->count();

        $ticketsThisWeek = SupportTicket::query()
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $recentTickets = SupportTicket::query()
            ->with('user:id,name,email')
            ->latest()
            ->limit(6)
            ->get();

        $expenseCount = Expense::query()->count();

        return view('admin.dashboard', compact(
            'userCount',
            'adminCount',
            'totalUsers',
            'openTickets',
            'ticketsThisWeek',
            'recentTickets',
            'expenseCount',
        ));
    }
}
