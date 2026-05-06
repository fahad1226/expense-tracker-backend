<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Console') — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|jetbrains-mono:400,500" rel="stylesheet" />
    @vite(['resources/css/admin.css', 'resources/js/app.js'])
</head>
<body class="h-full min-h-screen bg-slate-950 font-sans antialiased text-slate-100 selection:bg-violet-500/30">
    {{-- Ambient background --}}
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute -left-40 top-0 size-[520px] rounded-full bg-violet-600/15 blur-[120px]"></div>
        <div class="absolute -right-40 bottom-0 size-[480px] rounded-full bg-fuchsia-600/10 blur-[100px]"></div>
        <div class="absolute inset-0 bg-[linear-gradient(to_bottom,rgba(15,23,42,0.3)_0%,rgba(2,6,23,0.95)_45%,rgb(2,6,23)_100%)]"></div>
    </div>

    <div class="relative flex min-h-full">
        {{-- Desktop sidebar --}}
        <aside class="relative hidden w-64 shrink-0 flex-col border-r border-white/10 bg-slate-950/80 shadow-[4px_0_24px_-4px_rgba(0,0,0,0.5)] backdrop-blur-xl lg:flex">
            <div class="flex h-16 items-center gap-3 border-b border-white/10 px-5">
                <span class="flex size-10 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 via-violet-600 to-fuchsia-600 text-sm font-bold text-white shadow-lg shadow-violet-600/35 ring-1 ring-white/20">E</span>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-violet-300/90">ExpenseTracker</p>
                    <p class="text-sm font-semibold text-white">Admin console</p>
                </div>
            </div>
            <nav class="flex flex-1 flex-col gap-1 p-3">
                <a href="{{ route('admin.dashboard') }}"
                   class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition
                   {{ request()->routeIs('admin.dashboard') ? 'bg-white/[0.08] text-white shadow-inner shadow-black/20 ring-1 ring-violet-500/25' : 'text-slate-400 hover:bg-white/[0.04] hover:text-white' }}">
                    <span class="flex size-8 items-center justify-center rounded-lg bg-white/5 text-violet-300 ring-1 ring-white/10 group-hover:bg-violet-500/10 group-hover:text-violet-200">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    </span>
                    Overview
                </a>
                <a href="{{ route('admin.users.index') }}"
                   class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition
                   {{ request()->routeIs('admin.users.*') ? 'bg-white/[0.08] text-white shadow-inner shadow-black/20 ring-1 ring-violet-500/25' : 'text-slate-400 hover:bg-white/[0.04] hover:text-white' }}">
                    <span class="flex size-8 items-center justify-center rounded-lg bg-white/5 text-violet-300 ring-1 ring-white/10 group-hover:bg-violet-500/10 group-hover:text-violet-200">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </span>
                    Users
                </a>
                <a href="{{ route('admin.tickets.index') }}"
                   class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition
                   {{ request()->routeIs('admin.tickets.*') ? 'bg-white/[0.08] text-white shadow-inner shadow-black/20 ring-1 ring-violet-500/25' : 'text-slate-400 hover:bg-white/[0.04] hover:text-white' }}">
                    <span class="flex size-8 items-center justify-center rounded-lg bg-white/5 text-violet-300 ring-1 ring-white/10 group-hover:bg-violet-500/10 group-hover:text-violet-200">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12h-1M4 12H3m9 8v1m0-18v1"/></svg>
                    </span>
                    Support
                </a>
            </nav>
            <div class="border-t border-white/10 p-3">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-400 transition hover:bg-rose-500/[0.12] hover:text-rose-200">
                        <svg class="size-5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Sign out
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            {{-- Mobile nav --}}
            <div class="sticky top-0 z-20 flex items-center gap-2 border-b border-white/10 bg-slate-950/90 px-3 py-3 backdrop-blur-xl lg:hidden">
                <span class="flex size-8 items-center justify-center rounded-lg bg-gradient-to-br from-violet-500 to-fuchsia-600 text-xs font-bold text-white">E</span>
                <nav class="flex min-w-0 flex-1 gap-1 overflow-x-auto pb-0.5 text-xs font-medium">
                    <a href="{{ route('admin.dashboard') }}" class="shrink-0 rounded-lg px-3 py-2 transition {{ request()->routeIs('admin.dashboard') ? 'bg-white/10 text-white' : 'text-slate-400' }}">Overview</a>
                    <a href="{{ route('admin.users.index') }}" class="shrink-0 rounded-lg px-3 py-2 transition {{ request()->routeIs('admin.users.*') ? 'bg-white/10 text-white' : 'text-slate-400' }}">Users</a>
                    <a href="{{ route('admin.tickets.index') }}" class="shrink-0 rounded-lg px-3 py-2 transition {{ request()->routeIs('admin.tickets.*') ? 'bg-white/10 text-white' : 'text-slate-400' }}">Support</a>
                </nav>
                <form method="POST" action="{{ route('admin.logout') }}" class="shrink-0">@csrf<button class="rounded-lg p-2 text-slate-500 hover:bg-white/5 hover:text-rose-300" title="Sign out"><svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg></button></form>
            </div>

            <header class="relative z-10 flex h-14 items-center justify-between border-b border-white/10 bg-slate-950/70 px-4 backdrop-blur-md lg:px-8">
                <div class="flex items-center gap-3">
                    <h1 class="text-sm font-semibold text-white lg:text-base">@yield('heading', 'Dashboard')</h1>
                </div>
                <div class="flex items-center gap-3">
                    <span class="hidden max-w-[200px] truncate rounded-lg border border-white/10 bg-white/[0.03] px-2.5 py-1 text-[11px] text-slate-400 font-mono sm:inline-block">{{ auth()->user()?->email }}</span>
                </div>
            </header>

            <main class="relative z-10 flex-1 px-4 py-6 lg:px-8 lg:py-8">
                @if (session('status'))
                    <div class="mb-6 rounded-xl border border-emerald-400/25 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100 shadow-lg shadow-emerald-900/20">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-xl border border-rose-400/25 bg-rose-500/10 px-4 py-3 text-sm text-rose-100 shadow-lg shadow-rose-900/20">
                        <ul class="list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
