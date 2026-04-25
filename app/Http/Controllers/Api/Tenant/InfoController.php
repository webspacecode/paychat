<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Models\Tenant;
use Illuminate\Http\Request;
use App\Services\InvoiceService;
use App\Http\Controllers\Controller;

class InfoController extends Controller
{
    public function index(Request $req)
    {   
        $apiKey = $req->header('x-api-key');

        $tenant = Tenant::with(['branding', 'taxConfig'])
            ->where('api_key', $apiKey)
            ->first();

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API Key'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'slug' => $tenant->slug,
                    'email' => $tenant->email,
                    'phone' => $tenant->phone,
                    'logo' => $tenant->logo,
                    'api_key' => $tenant->api_key,
                    'address' => $tenant->address,
                    'industry' => $tenant->industry,
                ],

                'branding' => $tenant->branding,

                'tax' => $tenant->taxConfig,
            ]
        ]);
    }

    public function list(Request $req)
    {   

        $tenants = Tenant::with(['branding', 'taxConfig'])
        ->whereNotNull('api_key')
        ->where('api_key', '!=', '')
        ->get();

        if (!$tenants) {
            return response()->json([
                'success' => false,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'tenants' => $tenants
            ]
        ]);
    }
}