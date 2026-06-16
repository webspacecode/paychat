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
                $req->paper_size,
                $req->boolean('custinfo')
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

    public function view(Request $request, $uuid, InvoiceService $service)
    {
        
        return $service->view(
            $uuid,
            $request->boolean('custinfo')
        );
        
    }

    public function downloadPdf(Request $request, $uuid, InvoiceService $service)
    {
        return $service->downloadPdf($uuid, $request->boolean('custinfo'));
    }

    public function generatedView(Request $request, $uuid, InvoiceService $service)
    {
        
        return $service->generatedView(
            $uuid,
            $request->boolean('custinfo')
        );
        
    }

    public function viewToken(Request $request, $uuid, InvoiceService $service)
    {
        
        return $service->viewToken(
            $uuid,
            $request->boolean('custinfo')
        );
        
    }
}
