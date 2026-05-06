@extends('admin.layout')

@section('title', 'Support tickets')
@section('heading', 'Support tickets')

@section('content')
    <div class="overflow-hidden rounded-2xl border border-white/10 bg-slate-900/40 shadow-xl shadow-black/30 ring-1 ring-white/5 backdrop-blur-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-white/10 text-left text-sm">
                <thead class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-6 py-4">Subject</th>
                        <th class="px-6 py-4">From</th>
                        <th class="px-6 py-4">Linked user</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Created</th>
                        <th class="px-6 py-4 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach ($tickets as $ticket)
                        <tr class="hover:bg-white/[0.02]">
                            <td class="max-w-xs truncate px-6 py-4 font-medium text-white">{{ $ticket->subject }}</td>
                            <td class="px-6 py-4 text-slate-400">{{ $ticket->reply_email }}</td>
                            <td class="px-6 py-4 text-slate-400">
                                @if ($ticket->user)
                                    {{ $ticket->user->email }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-medium text-slate-200">{{ $statusLabels[$ticket->status] ?? $ticket->status }}</span>
                            </td>
                            <td class="px-6 py-4 text-slate-500">{{ $ticket->created_at->format('M j, H:i') }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.tickets.show', $ticket) }}" class="text-violet-400 hover:text-violet-300">Open</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-white/10 px-6 py-4">
            {{ $tickets->links('vendor.pagination.admin') }}
        </div>
    </div>
@endsection
