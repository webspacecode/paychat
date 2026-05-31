@extends('layouts.app')

@section('title', 'Tenant Dashboard | PayChat')

@section('content')
    <div class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-950">Tenant Dashboard</h1>
                <p class="mt-2 text-sm text-slate-600">This dashboard is a placeholder for now.</p>
            </div>
            @if($tenant?->onboarding)
                <span class="rounded-md bg-slate-100 px-3 py-1 text-sm font-medium text-slate-700">
                    Setup: {{ $tenant->onboarding->status }}
                </span>
            @endif
        </div>

        @if($tenant)
            <dl class="mt-6 grid gap-4 sm:grid-cols-3">
                <div class="rounded-md border border-slate-200 p-4">
                    <dt class="text-xs font-semibold uppercase text-slate-500">Tenant</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $tenant->name }}</dd>
                </div>
                <div class="rounded-md border border-slate-200 p-4">
                    <dt class="text-xs font-semibold uppercase text-slate-500">Slug</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $tenant->slug }}</dd>
                </div>
                <div class="rounded-md border border-slate-200 p-4">
                    <dt class="text-xs font-semibold uppercase text-slate-500">Industry</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $tenant->industry }}</dd>
                </div>
            </dl>
        @endif
    </div>
@endsection
