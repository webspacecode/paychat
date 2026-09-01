<?php

namespace App\Http\Controllers\Api;


use App\Models\User;
use App\Models\Tenant;
use App\Models\PaychatPricingPlan;
use App\Models\TenantOnboarding;
use App\Models\TenantPolicyAcceptance;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Jobs\SetupTenantJob;
use Intervention\Image\Laravel\Facades\Image;


class TenantController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'slug' => [
                'required',
                'string',
                'max:80',
                'regex:/^[A-Za-z0-9][A-Za-z0-9_-]*[A-Za-z0-9]$/',
                'unique:tenants,slug',
            ],
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'industry' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:1000',
            'gst_number' => 'nullable|string|max:20',
            'is_gst_enabled' => 'nullable|boolean',
            'upi_id' => 'nullable|string|max:100',
            'enable_token_system' => 'nullable|boolean',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'terms_accepted' => 'required|accepted',
            'terms_version' => 'nullable|string|max:100',
            'privacy_accepted' => 'required|accepted',
            'privacy_version' => 'nullable|string|max:100',
            'plan' => 'nullable|string|max:80',
        ]);

        $sanitizedSlug = $this->normalizeSlugForDatabase($validated['slug']);

        $dbName = 'tenant_' . $sanitizedSlug;

        if (strlen($dbName) > 64) {
            throw ValidationException::withMessages([
                'slug' => ['The slug is too long for tenant provisioning. Please choose a shorter slug.'],
            ]);
        }

        if (Tenant::where('database', $dbName)->exists()) {
            throw ValidationException::withMessages([
                'slug' => ['This slug conflicts with an existing tenant. Please choose another slug.'],
            ]);
        }

        $logoContent = null;

        if ($request->hasFile('logo')) {
            try {
                $logoContent = $this->prepareTenantLogoContent($request);
            } catch (\Throwable $e) {
                Log::error('Tenant logo processing failed', [
                    'slug' => $validated['slug'],
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'message' => 'Logo could not be processed. Please upload a valid JPG, PNG, or WEBP image.',
                    'support_code' => $request->attributes->get('request_id'),
                ], 422);
            }
        }

        DB::statement("CREATE DATABASE IF NOT EXISTS `$dbName`");

        $selectedPlan = $validated['plan'] ?? null;
        $selectedPlan = $selectedPlan
            && Schema::hasTable('paychat_pricing_plans')
            && PaychatPricingPlan::where('key', $selectedPlan)->where('is_active', true)->exists()
            ? $selectedPlan
            : 'trial';

        [$tenant, $adminUser, $onboarding, $setupData] = DB::connection('mysql')->transaction(function () use ($request, $validated, $dbName, $logoContent, $selectedPlan) {
            $tenant = Tenant::create([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'database' => $dbName,
                'industry' => $validated['industry'],
                'plan' => $selectedPlan,
                'api_key' => $this->generateUniqueApiKey(),
            ]);

            $logoPath = null;

            if ($logoContent !== null) {
                $logoPath = $this->storeTenantLogoContent($tenant, $logoContent);
            }

            $adminUser = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'tenant_id' => $tenant->id,
                'role' => 'owner',
            ]);

            $onboarding = TenantOnboarding::create([
                'tenant_id' => $tenant->id,
                'status' => 'pending',
            ]);

            $this->storePolicyAcceptances($request, $tenant, $adminUser, $validated);

            $setupData = [
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'gst_number' => $validated['gst_number'] ?? null,
                'is_gst_enabled' => $validated['is_gst_enabled'] ?? null,
                'upi_id' => $validated['upi_id'] ?? null,
                'enable_token_system' => $validated['enable_token_system'] ?? null,
                'logo' => $logoPath,
            ];

            return [$tenant, $adminUser, $onboarding, $setupData];
        });

        SetupTenantJob::dispatch($tenant, $dbName, $setupData);

        return response()->json([
            'message' => 'Tenant created. Setup is in progress...',
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'industry' => $tenant->industry,
                'plan' => $tenant->plan,
                'setup_status' => $onboarding->status,
            ],
            'user' => [
                'id' => $adminUser->id,
                'name' => $adminUser->name,
                'email' => $adminUser->email,
                'role' => $adminUser->role,
            ],
            'onboarding' => [
                'status' => $onboarding->status,
                'next_step' => 'setup_in_progress',
            ],
        ], 201);
    }

    public function onboardingStatus(string $tenantSlug)
    {
        $tenant = Tenant::with('onboarding')
            ->where('slug', $tenantSlug)
            ->firstOrFail();

        $onboarding = $tenant->onboarding;

        return response()->json([
            'tenant_slug' => $tenant->slug,
            'setup_status' => $onboarding?->status ?? 'unknown',
            'failed_reason' => $onboarding?->failed_reason,
            'setup_completed_at' => $onboarding?->setup_completed_at,
        ]);
    }

    function generateUniqueApiKey()
    {
        do {
            $key = Str::random(32);
        } while (Tenant::where('api_key', $key)->exists());

        return $key;
    }

    private function normalizeSlugForDatabase(string $slug): string
    {
        $normalized = trim(strtolower($slug), '-_');

        return preg_replace('/[-_]+/', '_', $normalized);
    }

    private function prepareTenantLogoContent(Request $request): string
    {
        return (string) Image::read($request->file('logo')->getRealPath())
            ->scaleDown(width: 512)
            ->toWebp(82);
    }

    private function storeTenantLogoContent(Tenant $tenant, string $imageContent): string
    {
        $timestamp = now()->timestamp;
        $filename = "{$tenant->slug}-{$timestamp}.webp";
        $path = "tenants/{$tenant->id}/logos/{$filename}";

        if (! Storage::disk('public')->put($path, $imageContent)) {
            throw new \RuntimeException('Tenant logo could not be stored.');
        }

        return "/storage/{$path}";
    }

    private function storePolicyAcceptances(Request $request, Tenant $tenant, User $user, array $validated): void
    {
        $acceptedAt = now();
        $common = [
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'accepted_at' => $acceptedAt,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            'source' => 'registration',
        ];

        TenantPolicyAcceptance::create($common + [
            'type' => 'terms',
            'version' => $validated['terms_version'] ?? null,
        ]);

        TenantPolicyAcceptance::create($common + [
            'type' => 'privacy',
            'version' => $validated['privacy_version'] ?? null,
        ]);
    }
}
