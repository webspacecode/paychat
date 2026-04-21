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
use App\Jobs\SetupTenantJob;


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

        // 🔥 Dispatch background job
        SetupTenantJob::dispatch($tenant, $dbName);

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
}
