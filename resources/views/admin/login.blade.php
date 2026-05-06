<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin sign in — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|jetbrains-mono:400,500" rel="stylesheet" />
    @vite(['resources/css/admin.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full items-center justify-center bg-slate-950 px-4 font-sans antialiased selection:bg-violet-500/30">
    <div class="pointer-events-none fixed inset-0">
        <div class="absolute left-1/2 top-1/2 size-[600px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-violet-600/20 blur-[140px]"></div>
        <div class="absolute bottom-0 right-0 size-[400px] rounded-full bg-fuchsia-600/10 blur-[100px]"></div>
    </div>

    <div class="relative w-full max-w-md">
        <div class="mb-10 text-center">
            <div class="mx-auto mb-5 flex size-16 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 via-violet-600 to-fuchsia-600 text-xl font-bold text-white shadow-2xl shadow-violet-600/40 ring-1 ring-white/20">E</div>
            <h1 class="text-2xl font-bold tracking-tight text-white">ExpenseTracker</h1>
            <p class="mt-2 text-sm text-slate-400">Administrator console — sign in to continue</p>
        </div>

        <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-8 shadow-2xl shadow-black/50 backdrop-blur-xl ring-1 ring-white/5">
            <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-400">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                        class="w-full rounded-xl border border-white/10 bg-slate-950/80 px-4 py-3 text-sm text-white shadow-inner shadow-black/20 placeholder:text-slate-600 focus:border-violet-500/50 focus:outline-none focus:ring-2 focus:ring-violet-500/30" placeholder="admin@example.com" autocomplete="username">
                </div>
                <div>
                    <label for="password" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-400">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                        class="w-full rounded-xl border border-white/10 bg-slate-950/80 px-4 py-3 text-sm text-white shadow-inner shadow-black/20 focus:border-violet-500/50 focus:outline-none focus:ring-2 focus:ring-violet-500/30">
                </div>
                <label class="flex cursor-pointer items-center gap-2.5 text-sm text-slate-400">
                    <input type="checkbox" name="remember" value="1" class="size-4 rounded border-white/20 bg-slate-950 text-violet-500 focus:ring-violet-500/40 focus:ring-offset-0">
                    Remember this device
                </label>

                @if ($errors->any())
                    <div class="rounded-xl border border-rose-400/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-100">
                        {{ $errors->first() }}
                    </div>
                @endif

                <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-violet-600 to-fuchsia-600 py-3 text-sm font-semibold text-white shadow-lg shadow-violet-600/25 transition hover:from-violet-500 hover:to-fuchsia-500 hover:shadow-violet-500/35 active:scale-[0.99]">
                    Sign in
                </button>
            </form>
        </div>
        <p class="mt-8 text-center text-xs text-slate-600">Restricted access · Authorized personnel only</p>
    </div>
</body>
</html>
