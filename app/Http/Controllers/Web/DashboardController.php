<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DemoLead;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantOperationalLogReader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const TENANT_USER_ROLES = [
        'owner',
        'manager',
        'cashier',
        'kitchen',
        'waiter',
        'accountant',
    ];

    private const DEMO_LEAD_STATUSES = [
        'new',
        'contacted',
        'demo_scheduled',
        'converted',
        'not_interested',
        'closed',
    ];

    public function master(): View
    {
        $tenants = Tenant::with(['taxConfig', 'branding', 'users' => fn ($query) => $query->orderBy('role')->orderBy('name')])
            ->latest()
            ->get();

        $demoLeads = DemoLead::latest()
            ->get();

        $demoLeadStatuses = self::DEMO_LEAD_STATUSES;

        return view('dashboards.master', compact('tenants', 'demoLeads', 'demoLeadStatuses'));
    }

    public function tenant(Request $request): View|RedirectResponse
    {
        if ($request->user()->isMaster()) {
            return redirect()->route('master.dashboard');
        }

        return view('dashboards.tenant', [
            'tenant' => $request->user()->tenant?->load(['taxConfig', 'branding', 'onboarding']),
        ]);
    }

    public function storeTenantUser(Request $request, Tenant $tenant): RedirectResponse
    {
        $request->session()->flash('tenant_user_tenant_id', $tenant->id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string', Rule::in(self::TENANT_USER_ROLES)],
            'generate_password' => ['nullable', 'boolean'],
            'password' => ['nullable', 'required_without:generate_password', 'string', 'min:8', 'confirmed'],
        ]);

        $password = $request->boolean('generate_password')
            ? Str::random(16)
            : $validated['password'];

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'tenant_id' => $tenant->id,
            'role' => $validated['role'],
            'password' => Hash::make($password),
        ]);

        $redirect = back()
            ->with('status', "Tenant user created for {$tenant->name}.")
            ->with('tenant_user_tenant_id', $tenant->id);

        if ($request->boolean('generate_password')) {
            $redirect->with('generated_password', $password);
        }

        return $redirect;
    }

    public function resetTenantPassword(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where('tenant_id', $tenant->id),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::where('tenant_id', $tenant->id)->findOrFail($validated['user_id']);

        $user->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        return back()->with('status', "Password reset for {$user->email}.");
    }

    public function updateDemoLead(Request $request, DemoLead $demoLead): RedirectResponse
    {
        $request->session()->flash('demo_lead_id', $demoLead->id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'business_name' => ['required', 'string', 'max:255'],
            'business_type' => ['nullable', 'string', 'max:255'],
            'counters' => ['nullable', 'string', 'max:50'],
            'preferred_demo_time' => ['nullable', 'date'],
            'source' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'string', Rule::in(self::DEMO_LEAD_STATUSES)],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $demoLead->update($validated);

        return back()->with('status', "Demo lead updated for {$demoLead->name}.");
    }

    public function tenantLogs(Request $request, Tenant $tenant, TenantOperationalLogReader $reader): JsonResponse
    {
        $validated = $this->validateLogFilters($request);

        return response()->json($reader->read($tenant, $validated));
    }

    public function tenantLogDates(Tenant $tenant, TenantOperationalLogReader $reader): JsonResponse
    {
        return response()->json([
            'data' => $reader->availableDates($tenant),
        ]);
    }

    public function systemLogs(Request $request, TenantOperationalLogReader $reader): JsonResponse
    {
        $validated = $this->validateLogFilters($request);

        return response()->json($reader->readSystem($validated));
    }

    public function systemLogDates(TenantOperationalLogReader $reader): JsonResponse
    {
        return response()->json([
            'data' => $reader->availableSystemDates(),
        ]);
    }

    private function validateLogFilters(Request $request): array
    {
        return $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'module' => ['nullable', 'string', 'max:50'],
            'level' => ['nullable', 'string', 'max:20'],
            'severity' => ['nullable', 'string', 'max:20'],
            'event' => ['nullable', 'string', 'max:100'],
            'support_code' => ['nullable', 'string', 'max:120'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);
    }
}
