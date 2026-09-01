@extends('layouts.app')

@section('title', 'Master Dashboard | PayChat')

@section('content')
    <style>
        [data-master-dashboard] {
            --master-ink: #111827;
            --master-muted: #667085;
            --master-line: rgba(148, 163, 184, 0.18);
            --master-panel: rgba(255, 255, 255, 0.92);
            --master-blue: #2157ff;
            --master-blue-dark: #173fc0;
            --master-shadow: 0 20px 60px rgba(15, 23, 42, 0.08);
            --master-shadow-strong: 0 28px 90px rgba(15, 23, 42, 0.18);
        }

        [data-master-dashboard] .master-hero {
            background:
                radial-gradient(circle at 12% 15%, rgba(255, 255, 255, 0.18), transparent 32%),
                linear-gradient(135deg, #07111f 0%, #172554 52%, #2157ff 100%);
            box-shadow: var(--master-shadow-strong);
        }

        [data-master-dashboard] .master-panel {
            border: 1px solid var(--master-line);
            background: var(--master-panel);
            box-shadow: var(--master-shadow);
        }

        [data-master-dashboard] .master-section-head {
            border-bottom: 0;
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.9), rgba(255, 255, 255, 0.7));
        }

        [data-master-dashboard] table {
            border-collapse: separate;
            border-spacing: 0;
        }

        [data-master-dashboard] thead {
            background: #f8fafc;
        }

        [data-master-dashboard] tbody {
            background: transparent;
        }

        [data-master-dashboard] tbody tr {
            box-shadow: inset 0 -1px 0 rgba(226, 232, 240, 0.65);
        }

        [data-master-dashboard] tbody tr:hover {
            background: #f8fbff;
        }

        [data-master-dashboard] .master-action {
            display: inline-flex;
            min-height: 40px;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            border: 1px solid rgba(33, 87, 255, 0.18);
            border-radius: 8px;
            background: #eef4ff;
            color: #173fc0;
            font-weight: 700;
            box-shadow: 0 10px 28px rgba(33, 87, 255, 0.12);
            transition: transform 160ms ease, box-shadow 160ms ease, background 160ms ease;
        }

        [data-master-dashboard] .master-action:hover,
        [data-master-dashboard] .master-action:focus-visible {
            background: #dfe9ff;
            color: #0f2f91;
            box-shadow: 0 14px 34px rgba(33, 87, 255, 0.18);
            transform: translateY(-1px);
        }

        [data-master-dashboard] .master-action-primary {
            border-color: transparent;
            background: linear-gradient(135deg, var(--master-blue), var(--master-blue-dark));
            color: #fff;
        }

        [data-master-dashboard] .master-action-primary:hover,
        [data-master-dashboard] .master-action-primary:focus-visible {
            background: linear-gradient(135deg, #1748dc, #0f2f91);
            color: #fff;
        }

        [data-master-dashboard] .master-action-dark {
            border-color: rgba(255, 255, 255, 0.18);
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
            box-shadow: none;
        }

        [data-master-dashboard] .master-action-dark:hover,
        [data-master-dashboard] .master-action-dark:focus-visible {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        [data-master-dashboard] input,
        [data-master-dashboard] select,
        [data-master-dashboard] textarea {
            background-color: #fff;
        }

        [data-master-dashboard] .master-count-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            background: #eef4ff;
            padding: 0.25rem 0.65rem;
            font-size: 0.75rem;
            font-weight: 800;
            color: #173fc0;
        }

        [data-master-dashboard] .logs-manager-modal {
            position: fixed;
            inset: 0;
            z-index: 60;
            background: #f6f8fb;
        }

        [data-master-dashboard] .logs-manager-modal.hidden {
            display: none;
        }

        [data-master-dashboard] .logs-manager-modal:not(.hidden) {
            display: block;
        }

        [data-master-dashboard] .logs-manager-shell {
            display: flex;
            width: 100%;
            height: 100vh;
            height: 100dvh;
            flex-direction: column;
            overflow: hidden;
            background: #f6f8fb;
        }

        [data-master-dashboard] .logs-manager-header {
            display: flex;
            flex-shrink: 0;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            border-bottom: 1px solid rgba(15, 23, 42, 0.12);
            background: #0f172a;
            padding: 1rem 1.25rem;
            color: #fff;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.16);
        }

        [data-master-dashboard] .logs-manager-title {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 850;
            letter-spacing: 0;
        }

        [data-master-dashboard] .logs-manager-tenant {
            margin-top: 0.2rem;
            color: #cbd5e1;
            font-size: 0.85rem;
            font-weight: 650;
        }

        [data-master-dashboard] .logs-close-btn {
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 800;
            min-height: 40px;
            padding: 0.45rem 0.85rem;
        }

        [data-master-dashboard] .logs-close-btn:hover,
        [data-master-dashboard] .logs-close-btn:focus-visible {
            background: rgba(255, 255, 255, 0.18);
            outline: none;
        }

        [data-master-dashboard] .logs-filter-bar {
            flex-shrink: 0;
            border-bottom: 1px solid #e2e8f0;
            background: #fff;
            padding: 1rem 1.25rem;
        }

        [data-master-dashboard] .logs-filter-grid {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 0.8rem;
        }

        [data-master-dashboard] .logs-field {
            grid-column: span 2;
            color: #64748b;
            font-size: 0.7rem;
            font-weight: 850;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        [data-master-dashboard] .logs-field-wide {
            grid-column: span 3;
        }

        [data-master-dashboard] .logs-field-support {
            grid-column: span 4;
        }

        [data-master-dashboard] .logs-input-row {
            display: flex;
            gap: 0.6rem;
            margin-top: 0.35rem;
        }

        [data-master-dashboard] .logs-input-row input {
            min-width: 0;
        }

        [data-master-dashboard] .logs-workspace {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(320px, 380px);
            min-height: 0;
            flex: 1;
            gap: 1rem;
            overflow: hidden;
            padding: 1rem;
        }

        [data-master-dashboard] .logs-table-panel,
        [data-master-dashboard] .logs-detail-panel {
            min-height: 0;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.08);
        }

        [data-master-dashboard] .logs-table-scroll {
            width: 100%;
            height: 100%;
            overflow: auto;
            overscroll-behavior: contain;
        }

        [data-master-dashboard] .logs-table {
            width: 100%;
            min-width: 1120px;
            table-layout: fixed;
        }

        [data-master-dashboard] .logs-table th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: #f8fafc;
        }

        [data-master-dashboard] .logs-table td,
        [data-master-dashboard] .logs-table th {
            overflow: hidden;
            text-overflow: ellipsis;
            vertical-align: top;
            white-space: nowrap;
        }

        [data-master-dashboard] .logs-row-selected {
            background: #eef4ff;
            box-shadow: inset 3px 0 0 #2157ff;
        }

        [data-master-dashboard] .logs-detail-panel {
            display: flex;
            flex-direction: column;
        }

        [data-master-dashboard] .logs-detail-header {
            flex-shrink: 0;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem;
        }

        [data-master-dashboard] .logs-detail-body {
            min-height: 0;
            overflow: auto;
            padding: 1rem;
        }

        [data-master-dashboard] .logs-detail-grid {
            display: grid;
            gap: 0.8rem;
        }

        [data-master-dashboard] .logs-detail-grid dt {
            color: #64748b;
            font-size: 0.7rem;
            font-weight: 850;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        [data-master-dashboard] .logs-detail-grid dd {
            margin-top: 0.25rem;
            overflow-wrap: anywhere;
            color: #0f172a;
            font-size: 0.85rem;
        }

        [data-master-dashboard] .logs-footer {
            display: flex;
            flex-shrink: 0;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            border-top: 1px solid #e2e8f0;
            background: #fff;
            padding: 0.75rem 1rem;
            color: #475569;
            font-size: 0.875rem;
        }

        @media (max-width: 1180px) {
            [data-master-dashboard] .logs-workspace {
                grid-template-columns: minmax(0, 1fr);
                grid-template-rows: minmax(0, 1fr) minmax(220px, 34vh);
            }

            [data-master-dashboard] .logs-detail-panel {
                display: flex;
            }
        }

        @media (max-width: 920px) {
            [data-master-dashboard] .logs-filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            [data-master-dashboard] .logs-field,
            [data-master-dashboard] .logs-field-wide,
            [data-master-dashboard] .logs-field-support {
                grid-column: span 1;
            }
        }

        @media (max-width: 640px) {
            [data-master-dashboard] .logs-manager-header,
            [data-master-dashboard] .logs-footer {
                align-items: flex-start;
                flex-direction: column;
            }

            [data-master-dashboard] .logs-filter-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div data-master-dashboard class="space-y-7">
    <div class="master-hero overflow-hidden rounded-xl text-white">
        <div class="flex flex-col gap-6 px-6 py-8 sm:flex-row sm:items-end sm:justify-between lg:px-8">
            <div>
                <div class="mb-5 flex items-center gap-3">
                    <span class="rounded-lg bg-white px-3 py-2 shadow-sm">
                        <img src="{{ asset('color-paychat-logo-main.svg') }}" alt="PayChat" class="h-8 w-auto">
                    </span>
                    <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-100">
                        Master Control
                    </span>
                </div>
                <h1 class="text-3xl font-semibold text-white">Tenant & Lead Operations</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-300">
                    Review tax settings, branding, owner accounts, reset tenant passwords, and track incoming demo requests.
                </p>
            </div>

            <div class="grid grid-cols-3 gap-3 text-right sm:min-w-80">
                <div class="rounded-lg border border-white/10 bg-white/10 px-4 py-3 shadow-sm">
                    <div class="text-2xl font-semibold">{{ $tenants->count() }}</div>
                    <div class="text-xs uppercase tracking-wide text-slate-300">Tenants</div>
                </div>
                <div class="rounded-lg border border-white/10 bg-white/10 px-4 py-3 shadow-sm">
                    <div class="text-2xl font-semibold">{{ $tenants->sum(fn ($tenant) => $tenant->users->count()) }}</div>
                    <div class="text-xs uppercase tracking-wide text-slate-300">Users</div>
                </div>
                <div class="rounded-lg border border-white/10 bg-white/10 px-4 py-3 shadow-sm">
                    <div class="text-2xl font-semibold">{{ $demoLeads->count() }}</div>
                    <div class="text-xs uppercase tracking-wide text-slate-300">Leads</div>
                </div>
            </div>
        </div>
    </div>

    @if(session('status'))
        <div class="mb-5 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @if(session('generated_password'))
        <div class="mb-5 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <div class="font-semibold">Generated password, show once only:</div>
            <code class="mt-2 block break-all rounded-md bg-white px-3 py-2 font-mono text-sm text-slate-950">{{ session('generated_password') }}</code>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <div class="font-semibold">Please fix the highlighted details.</div>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="master-panel overflow-hidden rounded-xl">
        <div class="master-section-head px-5 py-4">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <h2 class="text-base font-semibold text-slate-950">All Tenants</h2>
                    <span class="master-count-pill">{{ $tenants->count() }} records</span>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <p class="text-sm text-slate-500">Click any row to open the full tenant profile.</p>
                    <button
                        type="button"
                        class="master-action px-3 py-2 text-sm"
                        data-logs-open
                        data-tenant-name="System / Unknown"
                        data-logs-url="{{ route('master.logs.system') }}"
                        data-dates-url="{{ route('master.logs.system.dates') }}"
                    >
                        System Logs
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Tenant</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Branding</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Tax</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Plan</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Users</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Created</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($tenants as $tenant)
                        @php
                            $branding = $tenant->branding;
                            $tax = $tenant->taxConfig;
                            $accent = $branding?->primary_color ?: '#6366f1';
                            $selfPosEnabled = $tenant->selfPosEnabled();
                        @endphp
                        <tr
                            role="button"
                            tabindex="0"
                            data-modal-target="tenant-modal-{{ $tenant->id }}"
                            class="cursor-pointer transition hover:bg-indigo-50/50 focus:bg-indigo-50/70 focus:outline-none"
                        >
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-md border border-slate-200 bg-slate-50">
                                        @if($branding?->logo)
                                            <img src="{{ $branding->logo }}" alt="{{ $tenant->name }}" class="h-full w-full object-cover">
                                        @else
                                            <span class="text-sm font-semibold text-slate-700">{{ \Illuminate\Support\Str::of($tenant->name)->substr(0, 1)->upper() }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-950">{{ $tenant->name }}</div>
                                        <div class="mt-0.5 text-xs text-slate-500">{{ $tenant->slug }} · {{ $tenant->industry }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2 text-sm text-slate-700">
                                    <span class="h-3 w-3 rounded-full border border-slate-200" style="background-color: {{ $accent }}"></span>
                                    {{ $branding?->company_name ?: 'Not set' }}
                                </div>
                                <div class="mt-1 text-xs text-slate-500">{{ $branding?->phone ?: 'No phone' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-sm font-medium text-slate-900">{{ $tax?->gst_number ?: 'GST not set' }}</div>
                                <div class="mt-1 text-xs text-slate-500">
                                    {{ $tax?->is_gst_enabled ? 'GST enabled' : 'GST disabled' }} · IGST {{ $tax?->igst_rate ?? 0 }}%
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">{{ $tenant->plan ?: 'trial' }}</span>
                            </td>
                            <td class="px-5 py-4">
                                @if($tenant->is_active === false)
                                    <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700">Inactive</span>
                                @else
                                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Active</span>
                                @endif
                                <div class="mt-2">
                                    @if($selfPosEnabled)
                                        <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">Self POS on</span>
                                    @else
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Self POS off</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-700">{{ $tenant->users->count() }}</td>
                            <td class="px-5 py-4 text-right text-sm text-slate-500">{{ $tenant->created_at?->format('d M Y') }}</td>
                            <td class="px-5 py-4 text-right">
                                <button
                                    type="button"
                                    title="Manage tenant"
                                    class="master-action master-action-primary mr-2 px-3 py-2 text-sm"
                                    data-modal-target="tenant-modal-{{ $tenant->id }}"
                                    data-open-tab="users"
                                >
                                    Manage ...
                                </button>
                                <button
                                    type="button"
                                    class="master-action px-3 py-2 text-sm"
                                    data-logs-open
                                    data-tenant-name="{{ $tenant->name }}"
                                    data-logs-url="{{ route('master.tenants.logs', $tenant) }}"
                                    data-dates-url="{{ route('master.tenants.logs.dates', $tenant) }}"
                                >
                                    Logs
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10 text-center text-sm text-slate-500">No tenants registered yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="master-panel overflow-hidden rounded-xl">
        <div class="master-section-head px-5 py-4">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <h2 class="text-base font-semibold text-slate-950">Demo Leads</h2>
                    <span class="master-count-pill">{{ $demoLeads->count() }} records</span>
                </div>
                <p class="text-sm text-slate-500">Latest website demo requests with editable status and notes.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[1120px] divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Lead</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Business</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Demo Time</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Notes</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Source</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Received</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($demoLeads as $lead)
                        @php
                            $leadStatus = $lead->status ?: 'new';
                            $leadStatusClasses = match ($leadStatus) {
                                'converted' => 'bg-emerald-50 text-emerald-700',
                                'demo_scheduled' => 'bg-blue-50 text-blue-700',
                                'contacted' => 'bg-indigo-50 text-indigo-700',
                                'not_interested', 'closed' => 'bg-slate-100 text-slate-600',
                                default => 'bg-amber-50 text-amber-700',
                            };
                        @endphp
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-950">{{ $lead->name }}</div>
                                <div class="mt-1 text-xs text-slate-500">
                                    {{ $lead->phone }}
                                    @if($lead->email)
                                        · {{ $lead->email }}
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-sm font-medium text-slate-900">{{ $lead->business_name }}</div>
                                <div class="mt-1 text-xs text-slate-500">
                                    {{ $lead->business_type ?: 'Type not set' }}
                                    @if($lead->counters)
                                        · {{ $lead->counters }} counters
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-700">
                                {{ $lead->preferred_demo_time?->format('d M Y, h:i A') ?: 'Not set' }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-semibold uppercase tracking-wide {{ $leadStatusClasses }}">
                                    {{ str_replace('_', ' ', $leadStatus) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-700">
                                <div class="max-w-sm whitespace-normal break-words leading-5">
                                    {{ $lead->notes ?: 'No notes yet' }}
                                </div>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-700">{{ $lead->source ?: 'website' }}</td>
                            <td class="px-5 py-4 text-right text-sm text-slate-500">{{ $lead->created_at?->format('d M Y') }}</td>
                            <td class="px-5 py-4 text-right">
                                <button
                                    type="button"
                                    class="master-action px-3 py-2 text-sm"
                                    data-modal-target="demo-lead-modal-{{ $lead->id }}"
                                >
                                    Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10 text-center text-sm text-slate-500">No demo leads yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @foreach($demoLeads as $lead)
        @php
            $isDemoLeadModalOpen = (int) session('demo_lead_id') === (int) $lead->id;
            $leadOld = fn (string $field, mixed $default = null) => $isDemoLeadModalOpen ? old($field, $default) : $default;
        @endphp

        <div
            id="demo-lead-modal-{{ $lead->id }}"
            class="fixed inset-0 z-50 {{ $isDemoLeadModalOpen ? 'flex' : 'hidden' }} items-center justify-center bg-slate-950/70 px-4 py-6"
            data-modal
            aria-hidden="{{ $isDemoLeadModalOpen ? 'false' : 'true' }}"
        >
            <div class="max-h-[92vh] w-full max-w-4xl overflow-hidden rounded-xl bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-4 bg-slate-950 px-6 py-5 text-white">
                    <div>
                        <h2 class="text-xl font-semibold">Edit Demo Lead</h2>
                        <p class="mt-1 text-sm text-slate-300">{{ $lead->business_name }} · {{ $lead->phone }}</p>
                    </div>
                    <button type="button" class="master-action master-action-dark px-3 py-2 text-sm" data-modal-close>
                        Close
                    </button>
                </div>

                <form method="POST" action="{{ route('master.demo-leads.update', $lead) }}" class="max-h-[calc(92vh-89px)] overflow-y-auto px-6 py-5">
                    @csrf
                    @method('PATCH')

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="text-sm font-semibold text-slate-700">
                            Name
                            <input name="name" value="{{ $leadOld('name', $lead->name) }}" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-normal text-slate-900 outline-none focus:border-slate-950 focus:ring-1 focus:ring-slate-950">
                        </label>
                        <label class="text-sm font-semibold text-slate-700">
                            Phone
                            <input name="phone" value="{{ $leadOld('phone', $lead->phone) }}" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-normal text-slate-900 outline-none focus:border-slate-950 focus:ring-1 focus:ring-slate-950">
                        </label>
                        <label class="text-sm font-semibold text-slate-700">
                            Email
                            <input name="email" type="email" value="{{ $leadOld('email', $lead->email) }}" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-normal text-slate-900 outline-none focus:border-slate-950 focus:ring-1 focus:ring-slate-950">
                        </label>
                        <label class="text-sm font-semibold text-slate-700">
                            Business Name
                            <input name="business_name" value="{{ $leadOld('business_name', $lead->business_name) }}" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-normal text-slate-900 outline-none focus:border-slate-950 focus:ring-1 focus:ring-slate-950">
                        </label>
                        <label class="text-sm font-semibold text-slate-700">
                            Business Type
                            <input name="business_type" value="{{ $leadOld('business_type', $lead->business_type) }}" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-normal text-slate-900 outline-none focus:border-slate-950 focus:ring-1 focus:ring-slate-950">
                        </label>
                        <label class="text-sm font-semibold text-slate-700">
                            Counters
                            <input name="counters" value="{{ $leadOld('counters', $lead->counters) }}" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-normal text-slate-900 outline-none focus:border-slate-950 focus:ring-1 focus:ring-slate-950">
                        </label>
                        <label class="text-sm font-semibold text-slate-700">
                            Demo Time
                            <input name="preferred_demo_time" type="datetime-local" value="{{ $leadOld('preferred_demo_time', $lead->preferred_demo_time?->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-normal text-slate-900 outline-none focus:border-slate-950 focus:ring-1 focus:ring-slate-950">
                        </label>
                        <label class="text-sm font-semibold text-slate-700">
                            Status
                            <select name="status" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-normal text-slate-900 outline-none focus:border-slate-950 focus:ring-1 focus:ring-slate-950">
                                @foreach($demoLeadStatuses as $status)
                                    <option value="{{ $status }}" @selected($leadOld('status', $lead->status ?: 'new') === $status)>{{ \Illuminate\Support\Str::headline($status) }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="text-sm font-semibold text-slate-700 md:col-span-2">
                            Source
                            <input name="source" value="{{ $leadOld('source', $lead->source ?: 'website') }}" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-normal text-slate-900 outline-none focus:border-slate-950 focus:ring-1 focus:ring-slate-950">
                        </label>
                        <label class="text-sm font-semibold text-slate-700 md:col-span-2">
                            Notes
                            <textarea name="notes" rows="5" class="mt-1 w-full resize-y rounded-md border border-slate-300 px-3 py-2 text-sm font-normal text-slate-900 outline-none focus:border-slate-950 focus:ring-1 focus:ring-slate-950">{{ $leadOld('notes', $lead->notes) }}</textarea>
                        </label>
                    </div>

                    <div class="mt-6 flex flex-col gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-end">
                        <button type="button" class="master-action px-4 py-2.5 text-sm" data-modal-close>Cancel</button>
                        <button class="master-action master-action-primary px-4 py-2.5 text-sm">Save Lead</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    @foreach($tenants as $tenant)
        @php
            $branding = $tenant->branding;
            $tax = $tenant->taxConfig;
            $accent = $branding?->primary_color ?: '#6366f1';
            $isUserModalOpen = (int) session('tenant_user_tenant_id') === (int) $tenant->id;
            $selfPosEnabled = $tenant->selfPosEnabled();
        @endphp

        <div
            id="tenant-modal-{{ $tenant->id }}"
            class="fixed inset-0 z-50 {{ $isUserModalOpen ? 'flex' : 'hidden' }} items-center justify-center bg-slate-950/70 px-4 py-6"
            data-modal
            aria-hidden="{{ $isUserModalOpen ? 'false' : 'true' }}"
        >
            <div class="max-h-[92vh] w-full max-w-5xl overflow-hidden rounded-xl bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-4 bg-slate-950 px-6 py-5 text-white">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-lg bg-white">
                            @if($branding?->logo)
                                <img src="{{ $branding->logo }}" alt="{{ $tenant->name }}" class="h-full w-full object-cover">
                            @else
                                <span class="text-lg font-semibold text-slate-950">{{ \Illuminate\Support\Str::of($tenant->name)->substr(0, 1)->upper() }}</span>
                            @endif
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold">{{ $tenant->name }}</h2>
                            <p class="mt-1 text-sm text-slate-300">{{ $tenant->slug }} · {{ $tenant->database }}</p>
                        </div>
                    </div>
                    <button type="button" class="master-action master-action-dark px-3 py-2 text-sm" data-modal-close>
                        Close
                    </button>
                </div>

                <div class="max-h-[calc(92vh-89px)] overflow-y-auto">
                    <div class="px-6 pt-4">
                        <div class="flex gap-2" role="tablist" aria-label="Tenant management">
                            <button type="button" class="rounded-lg px-4 py-2 text-sm font-semibold {{ $isUserModalOpen ? 'bg-slate-100 text-slate-600' : 'bg-slate-950 text-white' }}" data-tenant-tab="profile" data-tab-active-class="bg-slate-950 text-white" data-tab-inactive-class="bg-slate-100 text-slate-600">
                                Profile
                            </button>
                            <button type="button" class="rounded-lg px-4 py-2 text-sm font-semibold {{ $isUserModalOpen ? 'bg-slate-950 text-white' : 'bg-slate-100 text-slate-600' }}" data-tenant-tab="users" data-tab-active-class="bg-slate-950 text-white" data-tab-inactive-class="bg-slate-100 text-slate-600">
                                Users
                            </button>
                        </div>
                    </div>

                    <div class="p-6 {{ $isUserModalOpen ? 'hidden' : '' }}" data-tenant-tab-panel="profile">
                        <div class="grid gap-5 lg:grid-cols-3">
                            <section class="rounded-xl bg-slate-50 p-5">
                                <h3 class="text-sm font-semibold text-slate-950">Tenant Profile</h3>
                                <dl class="mt-4 space-y-3 text-sm">
                                    <div><dt class="text-slate-500">Name</dt><dd class="mt-1 font-medium text-slate-950">{{ $tenant->name }}</dd></div>
                                    <div><dt class="text-slate-500">Industry</dt><dd class="mt-1 font-medium text-slate-950">{{ $tenant->industry }}</dd></div>
                                    <div><dt class="text-slate-500">Plan</dt><dd class="mt-1 font-medium text-slate-950">{{ $tenant->plan ?: 'trial' }}</dd></div>
                                    <div><dt class="text-slate-500">Access</dt><dd class="mt-1 font-medium text-slate-950">{{ $tenant->is_active === false ? 'Inactive' : 'Active' }}</dd></div>
                                    <div><dt class="text-slate-500">Self POS</dt><dd class="mt-1 font-medium text-slate-950">{{ $selfPosEnabled ? 'Enabled' : 'Disabled' }}</dd></div>
                                    <div><dt class="text-slate-500">API key</dt><dd class="mt-1 break-all font-mono text-xs text-slate-700">{{ $tenant->api_key }}</dd></div>
                                </dl>
                            </section>

                            <section class="rounded-xl bg-slate-50 p-5">
                                <h3 class="text-sm font-semibold text-slate-950">Tax Config</h3>
                                <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                    <div><dt class="text-slate-500">GST</dt><dd class="mt-1 font-medium">{{ $tax?->gst_number ?: 'Not set' }}</dd></div>
                                    <div><dt class="text-slate-500">Enabled</dt><dd class="mt-1 font-medium">{{ $tax?->is_gst_enabled ? 'Yes' : 'No' }}</dd></div>
                                    <div><dt class="text-slate-500">Inclusive</dt><dd class="mt-1 font-medium">{{ $tax?->is_inclusive ? 'Yes' : 'No' }}</dd></div>
                                    <div><dt class="text-slate-500">CGST</dt><dd class="mt-1 font-medium">{{ $tax?->cgst_rate ?? 0 }}%</dd></div>
                                    <div><dt class="text-slate-500">SGST</dt><dd class="mt-1 font-medium">{{ $tax?->sgst_rate ?? 0 }}%</dd></div>
                                    <div><dt class="text-slate-500">IGST</dt><dd class="mt-1 font-medium">{{ $tax?->igst_rate ?? 0 }}%</dd></div>
                                </dl>
                            </section>

                            <section class="rounded-xl bg-slate-50 p-5">
                                <h3 class="text-sm font-semibold text-slate-950">Branding</h3>
                                <dl class="mt-4 space-y-3 text-sm">
                                    <div><dt class="text-slate-500">Company</dt><dd class="mt-1 font-medium">{{ $branding?->company_name ?: 'Not set' }}</dd></div>
                                    <div>
                                        <dt class="text-slate-500">Primary color</dt>
                                        <dd class="mt-1 flex items-center gap-2 font-medium">
                                            <span class="h-4 w-4 rounded-full border border-slate-200" style="background-color: {{ $accent }}"></span>
                                            {{ $branding?->primary_color ?: 'Not set' }}
                                        </dd>
                                    </div>
                                    <div><dt class="text-slate-500">Phone</dt><dd class="mt-1 font-medium">{{ $branding?->phone ?: 'Not set' }}</dd></div>
                                    <div><dt class="text-slate-500">Address</dt><dd class="mt-1 font-medium">{{ $branding?->address ?: 'Not set' }}</dd></div>
                                </dl>
                            </section>
                        </div>

                        <section class="mt-5 rounded-xl bg-slate-50 p-5">
                            <h3 class="text-sm font-semibold text-slate-950">Plan & Access</h3>
                            <form method="POST" action="{{ route('master.tenants.access', $tenant) }}" class="mt-4 grid gap-4 md:grid-cols-[1fr_auto_auto_auto] md:items-end">
                                @csrf
                                @method('PATCH')

                                <label class="grid gap-1 text-sm font-semibold text-slate-700">
                                    Pricing plan
                                    <select name="plan" class="rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-slate-950 focus:ring-1 focus:ring-slate-950">
                                        <option value="trial" @selected(($tenant->plan ?: 'trial') === 'trial')>Trial</option>
                                        @foreach($pricingPlans as $plan)
                                            <option value="{{ $plan->key }}" @selected(($tenant->plan ?: 'trial') === $plan->key)>
                                                {{ $plan->name }} - {{ $plan->currency }} {{ number_format((float) $plan->monthly_price, 2) }}/mo
                                            </option>
                                        @endforeach
                                    </select>
                                </label>

                                <label class="flex min-h-[40px] items-center gap-2 text-sm font-semibold text-slate-700">
                                    <input type="checkbox" name="is_active" value="1" @checked($tenant->is_active !== false) class="h-4 w-4 rounded border-slate-300 text-slate-950 focus:ring-slate-950">
                                    Tenant active
                                </label>

                                <label class="flex min-h-[40px] items-center gap-2 text-sm font-semibold text-slate-700">
                                    <input type="checkbox" name="self_pos_enabled" value="1" @checked($selfPosEnabled) class="h-4 w-4 rounded border-slate-300 text-slate-950 focus:ring-slate-950">
                                    Self POS enabled
                                </label>

                                <button class="master-action master-action-primary px-4 py-2.5 text-sm">
                                    Save Access
                                </button>
                            </form>
                            <p class="mt-3 text-xs font-medium text-slate-500">Inactive tenants cannot log in or call tenant APIs. Disabled Self POS shows customers a PayChat support message while staff POS remains unaffected.</p>
                        </section>
                    </div>

                    <div class="p-6 {{ $isUserModalOpen ? '' : 'hidden' }}" data-tenant-tab-panel="users">
                        <div class="grid gap-5 lg:grid-cols-[1fr_420px]">
                            <section class="rounded-xl bg-slate-50 p-5">
                                <h3 class="text-sm font-semibold text-slate-950">Tenant Users</h3>
                                <div class="mt-4 overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-slate-200/70">
                                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                                        <thead class="bg-slate-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left font-semibold text-slate-600">Name</th>
                                                <th class="px-4 py-2 text-left font-semibold text-slate-600">Email</th>
                                                <th class="px-4 py-2 text-left font-semibold text-slate-600">Role</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @forelse($tenant->users as $user)
                                                <tr>
                                                    <td class="px-4 py-3 font-medium text-slate-950">{{ $user->name }}</td>
                                                    <td class="px-4 py-3 text-slate-700">{{ $user->email }}</td>
                                                    <td class="px-4 py-3 text-slate-700">{{ $user->role ?: 'user' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="px-4 py-6 text-center text-slate-500">No tenant users found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </section>

                            <div class="space-y-5">
                                <section class="rounded-xl bg-slate-50 p-5">
                                    <h3 class="text-sm font-semibold text-slate-950">Create User</h3>
                                    <form method="POST" action="{{ route('master.tenants.users.store', $tenant) }}" class="mt-4 space-y-3">
                                        @csrf

                                        <input name="name" type="text" required value="{{ $isUserModalOpen ? old('name') : '' }}" placeholder="Name"
                                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-slate-950 focus:ring-1 focus:ring-slate-950">

                                        <input name="email" type="email" required value="{{ $isUserModalOpen ? old('email') : '' }}" placeholder="Email"
                                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-slate-950 focus:ring-1 focus:ring-slate-950">

                                        <select name="role" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-slate-950 focus:ring-1 focus:ring-slate-950">
                                            <option value="">Select role</option>
                                            @foreach(['owner', 'manager', 'cashier', 'kitchen', 'waiter', 'accountant'] as $role)
                                                <option value="{{ $role }}" @selected($isUserModalOpen && old('role') === $role)>{{ ucfirst($role) }}</option>
                                            @endforeach
                                        </select>

                                        <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                            <input name="generate_password" value="1" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-slate-950 focus:ring-slate-950" data-generate-password @checked($isUserModalOpen && old('generate_password'))>
                                            Generate password
                                        </label>

                                        <div data-password-fields>
                                            <input name="password" type="password" placeholder="Password"
                                                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-slate-950 focus:ring-1 focus:ring-slate-950">

                                            <input name="password_confirmation" type="password" placeholder="Confirm password"
                                                class="mt-3 w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-slate-950 focus:ring-1 focus:ring-slate-950">
                                        </div>

                                        <button class="master-action master-action-primary w-full px-4 py-2.5 text-sm">
                                            Create Tenant User
                                        </button>
                                    </form>
                                </section>

                                <section class="rounded-xl bg-slate-50 p-5">
                                    <h3 class="text-sm font-semibold text-slate-950">Reset Password</h3>
                                    <form method="POST" action="{{ route('master.tenants.password', $tenant) }}" class="mt-4 space-y-3">
                                        @csrf
                                        @method('PATCH')

                                        <select name="user_id" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-slate-950 focus:ring-1 focus:ring-slate-950">
                                            <option value="">Select user</option>
                                            @foreach($tenant->users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->email }}</option>
                                            @endforeach
                                        </select>

                                        <input name="password" type="password" required placeholder="New password"
                                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-slate-950 focus:ring-1 focus:ring-slate-950">

                                        <input name="password_confirmation" type="password" required placeholder="Confirm password"
                                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-slate-950 focus:ring-1 focus:ring-slate-950">

                                        <button class="master-action master-action-primary w-full px-4 py-2.5 text-sm">
                                            Reset Tenant Password
                                        </button>
                                    </form>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div id="tenant-logs-modal" class="logs-manager-modal hidden" data-modal aria-hidden="true">
        <div class="logs-manager-shell">
            <div class="logs-manager-header">
                <div>
                    <h2 class="logs-manager-title">Operational Logs</h2>
                    <p id="logs-tenant-name" class="logs-manager-tenant"></p>
                </div>
                <button type="button" class="logs-close-btn" data-modal-close>
                    Close
                </button>
            </div>

            <div class="logs-filter-bar">
                <div class="logs-filter-grid">
                    <label class="logs-field">
                        Date
                        <input id="logs-date" type="date" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-normal normal-case tracking-normal text-slate-900">
                    </label>
                    <label class="logs-field">
                        Module
                        <select id="logs-module" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-normal normal-case tracking-normal text-slate-900">
                            <option value="">All</option>
                            <option value="payment">Payment</option>
                            <option value="cart">Cart</option>
                            <option value="order">Order</option>
                            <option value="invoice">Invoice</option>
                            <option value="kds">KDS</option>
                            <option value="token">Token</option>
                            <option value="offline">Offline</option>
                            <option value="system">System</option>
                        </select>
                    </label>
                    <label class="logs-field logs-field-wide">
                        Event
                        <input id="logs-event" type="search" placeholder="payment.create.failed" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-normal normal-case tracking-normal text-slate-900">
                    </label>
                    <label class="logs-field">
                        Level
                        <select id="logs-level" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-normal normal-case tracking-normal text-slate-900">
                            <option value="">All</option>
                            <option value="error">Error</option>
                            <option value="warning">Warning</option>
                        </select>
                    </label>
                    <label class="logs-field">
                        Severity
                        <select id="logs-severity" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-normal normal-case tracking-normal text-slate-900">
                            <option value="">All</option>
                            <option value="high">High</option>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                        </select>
                    </label>
                    <label class="logs-field logs-field-support">
                        Support Code
                        <div class="logs-input-row">
                            <input id="logs-support-code" type="search" placeholder="PCR-..." class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-normal normal-case tracking-normal text-slate-900">
                            <button id="logs-refresh" type="button" class="master-action master-action-primary px-4 py-2 text-sm">Refresh</button>
                        </div>
                    </label>
                </div>
            </div>

            <div class="logs-workspace">
                <section class="logs-table-panel">
                    <div class="logs-table-scroll">
                        <div id="logs-status" class="hidden px-5 py-4 text-sm text-slate-600"></div>
                        <table class="logs-table divide-y divide-slate-200 text-sm">
                            <thead>
                                <tr>
                                    <th class="w-44 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Time</th>
                                    <th class="w-28 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Level</th>
                                    <th class="w-28 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Module</th>
                                    <th class="w-56 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Event</th>
                                    <th class="w-64 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Support Code</th>
                                    <th class="w-24 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Message</th>
                                    <th class="w-28 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Actor</th>
                                </tr>
                            </thead>
                            <tbody id="logs-table-body" class="divide-y divide-slate-100 bg-white"></tbody>
                        </table>
                    </div>
                </section>

                <aside class="logs-detail-panel" aria-live="polite">
                    <div class="logs-detail-header">
                        <p class="text-xs font-black uppercase tracking-wide text-slate-500">Selected Entry</p>
                        <h3 id="logs-detail-title" class="mt-1 text-base font-black text-slate-950">No log selected</h3>
                    </div>
                    <div id="logs-detail-body" class="logs-detail-body text-sm text-slate-600">
                        Select a row to inspect path, exception, file, and line details.
                    </div>
                </aside>
            </div>

            <div class="logs-footer">
                <span id="logs-page-summary">No logs loaded</span>
                <div class="flex gap-2">
                    <button id="logs-prev" type="button" class="master-action px-3 py-2 disabled:opacity-40">Prev</button>
                    <button id="logs-next" type="button" class="master-action px-3 py-2 disabled:opacity-40">Next</button>
                </div>
            </div>
        </div>
    </div>

    </div>

    <script>
        const logsState = {
            url: null,
            datesUrl: null,
            page: 1,
            lastPage: 1,
            perPage: 10,
            rows: [],
            selectedIndex: null,
        };

        const today = () => new Date().toISOString().slice(0, 10);
        const logsModal = document.getElementById('tenant-logs-modal');
        const logsBody = document.getElementById('logs-table-body');
        const logsStatus = document.getElementById('logs-status');
        const logsSummary = document.getElementById('logs-page-summary');
        const logsPrev = document.getElementById('logs-prev');
        const logsNext = document.getElementById('logs-next');
        const logsDetailTitle = document.getElementById('logs-detail-title');
        const logsDetailBody = document.getElementById('logs-detail-body');

        const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        }[char]));

        const toggleClasses = (element, removeClasses, addClasses) => {
            element.classList.remove(...removeClasses.split(' '));
            element.classList.add(...addClasses.split(' '));
        };

        const setTenantTab = (modal, selectedTab) => {
            if (!modal) return;

            modal.querySelectorAll('[data-tenant-tab]').forEach((button) => {
                const active = button.dataset.tenantTab === selectedTab;
                toggleClasses(
                    button,
                    active ? button.dataset.tabInactiveClass : button.dataset.tabActiveClass,
                    active ? button.dataset.tabActiveClass : button.dataset.tabInactiveClass
                );
            });

            modal.querySelectorAll('[data-tenant-tab-panel]').forEach((panel) => {
                panel.classList.toggle('hidden', panel.dataset.tenantTabPanel !== selectedTab);
            });
        };

        const openModal = (modal, tab = null) => {
            if (!modal) return;

            if (tab) {
                setTenantTab(modal, tab);
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.setAttribute('aria-hidden', 'false');

            if (modal === logsModal) {
                document.body.style.overflow = 'hidden';
            }
        };

        const closeModal = (modal) => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            modal.setAttribute('aria-hidden', 'true');

            if (modal === logsModal) {
                document.body.style.overflow = '';
            }
        };

        const syncGeneratedPasswordFields = (checkbox) => {
            const form = checkbox.closest('form');
            const fields = form?.querySelector('[data-password-fields]');
            if (!fields) return;

            fields.classList.toggle('hidden', checkbox.checked);
        };

        const showLogsStatus = (message, tone = 'default') => {
            logsStatus.textContent = message;
            logsStatus.className = `px-5 py-4 text-sm ${tone === 'error' ? 'text-rose-700' : 'text-slate-600'}`;
            logsStatus.classList.remove('hidden');
        };

        const hideLogsStatus = () => {
            logsStatus.classList.add('hidden');
            logsStatus.textContent = '';
        };

        const renderLogDetails = (row) => {
            if (!row) {
                logsDetailTitle.textContent = 'No log selected';
                logsDetailBody.textContent = 'Select a row to inspect path, exception, file, and line details.';
                return;
            }

            const supportCode = row.support_code || row.request_id || '';
            logsDetailTitle.textContent = row.event || supportCode || 'Log entry';
            logsDetailBody.innerHTML = `
                <dl class="logs-detail-grid">
                    <div><dt>Support Code</dt><dd><code>${escapeHtml(supportCode || '-')}</code></dd></div>
                    <div><dt>Message</dt><dd>${escapeHtml(row.safe_message || '-')}</dd></div>
                    <div><dt>Path</dt><dd>${escapeHtml(row.path || '-')}</dd></div>
                    <div><dt>Route</dt><dd>${escapeHtml(row.route || '-')}</dd></div>
                    <div><dt>Exception</dt><dd>${escapeHtml(row.exception_class || '-')}</dd></div>
                    <div><dt>Exception Message</dt><dd>${escapeHtml(row.exception_message || '-')}</dd></div>
                    <div><dt>File</dt><dd>${escapeHtml(row.file || '-')}</dd></div>
                    <div><dt>Line</dt><dd>${escapeHtml(row.line || '-')}</dd></div>
                </dl>
            `;
        };

        const selectLogRow = (index) => {
            logsState.selectedIndex = index;
            logsBody.querySelectorAll('[data-log-row]').forEach((row) => {
                row.classList.toggle('logs-row-selected', Number(row.dataset.logRow) === index);
            });
            renderLogDetails(logsState.rows[index]);
        };

        const logsParams = () => {
            const params = new URLSearchParams({
                date: document.getElementById('logs-date').value || today(),
                page: logsState.page,
                per_page: logsState.perPage,
            });

            ['module', 'level', 'severity', 'event'].forEach((id) => {
                const value = document.getElementById(`logs-${id}`).value;
                if (value) params.set(id, value);
            });

            const supportCode = document.getElementById('logs-support-code').value.trim();
            if (supportCode) params.set('support_code', supportCode);

            return params;
        };

        const loadAvailableLogDate = async () => {
            document.getElementById('logs-date').value = today();

            if (!logsState.datesUrl) return;

            try {
                const response = await fetch(logsState.datesUrl, { headers: { Accept: 'application/json' } });
                if (!response.ok) return;
                const json = await response.json();
                if (Array.isArray(json.data) && json.data.length > 0) {
                    document.getElementById('logs-date').value = json.data.includes(today()) ? today() : json.data[0];
                }
            } catch (error) {
                // Date lookup is optional; log loading will still work with today.
            }
        };

        const renderLogs = (rows) => {
            logsBody.innerHTML = '';
            logsState.rows = rows;
            logsState.selectedIndex = null;

            rows.forEach((row, index) => {
                const supportCode = escapeHtml(row.support_code || row.request_id || '');
                const time = row.timestamp ? new Date(row.timestamp).toLocaleString() : '';
                const levelClass = row.level === 'error' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700';

                logsBody.insertAdjacentHTML('beforeend', `
                    <tr class="align-top hover:bg-slate-50 cursor-pointer" data-log-row="${index}">
                        <td class="whitespace-nowrap px-4 py-3 text-slate-600">${escapeHtml(time)}</td>
                        <td class="px-4 py-3"><span class="rounded-md px-2 py-1 text-xs font-semibold ${levelClass}">${escapeHtml(row.level || '')}/${escapeHtml(row.severity || '')}</span></td>
                        <td class="px-4 py-3 text-slate-700">${escapeHtml(row.module || '')}</td>
                        <td class="px-4 py-3 font-medium text-slate-900">${escapeHtml(row.event || '')}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <code class="text-xs text-slate-700">${supportCode}</code>
                                ${supportCode ? `<button type="button" class="master-action px-2 py-1 text-xs" data-copy="${supportCode}">Copy</button>` : ''}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-700">${escapeHtml(row.status_code || '')}</td>
                        <td class="max-w-md px-4 py-3 text-slate-700">
                            <div>${escapeHtml(row.safe_message || '')}</div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate-600">${escapeHtml(row.location_id || '-')} / ${escapeHtml(row.user_id || '-')}</td>
                    </tr>
                `);
            });

            renderLogDetails(rows[0]);
            if (rows.length > 0) {
                selectLogRow(0);
            }
        };

        const loadLogs = async () => {
            if (!logsState.url) return;

            showLogsStatus('Loading logs...');
            logsBody.innerHTML = '';

            try {
                const response = await fetch(`${logsState.url}?${logsParams().toString()}`, { headers: { Accept: 'application/json' } });
                const json = await response.json();

                if (!response.ok) {
                    throw new Error(json.message || 'Unable to load logs.');
                }

                logsState.page = json.meta?.page || json.meta?.current_page || logsState.page;
                logsState.lastPage = json.meta?.last_page || 1;
                renderLogs(json.data || []);

                if (!json.data || json.data.length === 0) {
                    showLogsStatus('No operational logs found for this tenant/date/filter.');
                    renderLogDetails(null);
                } else {
                    hideLogsStatus();
                }

                logsSummary.textContent = `Page ${json.meta?.page || 1} of ${logsState.lastPage} · ${json.meta?.total || 0} logs`;
                logsPrev.disabled = logsState.page <= 1;
                logsNext.disabled = logsState.page >= logsState.lastPage;
            } catch (error) {
                logsSummary.textContent = 'No logs loaded';
                logsPrev.disabled = true;
                logsNext.disabled = true;
                showLogsStatus(error.message || 'Unable to load logs.', 'error');
            }
        };

        document.querySelectorAll('[data-logs-open]').forEach((button) => {
            button.addEventListener('click', async (event) => {
                event.stopPropagation();
                logsState.url = button.dataset.logsUrl;
                logsState.datesUrl = button.dataset.datesUrl;
                logsState.page = 1;
                logsState.lastPage = 1;
                document.getElementById('logs-tenant-name').textContent = button.dataset.tenantName || '';
                document.getElementById('logs-module').value = '';
                document.getElementById('logs-level').value = '';
                document.getElementById('logs-severity').value = '';
                document.getElementById('logs-event').value = '';
                document.getElementById('logs-support-code').value = '';
                openModal(logsModal);
                renderLogDetails(null);
                await loadAvailableLogDate();
                await loadLogs();
            });
        });

        document.getElementById('logs-refresh').addEventListener('click', () => {
            logsState.page = 1;
            loadLogs();
        });

        ['logs-date', 'logs-module', 'logs-level', 'logs-severity'].forEach((id) => {
            document.getElementById(id).addEventListener('change', () => {
                logsState.page = 1;
                loadLogs();
            });
        });

        ['logs-support-code', 'logs-event'].forEach((id) => document.getElementById(id).addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                logsState.page = 1;
                loadLogs();
            }
        }));

        logsPrev.addEventListener('click', () => {
            if (logsState.page > 1) {
                logsState.page -= 1;
                loadLogs();
            }
        });

        logsNext.addEventListener('click', () => {
            if (logsState.page < logsState.lastPage) {
                logsState.page += 1;
                loadLogs();
            }
        });

        logsBody.addEventListener('click', (event) => {
            const copyButton = event.target.closest('[data-copy]');
            if (copyButton) {
                navigator.clipboard?.writeText(copyButton.dataset.copy);
                copyButton.textContent = 'Copied';
                setTimeout(() => copyButton.textContent = 'Copy', 1200);
                return;
            }

            const row = event.target.closest('[data-log-row]');
            if (row) {
                selectLogRow(Number(row.dataset.logRow));
            }
        });

        document.querySelectorAll('[data-modal-target]').forEach((trigger) => {
            trigger.addEventListener('click', (event) => {
                if (trigger.tagName === 'BUTTON') {
                    event.stopPropagation();
                }

                const modal = document.getElementById(trigger.dataset.modalTarget);
                openModal(modal, trigger.dataset.openTab || 'profile');
            });

            trigger.addEventListener('keydown', (event) => {
                if (trigger.tagName === 'BUTTON') return;

                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    trigger.click();
                }
            });
        });

        document.querySelectorAll('[data-tenant-tab]').forEach((button) => {
            button.addEventListener('click', () => {
                setTenantTab(button.closest('[data-modal]'), button.dataset.tenantTab);
            });
        });

        document.querySelectorAll('[data-generate-password]').forEach((checkbox) => {
            syncGeneratedPasswordFields(checkbox);
            checkbox.addEventListener('change', () => syncGeneratedPasswordFields(checkbox));
        });

        document.querySelectorAll('[data-modal]').forEach((modal) => {
            const close = () => {
                closeModal(modal);
            };

            modal.querySelectorAll('[data-modal-close]').forEach((button) => {
                button.addEventListener('click', close);
            });

            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    close();
                }
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                document.querySelectorAll('[data-modal]').forEach((modal) => {
                    closeModal(modal);
                });
            }
        });
    </script>
@endsection
