@extends('admin.layout')

@section('title', 'Ticket #' . $ticket->id)
@section('heading', 'Support ticket')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="rounded-2xl border border-white/10 bg-slate-900/40 p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Subject</p>
                    <h2 class="mt-1 text-xl font-semibold text-white">{{ $ticket->subject }}</h2>
                    <p class="mt-2 text-sm text-slate-400">{{ $ticket->reply_email }} · {{ $ticket->created_at->toDayDateTimeString() }}</p>
                    @if ($ticket->user)
                        <p class="mt-1 text-xs text-violet-300/90">Registered: {{ $ticket->user->name }} ({{ $ticket->user->email }})</p>
                    @endif
                </div>
                <span class="rounded-full bg-amber-500/20 px-3 py-1 text-xs font-semibold text-amber-200">{{ $statusLabels[$ticket->status] ?? $ticket->status }}</span>
            </div>

            <div class="mt-6 rounded-xl border border-white/5 bg-slate-950/50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Message</p>
                <div class="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-slate-200">{{ $ticket->message }}</div>
            </div>

            <p class="mt-4 text-xs text-slate-600">Category: <span class="font-mono text-slate-400">{{ $ticket->category }}</span></p>
        </div>

        <div class="rounded-2xl border border-white/10 bg-slate-900/40 p-6">
            <h3 class="text-sm font-semibold text-white">Update ticket</h3>
            <form method="POST" action="{{ route('admin.tickets.update', $ticket) }}" class="mt-4 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="status" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-400">Status</label>
                    <select id="status" name="status" required
                        class="w-full rounded-xl border border-white/10 bg-slate-950/80 px-4 py-2.5 text-sm text-white focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                        @foreach ($statusLabels as $key => $label)
                            <option value="{{ $key }}" @selected(old('status', $ticket->status) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="admin_notes" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-400">Internal notes</label>
                    <textarea id="admin_notes" name="admin_notes" rows="4" placeholder="Private notes (not shown to the user)"
                        class="w-full rounded-xl border border-white/10 bg-slate-950/80 px-4 py-2.5 text-sm text-white placeholder:text-slate-600 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">{{ old('admin_notes', $ticket->admin_notes) }}</textarea>
                </div>

                <button type="submit" class="rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-violet-500">
                    Save
                </button>
            </form>
        </div>
    </div>
@endsection
