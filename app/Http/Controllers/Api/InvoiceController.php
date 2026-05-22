<?php

namespace App\Http\Controllers\Api;

use App\Models\Tenant;
use Illuminate\Http\Request;
use App\Services\InvoiceService;
use App\Http\Controllers\Controller;
use App\Support\Observability;
use Throwable;

class InvoiceController extends Controller
{
    public function generate(Request $req, InvoiceService $service)
    {   
        $apiKey = $req->header('x-api-key');
        $tenant = Tenant::where('api_key', $apiKey)->first();

        try {
            return $service->generate(
                $req->order,
                $tenant,
                $req->industry,
                $req->paper_size
            );
        } catch (Throwable $e) {
            Observability::logFailure('invoice.generate.failed', $e, [
                'tenant_slug' => $tenant?->slug,
                'tenant_id' => $tenant?->id,
                'order_id' => data_get($req->order, 'id') ?? data_get($req->order, 'data.data.id'),
                'action' => 'invoice.generate',
            ], $req);

            throw $e;
        }
    }

    public function view($uuid, InvoiceService $service)
    {
        
        return $service->view(
            $uuid
        );
        
    }

    public function downloadPdf($uuid, InvoiceService $service)
    {
        return $service->downloadPdf($uuid);
    }

    public function generatedView($uuid, InvoiceService $service)
    {
        
        return $service->generatedView(
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
