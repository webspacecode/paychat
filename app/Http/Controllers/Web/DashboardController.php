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
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function master(): View
    {
        $tenants = Tenant::with(['taxConfig', 'branding', 'users' => fn ($query) => $query->orderBy('role')->orderBy('name')])
            ->latest()
            ->get();

        $demoLeads = DemoLead::latest()
            ->get();

        return view('dashboards.master', compact('tenants', 'demoLeads'));
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

    public function tenantLogs(Request $request, Tenant $tenant, TenantOperationalLogReader $reader): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'module' => ['nullable', 'string', 'max:50'],
            'level' => ['nullable', 'string', 'max:20'],
            'severity' => ['nullable', 'string', 'max:20'],
            'event' => ['nullable', 'string', 'max:100'],
            'support_code' => ['nullable', 'string', 'max:120'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        return response()->json($reader->read($tenant, $validated));
    }

    public function tenantLogDates(Tenant $tenant, TenantOperationalLogReader $reader): JsonResponse
    {
        return response()->json([
            'data' => $reader->availableDates($tenant),
        ]);
    }
}
