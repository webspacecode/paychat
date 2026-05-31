@extends('layouts.app')

@section('title', 'Login | PayChat')

@section('content')
    <div class="mx-auto max-w-md">
        <div class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-semibold text-slate-950">Login</h1>
            <p class="mt-2 text-sm text-slate-600">Use the same page for the private master account and tenant accounts.</p>

            <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                        class="mt-2 w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-slate-950 focus:ring-1 focus:ring-slate-950">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <input id="password" name="password" type="password" required
                        class="mt-2 w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-slate-950 focus:ring-1 focus:ring-slate-950">
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="remember" value="1" class="rounded border-slate-300">
                    Remember me
                </label>

                <button class="w-full rounded-md bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">
                    Login
                </button>
            </form>
        </div>
    </div>
@endsection
