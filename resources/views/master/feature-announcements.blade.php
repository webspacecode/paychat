@extends('layouts.app')

@section('title', 'Feature Announcements | PayChat')

@section('content')
    <div class="grid gap-6 lg:grid-cols-[220px_1fr]">
        <aside class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <nav class="grid gap-2 text-sm font-semibold">
                <a href="{{ route('master.dashboard') }}" class="rounded-md px-3 py-2 text-slate-600 hover:bg-slate-50">Dashboard</a>
                <a href="{{ route('master.features.index') }}" class="rounded-md px-3 py-2 text-slate-600 hover:bg-slate-50">Features</a>
                <a href="{{ route('master.plans.index') }}" class="rounded-md px-3 py-2 text-slate-600 hover:bg-slate-50">Plans</a>
                <a href="{{ route('master.feature-announcements.index') }}" class="rounded-md bg-slate-950 px-3 py-2 text-white">Feature Announcements</a>
            </nav>
        </aside>

        <div class="space-y-6">
            @if(session('status'))
                <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    <div class="font-semibold">Please fix the highlighted details.</div>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide text-blue-600">Master announcements</p>
                        <h1 class="mt-1 text-2xl font-black text-slate-950">Feature Announcements</h1>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">{{ $announcements->count() }} announcements</span>
                </div>
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-black text-slate-950">New Announcement</h2>
                <form method="POST" action="{{ route('master.feature-announcements.store') }}" class="mt-4 grid gap-4 md:grid-cols-2">
                    @csrf
                    @include('master.partials.feature-announcement-form', ['announcement' => null])
                    <div class="md:col-span-2">
                        <button class="rounded-md bg-slate-950 px-4 py-2 text-sm font-black text-white">Publish announcement</button>
                    </div>
                </form>
            </section>

            <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 p-5">
                    <h2 class="text-lg font-black text-slate-950">Existing Announcements</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-[1120px] divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-black uppercase text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Announcement</th>
                                <th class="px-4 py-3">Action</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Published</th>
                                <th class="px-4 py-3">Seen</th>
                                <th class="px-4 py-3">Save</th>
                                <th class="px-4 py-3">Activate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($announcements as $announcement)
                                <tr>
                                    <form method="POST" action="{{ route('master.feature-announcements.update', $announcement) }}">
                                        @csrf
                                        @method('PATCH')
                                        <td class="px-4 py-3 align-top">
                                            <input name="title" value="{{ old("announcements.{$announcement->id}.title", $announcement->title) }}" class="w-full rounded-md border border-slate-200 px-3 py-2 font-bold" required>
                                            <div class="mt-2 break-all text-[11px] font-bold text-slate-400">{{ $announcement->key }}</div>
                                            <textarea name="description" rows="4" class="mt-2 w-full rounded-md border border-slate-200 px-3 py-2 text-xs" required>{{ old("announcements.{$announcement->id}.description", $announcement->description) }}</textarea>
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            <input name="action_label" value="{{ old("announcements.{$announcement->id}.action_label", $announcement->action_label) }}" class="w-full rounded-md border border-slate-200 px-3 py-2 text-xs font-bold" placeholder="Button label">
                                            <input name="action_url" value="{{ old("announcements.{$announcement->id}.action_url", $announcement->action_url) }}" class="mt-2 w-full rounded-md border border-slate-200 px-3 py-2 text-xs font-bold" placeholder="/settings">
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            <label class="flex items-center gap-2 font-bold">
                                                <input type="checkbox" name="is_active" value="1" @checked($announcement->is_active) class="rounded border-slate-300">
                                                Active
                                            </label>
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            <input type="datetime-local" name="published_at" value="{{ old("announcements.{$announcement->id}.published_at", $announcement->published_at?->timezone(config('app.master_timezone', 'Asia/Kolkata'))->format('Y-m-d\TH:i')) }}" class="w-full rounded-md border border-slate-200 px-3 py-2 text-xs font-bold">
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700">{{ $announcement->user_feature_announcements_count }}</span>
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            <button class="rounded-md border border-slate-300 px-3 py-2 text-xs font-black text-slate-700 hover:bg-slate-50">Save</button>
                                        </td>
                                    </form>
                                    <td class="px-4 py-3 align-top">
                                        <form method="POST" action="{{ route('master.feature-announcements.toggle', $announcement) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="is_active" value="{{ $announcement->is_active ? 0 : 1 }}">
                                            <button class="rounded-md border px-3 py-2 text-xs font-black {{ $announcement->is_active ? 'border-amber-200 bg-amber-50 text-amber-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700' }}">
                                                {{ $announcement->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500">No announcements yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
@endsection
