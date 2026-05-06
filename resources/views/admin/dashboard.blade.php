@extends('admin.layout')

@section('title', 'Overview')
@section('heading', 'Overview')

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-violet-400/20 bg-gradient-to-br from-violet-600/25 via-violet-900/10 to-slate-900/80 p-6 shadow-xl shadow-violet-950/40 ring-1 ring-white/5">
            <p class="text-xs font-semibold uppercase tracking-wider text-violet-300/80">App users</p>
            <p class="mt-2 text-4xl font-bold tabular-nums text-white">{{ number_format($userCount) }}</p>
            <p class="mt-1 text-xs text-slate-400">Excluding admin accounts</p>
        </div>
        <div class="rounded-2xl border border-white/10 bg-slate-900/50 p-6 shadow-lg shadow-black/20 ring-1 ring-white/5 backdrop-blur-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total accounts</p>
            <p class="mt-2 text-4xl font-bold tabular-nums text-white">{{ number_format($totalUsers) }}</p>
            <p class="mt-1 text-xs text-slate-400">Including {{ $adminCount }} admin(s)</p>
        </div>
        <div class="rounded-2xl border border-white/10 bg-slate-900/50 p-6 shadow-lg shadow-black/20 ring-1 ring-white/5 backdrop-blur-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-amber-300/80">Open support</p>
            <p class="mt-2 text-4xl font-bold tabular-nums text-white">{{ number_format($openTickets) }}</p>
            <p class="mt-1 text-xs text-slate-400">Open + in progress</p>
        </div>
        <div class="rounded-2xl border border-white/10 bg-slate-900/50 p-6 shadow-lg shadow-black/20 ring-1 ring-white/5 backdrop-blur-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tickets (7 days)</p>
            <p class="mt-2 text-4xl font-bold tabular-nums text-white">{{ number_format($ticketsThisWeek) }}</p>
            <p class="mt-1 text-xs text-slate-400">New submissions</p>
        </div>
    </div>

    <div class="mt-8 grid gap-8 lg:grid-cols-2">
        <div class="rounded-2xl border border-white/10 bg-slate-900/40 p-6 shadow-lg shadow-black/20 ring-1 ring-white/5 backdrop-blur-sm">
            <h2 class="text-sm font-semibold text-white">Activity</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between border-b border-white/5 py-2">
                    <dt class="text-slate-400">Expense rows</dt>
                    <dd class="font-mono text-white">{{ number_format($expenseCount) }}</dd>
                </div>
                <div class="flex justify-between py-2">
                    <dt class="text-slate-400">Support backlog</dt>
                    <dd class="font-mono text-white">{{ $openTickets > 0 ? 'Needs attention' : 'Clear' }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-2xl border border-white/10 bg-slate-900/40 p-6 shadow-lg shadow-black/20 ring-1 ring-white/5 backdrop-blur-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-white">Recent tickets</h2>
                <a href="{{ route('admin.tickets.index') }}" class="text-xs font-medium text-violet-400 hover:text-violet-300">View all</a>
            </div>
            <ul class="mt-4 divide-y divide-white/5">
                @forelse ($recentTickets as $t)
                    <li class="flex items-start justify-between gap-3 py-3">
                        <div class="min-w-0">
                            <a href="{{ route('admin.tickets.show', $t) }}" class="truncate text-sm font-medium text-white hover:text-violet-300">{{ $t->subject }}</a>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $t->reply_email }} · {{ $t->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide
                            @class([
                                'bg-emerald-500/20 text-emerald-300' => $t->status === \App\Models\SupportTicket::STATUS_RESOLVED,
                                'bg-slate-500/20 text-slate-300' => $t->status === \App\Models\SupportTicket::STATUS_CLOSED,
                                'bg-amber-500/20 text-amber-300' => in_array($t->status, [\App\Models\SupportTicket::STATUS_OPEN, \App\Models\SupportTicket::STATUS_IN_PROGRESS], true),
                            ])">{{ str_replace('_', ' ', $t->status) }}</span>
                    </li>
                @empty
                    <li class="py-8 text-center text-sm text-slate-500">No support tickets yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
