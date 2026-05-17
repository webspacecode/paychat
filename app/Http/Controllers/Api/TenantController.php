<?php

namespace App\Http\Controllers\Api;


use App\Models\User;
use App\Models\Tenant;
use App\Models\Tenant\Location;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Jobs\SetupTenantJob;
use Intervention\Image\Laravel\Facades\Image;


class TenantController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'slug' => 'required|alpha_dash|unique:tenants,slug',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'industry' => 'required|string',

            // NEW
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'gst_number' => 'nullable|string',
            'is_gst_enabled' => 'nullable|boolean',
            'upi_id' => 'nullable|string',
            'enable_token_system' => 'nullable|boolean',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $sanitizedSlug = trim(strtolower($request->slug), '-');
        $sanitizedSlug = str_replace('-', '_', $sanitizedSlug);

        $dbName = 'tenant_' . $sanitizedSlug;

        // Create DB (light operation, keep it here or move later if needed)
        DB::statement("CREATE DATABASE IF NOT EXISTS `$dbName`");

        // Create tenant
        $tenant = Tenant::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'database' => $dbName,
            'industry' => $request->industry,
            'api_key' => $this->generateUniqueApiKey(),
        ]);

        // Create user
        $adminUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'tenant_id' => $tenant->id,
        ]);

        $setupData = [
            'phone' => $request->phone,
            'address' => $request->address,
            'gst_number' => $request->gst_number,
            'is_gst_enabled' => $request->is_gst_enabled,
            'upi_id' => $request->upi_id,
            'enable_token_system' => $request->enable_token_system,
        ];

        if ($request->hasFile('logo')) {
            try {
                $setupData['logo'] = $this->storeTenantLogo($request, $tenant);
            } catch (\Throwable $e) {
                Log::error('Tenant logo processing failed', [
                    'tenant_id' => $tenant->id,
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'message' => 'Logo could not be processed. Please upload a valid JPG, PNG, or WEBP image.',
                ], 422);
            }
        }

        // 🔥 Dispatch background job
        SetupTenantJob::dispatch($tenant, $dbName, $setupData);

        return response()->json([
            'message' => 'Tenant created. Setup is in progress...',
            'tenant' => $tenant,
            'user' => $adminUser,
        ], 201);
    }

    function generateUniqueApiKey()
    {
        do {
            $key = Str::random(32);
        } while (Tenant::where('api_key', $key)->exists());

        return $key;
    }

    private function storeTenantLogo(Request $request, Tenant $tenant): string
    {
        $timestamp = now()->timestamp;
        $filename = "{$tenant->slug}-{$timestamp}.webp";
        $path = "tenants/{$tenant->id}/logos/{$filename}";

        $imageContent = (string) Image::read($request->file('logo')->getRealPath())
            ->scaleDown(width: 512)
            ->toWebp(82);

        Storage::disk('public')->put($path, $imageContent);

        return "/storage/{$path}";
    }
}
