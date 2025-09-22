<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }
    
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed', // expect password_confirmation field
            'tenant_id' => 'required|exists:tenants,id',
        ]);

        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'tenant_id' => $request->tenant_id,
            'password' => bcrypt($request->password),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    public function me(Request $request)
    {
        // if ($request->user()->tenant->database) {
        //     // Assume $tenant has db credentials
        //     Config::set('database.connections.tenant', [
        //         'driver' => env('DB_CONNECTION', 'mysql'),
        //         'host' => env('DB_HOST', '127.0.0.1'),
        //         'port' => env('DB_PORT', '3306'),
        //         'database' => $request->user()->tenant->database,            // Tenant-specific
        //         'username' => env('DB_USERNAME', 'root'),
        //         'password' => env('DB_PASSWORD', 'mypass'),
        //         'charset' => 'utf8mb4',
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'prefix' => '',
        //         'strict' => true,
        //         'engine' => null,
        //     ]);
        // }

        // dd(DB::connection('tenant')->getDatabaseName());

        
        return response()->json($request->user());
    }

}

