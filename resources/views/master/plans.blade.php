@extends('layouts.app')

@section('title', 'Pricing Plans | PayChat')

@section('content')
    <div class="grid gap-6 lg:grid-cols-[220px_1fr]">
        <aside class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <nav class="grid gap-2 text-sm font-semibold">
                <a href="{{ route('master.dashboard') }}" class="rounded-md px-3 py-2 text-slate-600 hover:bg-slate-50">Dashboard</a>
                <a href="{{ route('master.features.index') }}" class="rounded-md px-3 py-2 text-slate-600 hover:bg-slate-50">Features</a>
                <a href="{{ route('master.plans.index') }}" class="rounded-md bg-slate-950 px-3 py-2 text-white">Plans</a>
            </nav>
        </aside>

        <div class="space-y-6">
            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wide text-blue-600">Master pricing</p>
                <h1 class="mt-1 text-2xl font-black text-slate-950">Pricing Plans</h1>
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-black text-slate-950">Add Plan</h2>
                <form method="POST" action="{{ route('master.plans.store') }}" class="mt-4 grid gap-4 md:grid-cols-2">
                    @csrf
                    @include('master.partials.plan-form', ['plan' => null, 'features' => $features])
                    <div class="md:col-span-2">
                        <button class="rounded-md bg-slate-950 px-4 py-2 text-sm font-black text-white">Add plan</button>
                    </div>
                </form>
            </section>

            <div class="grid gap-5">
                @foreach($plans as $plan)
                    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <form method="POST" action="{{ route('master.plans.update', $plan) }}" class="grid gap-4 md:grid-cols-2">
                            @csrf
                            @method('PATCH')
                            <div class="md:col-span-2 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h2 class="text-lg font-black text-slate-950">{{ $plan->name }}</h2>
                                    <p class="text-xs font-bold text-slate-500">{{ $plan->key }} · {{ $plan->currency }}</p>
                                </div>
                                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700">{{ $plan->features->count() }} features</span>
                            </div>
                            @include('master.partials.plan-form', ['plan' => $plan, 'features' => $features])
                            <div class="md:col-span-2">
                                <button class="rounded-md border border-slate-300 px-4 py-2 text-sm font-black text-slate-700 hover:bg-slate-50">Save plan</button>
                            </div>
                        </form>
                    </section>
                @endforeach
            </div>
        </div>
    </div>
@endsection
