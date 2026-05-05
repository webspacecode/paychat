<?php

namespace App\Http\Controllers\Api;

use App\Models\Tenant;
use Illuminate\Http\Request;
use App\Services\InvoiceService;
use App\Http\Controllers\Controller;

class InvoiceController extends Controller
{
    public function generate(Request $req, InvoiceService $service)
    {   
        $apiKey = $req->header('x-api-key');
        $tenant = Tenant::where('api_key', $apiKey)->first();

        return $service->generate(
            $req->order,
            $tenant,
            $req->industry,
            $req->paper_size
        );
    }

    public function view($uuid, InvoiceService $service)
    {
        
        return $service->view(
            $uuid
        );
        
    }

    public function viewToken($uuid, InvoiceService $service)
    {
        
        return $service->viewToken(
            $uuid
        );
        
    }
}