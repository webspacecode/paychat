@extends('layouts.app')

@section('title', 'Feature Catalog | PayChat')

@section('content')
    <div class="grid gap-6 lg:grid-cols-[220px_1fr]">
        <aside class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <nav class="grid gap-2 text-sm font-semibold">
                <a href="{{ route('master.dashboard') }}" class="rounded-md px-3 py-2 text-slate-600 hover:bg-slate-50">Dashboard</a>
                <a href="{{ route('master.features.index') }}" class="rounded-md bg-slate-950 px-3 py-2 text-white">Features</a>
                <a href="{{ route('master.plans.index') }}" class="rounded-md px-3 py-2 text-slate-600 hover:bg-slate-50">Plans</a>
            </nav>
        </aside>

        <div class="space-y-6">
            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide text-blue-600">Master catalog</p>
                        <h1 class="mt-1 text-2xl font-black text-slate-950">Feature Catalog</h1>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">{{ $features->count() }} features</span>
                </div>
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-black text-slate-950">Add Feature</h2>
                <form method="POST" action="{{ route('master.features.store') }}" class="mt-4 grid gap-4 md:grid-cols-2">
                    @csrf
                    <label class="grid gap-1 text-sm font-bold text-slate-700">Key
                        <input name="key" value="{{ old('key') }}" class="rounded-md border border-slate-200 px-3 py-2" placeholder="feature_key" required>
                    </label>
                    <label class="grid gap-1 text-sm font-bold text-slate-700">Name
                        <input name="name" value="{{ old('name') }}" class="rounded-md border border-slate-200 px-3 py-2" required>
                    </label>
                    <label class="grid gap-1 text-sm font-bold text-slate-700">Category
                        <input name="category" value="{{ old('category', 'core') }}" class="rounded-md border border-slate-200 px-3 py-2" required>
                    </label>
                    <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300">
                        Active
                    </label>
                    <label class="grid gap-1 text-sm font-bold text-slate-700 md:col-span-2">Description
                        <textarea name="description" rows="3" class="rounded-md border border-slate-200 px-3 py-2">{{ old('description') }}</textarea>
                    </label>
                    <div class="md:col-span-2">
                        <button class="rounded-md bg-slate-950 px-4 py-2 text-sm font-black text-white">Add feature</button>
                    </div>
                </form>
            </section>

            <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 p-5">
                    <h2 class="text-lg font-black text-slate-950">Existing Features</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-black uppercase text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Feature</th>
                                <th class="px-4 py-3">Category</th>
                                <th class="px-4 py-3">Source</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Update</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($features as $feature)
                                <tr>
                                    <form method="POST" action="{{ route('master.features.update', $feature) }}">
                                        @csrf
                                        @method('PATCH')
                                        <td class="px-4 py-3">
                                            <input name="name" value="{{ old("features.{$feature->id}.name", $feature->name) }}" class="w-full rounded-md border border-slate-200 px-3 py-2 font-bold">
                                            <input name="key" value="{{ old("features.{$feature->id}.key", $feature->key) }}" class="mt-2 w-full rounded-md border border-slate-200 px-3 py-2 text-xs font-bold text-slate-500">
                                            <textarea name="description" rows="2" class="mt-2 w-full rounded-md border border-slate-200 px-3 py-2 text-xs">{{ old("features.{$feature->id}.description", $feature->description) }}</textarea>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input name="category" value="{{ old("features.{$feature->id}.category", $feature->category) }}" class="w-full rounded-md border border-slate-200 px-3 py-2">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input name="source" value="{{ old("features.{$feature->id}.source", $feature->source) }}" class="w-full rounded-md border border-slate-200 px-3 py-2">
                                        </td>
                                        <td class="px-4 py-3">
                                            <label class="flex items-center gap-2 font-bold">
                                                <input type="checkbox" name="is_active" value="1" @checked($feature->is_active) class="rounded border-slate-300">
                                                Active
                                            </label>
                                        </td>
                                        <td class="px-4 py-3">
                                            <button class="rounded-md border border-slate-300 px-3 py-2 text-xs font-black text-slate-700 hover:bg-slate-50">Save</button>
                                        </td>
                                    </form>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
@endsection
