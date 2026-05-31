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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500">No tenants registered yet.</td>
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

    <script>
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
