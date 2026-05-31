@extends('layouts.app')

@section('title', 'Register Tenant | PayChat')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-semibold text-slate-950">Register Tenant</h1>
            <p class="mt-2 text-sm text-slate-600">Create a shop account. The owner can log in immediately while tenant setup runs.</p>

            <form method="POST" action="{{ route('register') }}" class="mt-6 grid gap-5 sm:grid-cols-2">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700">Business name</label>
                    <input id="name" name="name" value="{{ old('name') }}" required
                        class="mt-2 w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-slate-950 focus:ring-1 focus:ring-slate-950">
                </div>

                <div>
                    <label for="slug" class="block text-sm font-medium text-slate-700">Tenant slug</label>
                    <input id="slug" name="slug" value="{{ old('slug') }}" required
                        class="mt-2 w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-slate-950 focus:ring-1 focus:ring-slate-950">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Owner email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required
                        class="mt-2 w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-slate-950 focus:ring-1 focus:ring-slate-950">
                </div>

                <div>
                    <label for="industry" class="block text-sm font-medium text-slate-700">Industry</label>
                    <input id="industry" name="industry" value="{{ old('industry') }}" required
                        class="mt-2 w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-slate-950 focus:ring-1 focus:ring-slate-950">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <input id="password" name="password" type="password" required
                        class="mt-2 w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-slate-950 focus:ring-1 focus:ring-slate-950">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                        class="mt-2 w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-slate-950 focus:ring-1 focus:ring-slate-950">
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-slate-700">Phone</label>
                    <input id="phone" name="phone" value="{{ old('phone') }}"
                        class="mt-2 w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-slate-950 focus:ring-1 focus:ring-slate-950">
                </div>

                <div>
                    <label for="gst_number" class="block text-sm font-medium text-slate-700">GST number</label>
                    <input id="gst_number" name="gst_number" value="{{ old('gst_number') }}"
                        class="mt-2 w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-slate-950 focus:ring-1 focus:ring-slate-950">
                </div>

                <div class="sm:col-span-2">
                    <label for="address" class="block text-sm font-medium text-slate-700">Address</label>
                    <textarea id="address" name="address" rows="3"
                        class="mt-2 w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-slate-950 focus:ring-1 focus:ring-slate-950">{{ old('address') }}</textarea>
                </div>

                <div class="sm:col-span-2 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="is_gst_enabled" value="1" class="rounded border-slate-300" @checked(old('is_gst_enabled'))>
                        GST enabled
                    </label>

                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="terms_accepted" value="1" class="rounded border-slate-300" required>
                        I accept the terms
                    </label>
                </div>

                <div class="sm:col-span-2">
                    <button class="w-full rounded-md bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">
                        Create Tenant Account
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
