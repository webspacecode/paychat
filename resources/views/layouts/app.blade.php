<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>@yield('title', 'PayChat')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f6f7fb] text-slate-900">
    <header class="border-b border-slate-200/80 bg-white/95 shadow-sm">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="flex items-center">
                <img src="{{ asset('color-paychat-logo-main.svg') }}" alt="PayChat" class="h-9 w-auto">
            </a>

            <nav class="flex items-center gap-3 text-sm">
                @auth
                    @if(auth()->user()->isMaster())
                        <a href="{{ route('master.dashboard') }}" class="font-medium text-slate-700 hover:text-slate-950">Master</a>
                    @else
                        <a href="{{ route('tenant.dashboard') }}" class="font-medium text-slate-700 hover:text-slate-950">Dashboard</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded-md border border-slate-300 px-3 py-2 font-medium text-slate-700 hover:bg-slate-100">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="font-medium text-slate-700 hover:text-slate-950">Login</a>
                    <a href="{{ route('register') }}" class="rounded-md bg-slate-950 px-3 py-2 font-medium text-white hover:bg-slate-800">Register</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if(session('status'))
            <div class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
                <p class="font-semibold">Please fix the highlighted details.</p>
                <ul class="mt-2 list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
