@extends('admin.layout')

@section('title', 'Edit user')
@section('heading', 'Edit user')

@section('content')
    <div class="mx-auto max-w-2xl rounded-2xl border border-white/10 bg-slate-900/40 p-8">
        <p class="mb-6 text-sm text-slate-400">Update account details. Changes apply immediately to the product API.</p>

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-400">Name</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required
                    class="w-full rounded-xl border border-white/10 bg-slate-950/80 px-4 py-2.5 text-sm text-white focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
            </div>

            <div>
                <label for="email" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-400">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                    class="w-full rounded-xl border border-white/10 bg-slate-950/80 px-4 py-2.5 text-sm text-white focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
            </div>

            <div>
                <label for="currency" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-400">Currency</label>
                <select id="currency" name="currency" required
                    class="w-full rounded-xl border border-white/10 bg-slate-950/80 px-4 py-2.5 text-sm text-white focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                    @foreach ($currencies as $c)
                        <option value="{{ $c['code'] }}" @selected(old('currency', $user->currency ?? 'BDT') === $c['code'])>{{ $c['label'] }} ({{ $c['code'] }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-400">New password</label>
                <input id="password" name="password" type="password" autocomplete="new-password"
                    class="w-full rounded-xl border border-white/10 bg-slate-950/80 px-4 py-2.5 text-sm text-white focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500" placeholder="Leave blank to keep current">
                <p class="mt-1 text-xs text-slate-500">Min. 8 characters if set.</p>
            </div>

            <div>
                <label for="password_confirmation" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-400">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                    class="w-full rounded-xl border border-white/10 bg-slate-950/80 px-4 py-2.5 text-sm text-white focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
            </div>

            <div class="flex items-center gap-3 rounded-xl border border-white/10 bg-slate-950/50 px-4 py-3">
                <input id="is_admin" name="is_admin" type="checkbox" value="1" @checked(old('is_admin', $user->is_admin)) class="size-4 rounded border-white/20 bg-slate-950 text-violet-600 focus:ring-violet-500/50">
                <label for="is_admin" class="text-sm text-slate-300">Administrator (console access)</label>
            </div>

            <div class="flex flex-wrap items-center gap-4 pt-2">
                <button type="submit" class="rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-violet-500/20 hover:bg-violet-500">
                    Save changes
                </button>
                <a href="{{ route('admin.users.index') }}" class="text-sm text-slate-400 hover:text-white">Cancel</a>
            </div>
        </form>

        @if ($user->id !== auth()->id())
            <div class="mt-10 border-t border-white/10 pt-8">
                <h3 class="text-sm font-semibold text-rose-300">Danger zone</h3>
                <p class="mt-2 text-xs text-slate-500">Delete this user and all related expenses and categories (cascade).</p>
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="mt-4" onsubmit="return confirm('Delete this user permanently?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-xl border border-rose-500/50 bg-rose-500/10 px-4 py-2 text-sm font-medium text-rose-300 hover:bg-rose-500/20">
                        Delete user
                    </button>
                </form>
            </div>
        @endif
    </div>
@endsection
