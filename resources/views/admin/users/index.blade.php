@extends('admin.layout')

@section('title', 'Users')
@section('heading', 'Users')

@section('content')
    <div class="overflow-hidden rounded-2xl border border-white/10 bg-slate-900/40 shadow-xl shadow-black/30 ring-1 ring-white/5 backdrop-blur-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-white/10 text-left text-sm">
                <thead class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4">Currency</th>
                        <th class="px-6 py-4">Role</th>
                        <th class="px-6 py-4">Joined</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach ($users as $user)
                        <tr class="hover:bg-white/[0.02]">
                            <td class="px-6 py-4">
                                <p class="font-medium text-white">{{ $user->name }}</p>
                                <p class="text-xs text-slate-500">{{ $user->email }}</p>
                            </td>
                            <td class="px-6 py-4 font-mono text-slate-300">{{ $user->currency ?? 'BDT' }}</td>
                            <td class="px-6 py-4">
                                @if ($user->is_admin)
                                    <span class="rounded-full bg-violet-500/20 px-2 py-0.5 text-xs font-medium text-violet-300">Admin</span>
                                @else
                                    <span class="text-slate-400">User</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-400">{{ $user->created_at?->format('M j, Y') }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-violet-400 hover:text-violet-300">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-white/10 px-6 py-4">
            {{ $users->links('vendor.pagination.admin') }}
        </div>
    </div>
@endsection
