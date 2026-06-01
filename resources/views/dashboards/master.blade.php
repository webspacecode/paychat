@extends('layouts.app')

@section('title', 'Master Dashboard | PayChat')

@section('content')
    <div class="mb-7 overflow-hidden rounded-lg border border-slate-200 bg-slate-950 text-white shadow-xl">
        <div class="flex flex-col gap-6 px-6 py-7 sm:flex-row sm:items-end sm:justify-between lg:px-8">
            <div>
                <div class="mb-5 flex items-center gap-3">
                    <span class="rounded-md bg-white px-3 py-2">
                        <img src="{{ asset('color-paychat-logo-main.svg') }}" alt="PayChat" class="h-8 w-auto">
                    </span>
                    <span class="rounded-md border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-200">
                        Master Control
                    </span>
                </div>
                <h1 class="text-3xl font-semibold text-white">Tenant & Lead Operations</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-300">
                    Review tax settings, branding, owner accounts, reset tenant passwords, and track incoming demo requests.
                </p>
            </div>

            <div class="grid grid-cols-3 gap-3 text-right sm:min-w-80">
                <div class="rounded-md border border-white/10 bg-white/10 px-4 py-3">
                    <div class="text-2xl font-semibold">{{ $tenants->count() }}</div>
                    <div class="text-xs uppercase tracking-wide text-slate-300">Tenants</div>
                </div>
                <div class="rounded-md border border-white/10 bg-white/10 px-4 py-3">
                    <div class="text-2xl font-semibold">{{ $tenants->sum(fn ($tenant) => $tenant->users->count()) }}</div>
                    <div class="text-xs uppercase tracking-wide text-slate-300">Users</div>
                </div>
                <div class="rounded-md border border-white/10 bg-white/10 px-4 py-3">
                    <div class="text-2xl font-semibold">{{ $demoLeads->count() }}</div>
                    <div class="text-xs uppercase tracking-wide text-slate-300">Leads</div>
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-base font-semibold text-slate-950">All Tenants</h2>
                <p class="text-sm text-slate-500">Click any row to open the full tenant profile.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Tenant</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Branding</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Tax</th>
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
                            <td class="px-5 py-4 text-sm text-slate-700">{{ $tenant->users->count() }}</td>
                            <td class="px-5 py-4 text-right text-sm text-slate-500">{{ $tenant->created_at?->format('d M Y') }}</td>
                            <td class="px-5 py-4 text-right">
                                <button
                                    type="button"
                                    class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100"
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
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">No tenants registered yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-7 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-base font-semibold text-slate-950">Demo Leads</h2>
                <p class="text-sm text-slate-500">Latest website demo requests in table format.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Lead</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Business</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Demo Time</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Source</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Received</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($demoLeads as $lead)
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
                                <span class="inline-flex rounded-md bg-amber-50 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-amber-700">
                                    {{ $lead->status ?: 'new' }}
                                </span>
                                @if($lead->notes)
                                    <div class="mt-1 max-w-xs truncate text-xs text-slate-500">{{ $lead->notes }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-700">{{ $lead->source ?: 'website' }}</td>
                            <td class="px-5 py-4 text-right text-sm text-slate-500">{{ $lead->created_at?->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">No demo leads yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @foreach($tenants as $tenant)
        @php
            $branding = $tenant->branding;
            $tax = $tenant->taxConfig;
            $accent = $branding?->primary_color ?: '#6366f1';
        @endphp

        <div
            id="tenant-modal-{{ $tenant->id }}"
            class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/70 px-4 py-6"
            data-modal
            aria-hidden="true"
        >
            <div class="max-h-[92vh] w-full max-w-5xl overflow-hidden rounded-lg bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 bg-slate-950 px-6 py-5 text-white">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-md bg-white">
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
                    <button type="button" class="rounded-md border border-white/15 px-3 py-2 text-sm font-medium text-slate-200 hover:bg-white/10" data-modal-close>
                        Close
                    </button>
                </div>

                <div class="max-h-[calc(92vh-89px)] overflow-y-auto p-6">
                    <div class="grid gap-5 lg:grid-cols-3">
                        <section class="rounded-md border border-slate-200 p-5">
                            <h3 class="text-sm font-semibold text-slate-950">Tenant Profile</h3>
                            <dl class="mt-4 space-y-3 text-sm">
                                <div><dt class="text-slate-500">Name</dt><dd class="mt-1 font-medium text-slate-950">{{ $tenant->name }}</dd></div>
                                <div><dt class="text-slate-500">Industry</dt><dd class="mt-1 font-medium text-slate-950">{{ $tenant->industry }}</dd></div>
                                <div><dt class="text-slate-500">API key</dt><dd class="mt-1 break-all font-mono text-xs text-slate-700">{{ $tenant->api_key }}</dd></div>
                            </dl>
                        </section>

                        <section class="rounded-md border border-slate-200 p-5">
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

                        <section class="rounded-md border border-slate-200 p-5">
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

                    <div class="mt-5 grid gap-5 lg:grid-cols-[1fr_420px]">
                        <section class="rounded-md border border-slate-200 p-5">
                            <h3 class="text-sm font-semibold text-slate-950">Tenant Users</h3>
                            <div class="mt-4 overflow-hidden rounded-md border border-slate-200">
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

                        <section class="rounded-md border border-slate-200 p-5">
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

                                <button class="w-full rounded-md bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">
                                    Reset Tenant Password
                                </button>
                            </form>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div id="tenant-logs-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-950/80 px-3 py-4" data-modal aria-hidden="true">
        <div class="flex max-h-[94vh] w-full max-w-7xl flex-col overflow-hidden rounded-lg bg-white shadow-2xl">
            <div class="flex flex-col gap-4 border-b border-slate-200 bg-slate-950 px-5 py-4 text-white lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-xl font-semibold">Operational Logs</h2>
                    <p id="logs-tenant-name" class="mt-1 text-sm text-slate-300"></p>
                </div>
                <button type="button" class="rounded-md border border-white/15 px-3 py-2 text-sm font-medium text-slate-200 hover:bg-white/10" data-modal-close>
                    Close
                </button>
            </div>

            <div class="border-b border-slate-200 px-5 py-4">
                <div class="grid gap-3 md:grid-cols-6">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Date
                        <input id="logs-date" type="date" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-normal normal-case tracking-normal text-slate-900">
                    </label>
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
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
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Event
                        <input id="logs-event" type="search" placeholder="payment.create.failed" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-normal normal-case tracking-normal text-slate-900">
                    </label>
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Level
                        <select id="logs-level" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-normal normal-case tracking-normal text-slate-900">
                            <option value="">All</option>
                            <option value="error">Error</option>
                            <option value="warning">Warning</option>
                        </select>
                    </label>
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Severity
                        <select id="logs-severity" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-normal normal-case tracking-normal text-slate-900">
                            <option value="">All</option>
                            <option value="high">High</option>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                        </select>
                    </label>
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500 md:col-span-2">
                        Support Code
                        <div class="mt-1 flex gap-2">
                            <input id="logs-support-code" type="search" placeholder="PCR-..." class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-normal normal-case tracking-normal text-slate-900">
                            <button id="logs-refresh" type="button" class="rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Refresh</button>
                        </div>
                    </label>
                </div>
            </div>

            <div class="min-h-0 flex-1 overflow-auto">
                <div id="logs-status" class="hidden px-5 py-4 text-sm text-slate-600"></div>
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="sticky top-0 bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Time</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Level</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Module</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Event</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Support Code</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Message</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Location/User</th>
                        </tr>
                    </thead>
                    <tbody id="logs-table-body" class="divide-y divide-slate-100 bg-white"></tbody>
                </table>
            </div>

            <div class="flex items-center justify-between border-t border-slate-200 px-5 py-3 text-sm text-slate-600">
                <span id="logs-page-summary">No logs loaded</span>
                <div class="flex gap-2">
                    <button id="logs-prev" type="button" class="rounded-md border border-slate-300 px-3 py-2 font-medium hover:bg-slate-100 disabled:opacity-40">Prev</button>
                    <button id="logs-next" type="button" class="rounded-md border border-slate-300 px-3 py-2 font-medium hover:bg-slate-100 disabled:opacity-40">Next</button>
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
            perPage: 25,
        };

        const today = () => new Date().toISOString().slice(0, 10);
        const logsModal = document.getElementById('tenant-logs-modal');
        const logsBody = document.getElementById('logs-table-body');
        const logsStatus = document.getElementById('logs-status');
        const logsSummary = document.getElementById('logs-page-summary');
        const logsPrev = document.getElementById('logs-prev');
        const logsNext = document.getElementById('logs-next');

        const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        }[char]));

        const showLogsStatus = (message, tone = 'default') => {
            logsStatus.textContent = message;
            logsStatus.className = `px-5 py-4 text-sm ${tone === 'error' ? 'text-rose-700' : 'text-slate-600'}`;
            logsStatus.classList.remove('hidden');
        };

        const hideLogsStatus = () => {
            logsStatus.classList.add('hidden');
            logsStatus.textContent = '';
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

            rows.forEach((row, index) => {
                const supportCode = escapeHtml(row.support_code || row.request_id || '');
                const detailId = `log-detail-${index}-${Math.random().toString(16).slice(2)}`;
                const time = row.timestamp ? new Date(row.timestamp).toLocaleString() : '';
                const levelClass = row.level === 'error' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700';

                logsBody.insertAdjacentHTML('beforeend', `
                    <tr class="align-top hover:bg-slate-50">
                        <td class="whitespace-nowrap px-4 py-3 text-slate-600">${escapeHtml(time)}</td>
                        <td class="px-4 py-3"><span class="rounded-md px-2 py-1 text-xs font-semibold ${levelClass}">${escapeHtml(row.level || '')}/${escapeHtml(row.severity || '')}</span></td>
                        <td class="px-4 py-3 text-slate-700">${escapeHtml(row.module || '')}</td>
                        <td class="px-4 py-3 font-medium text-slate-900">${escapeHtml(row.event || '')}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <code class="text-xs text-slate-700">${supportCode}</code>
                                ${supportCode ? `<button type="button" class="rounded border border-slate-300 px-2 py-1 text-xs hover:bg-slate-100" data-copy="${supportCode}">Copy</button>` : ''}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-700">${escapeHtml(row.status_code || '')}</td>
                        <td class="max-w-md px-4 py-3 text-slate-700">
                            <div>${escapeHtml(row.safe_message || '')}</div>
                            <button type="button" class="mt-2 text-xs font-semibold text-indigo-700 hover:text-indigo-900" data-detail-toggle="${detailId}">Details</button>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate-600">${escapeHtml(row.location_id || '-')} / ${escapeHtml(row.user_id || '-')}</td>
                    </tr>
                    <tr id="${detailId}" class="hidden bg-slate-50">
                        <td colspan="8" class="px-4 py-3">
                            <dl class="grid gap-2 text-xs text-slate-700 md:grid-cols-2">
                                <div><dt class="font-semibold text-slate-500">Path</dt><dd class="mt-1 break-all">${escapeHtml(row.path || '')}</dd></div>
                                <div><dt class="font-semibold text-slate-500">Route</dt><dd class="mt-1 break-all">${escapeHtml(row.route || '')}</dd></div>
                                <div><dt class="font-semibold text-slate-500">Exception</dt><dd class="mt-1 break-all">${escapeHtml(row.exception_class || '')}</dd></div>
                                <div><dt class="font-semibold text-slate-500">Exception Message</dt><dd class="mt-1 break-all">${escapeHtml(row.exception_message || '')}</dd></div>
                                <div><dt class="font-semibold text-slate-500">File</dt><dd class="mt-1 break-all">${escapeHtml(row.file || '')}</dd></div>
                                <div><dt class="font-semibold text-slate-500">Line</dt><dd class="mt-1">${escapeHtml(row.line || '')}</dd></div>
                            </dl>
                        </td>
                    </tr>
                `);
            });
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

                logsState.lastPage = json.meta?.last_page || 1;
                renderLogs(json.data || []);

                if (!json.data || json.data.length === 0) {
                    showLogsStatus('No operational logs found for this tenant/date/filter.');
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
                logsModal?.classList.remove('hidden');
                logsModal?.classList.add('flex');
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

            const detailButton = event.target.closest('[data-detail-toggle]');
            if (detailButton) {
                document.getElementById(detailButton.dataset.detailToggle)?.classList.toggle('hidden');
            }
        });

        document.querySelectorAll('[data-modal-target]').forEach((row) => {
            row.addEventListener('click', () => {
                const modal = document.getElementById(row.dataset.modalTarget);
                modal?.classList.remove('hidden');
                modal?.classList.add('flex');
            });

            row.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    row.click();
                }
            });
        });

        document.querySelectorAll('[data-modal]').forEach((modal) => {
            const close = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
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
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                });
            }
        });
    </script>
@endsection
