<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Jobs\SetupTenantJob;
use App\Models\Tenant;
use App\Models\TenantOnboarding;
use App\Models\TenantPolicyAcceptance;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredTenantController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => [
                'required',
                'string',
                'max:80',
                'regex:/^[A-Za-z0-9][A-Za-z0-9_-]*[A-Za-z0-9]$/',
                'unique:tenants,slug',
            ],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'industry' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
            'gst_number' => ['nullable', 'string', 'max:20'],
            'is_gst_enabled' => ['nullable', 'boolean'],
            'terms_accepted' => ['required', 'accepted'],
        ]);

        $dbName = 'tenant_' . preg_replace('/[-_]+/', '_', trim(strtolower($validated['slug']), '-_'));

        if (strlen($dbName) > 64) {
            throw ValidationException::withMessages([
                'slug' => 'The slug is too long for tenant provisioning. Please choose a shorter slug.',
            ]);
        }

        if (Tenant::where('database', $dbName)->exists()) {
            throw ValidationException::withMessages([
                'slug' => 'This slug conflicts with an existing tenant. Please choose another slug.',
            ]);
        }

        DB::statement("CREATE DATABASE IF NOT EXISTS `$dbName`");

        [$tenant, $user] = DB::connection('mysql')->transaction(function () use ($request, $validated, $dbName) {
            $tenant = Tenant::create([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'database' => $dbName,
                'industry' => $validated['industry'],
                'api_key' => $this->generateUniqueApiKey(),
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'tenant_id' => $tenant->id,
                'role' => 'owner',
            ]);

            TenantOnboarding::create([
                'tenant_id' => $tenant->id,
                'status' => 'pending',
            ]);

            $this->storeAcceptance($request, $tenant, $user);

            return [$tenant, $user];
        });

        SetupTenantJob::dispatch($tenant, $dbName, [
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'gst_number' => $validated['gst_number'] ?? null,
            'is_gst_enabled' => $validated['is_gst_enabled'] ?? false,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('tenant.dashboard')
            ->with('status', 'Your tenant account was created. Setup is running in the background.');
    }

    private function generateUniqueApiKey(): string
    {
        do {
            $key = Str::random(32);
        } while (Tenant::where('api_key', $key)->exists());

        return $key;
    }

    private function storeAcceptance(Request $request, Tenant $tenant, User $user): void
    {
        TenantPolicyAcceptance::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'type' => 'terms',
            'version' => 'web-registration',
            'accepted_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            'source' => 'web-registration',
        ]);
    }
}
