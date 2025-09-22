<?php

namespace App\Http\Controllers\Api;


use App\Models\User;
use App\Models\Tenant;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

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

        $sanitizedSlug = str_replace('-', '_', strtolower($request->slug));
        $dbName = 'tenant_' . $sanitizedSlug;

        // Create tenant DB
        DB::statement("CREATE DATABASE IF NOT EXISTS `$dbName`");

        // Store tenant info in central DB
        $tenant = Tenant::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'database' => $dbName,
            'industry' => $request->industry,
        ]);

        // Create admin user in central DB
        $adminUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'tenant_id' => $tenant->id, // make sure you have tenant_id column in `users`
        ]);

        // Assume $tenant has db credentials
        Config::set('database.connections.tenant', [
            'driver' => env('DB_CONNECTION', 'mysql'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $dbName,            // Tenant-specific
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', 'mypass'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);

        // Purge any old connection (if reused)
        DB::purge('tenant');
        DB::reconnect('tenant');

        // Run tenant migrations (optional: seeders)
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => base_path('database/migrations/tenant'),
            '--realpath' => true,
            '--force' => true,
        ]);

        return response()->json(['message' => 'Tenant registered', 'tenant' => $tenant], 201);
    }
}
