<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\Tenant\Order;
use App\Models\ReviewSession;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Generator;

class InvoiceService
{
    public function generate($order,$tenant,$industry,$paper)
    {
        $config = config("invoice.industries.$industry");
    
        if(!$config){
            throw new \Exception("Invalid industry");
        }

        $template = $config['templates'][$paper] ?? null;

        if(!$template){
            throw new \Exception("Template not found");
        }

        $orderData = $this->normalizeOrder($order);
        $orderId = $this->extractOrderId($orderData);

        if (!$orderId) {
            throw new \Exception("Order id missing");
        }

        $this->configureTenantConnection($tenant);

        $tenantOrder = Order::find($orderId);

        if (!$tenantOrder) {
            throw new \Exception("Order not found");
        }

        if ($tenantOrder->status === 'cancelled') {
            throw new \Exception("Cancelled order cannot generate invoice");
        }

        $existingInvoice = $this->findExistingInvoice($tenant->id, $orderId);

        if ($existingInvoice) {
            $this->attachExistingInvoiceToOrder($orderId, $existingInvoice);

            return $this->generatedView($existingInvoice->uuid);
        }

        $uuid = $this->generateInvoiceNumber();
        $reviewToken = 'PCRV-' . strtoupper(Str::uuid()->toString());

        $orderData['review_token'] =  $reviewToken;

        $url = url("/pos#/invoices/$uuid");

        DB::connection('mysql')->beginTransaction();
        DB::connection('tenant')->beginTransaction();

        try {
            $invoice = Invoice::create([
                'tenant_id'=>$tenant->id,
                'order_id'=>$orderId,
                'uuid'=>$uuid,
                'industry'=>$industry,
                'paper_size'=>$paper,
                'order_data'=>$orderData
            ]);

            $this->updateOrderInvoiceData($orderId, $invoice, $url);

            // Now create a review token
            // We will add condition here based on settings
            // It runs only if customer review is on
            ReviewSession::create([
                'tenant_id' => $tenant->id,
                'tenant_slug' => $tenant->slug,
                'tenant_api_key' => $tenant->api_key,
                'invoice_number' => $uuid,
                'order_id' => $orderId,
                'customer_name' => $this->nullableTrim(data_get($orderData, 'customer.name')),
                'customer_phone' => $this->nullableTrim(data_get($orderData, 'customer.phone')),
                'review_token' => $reviewToken,
                'expires_at' => now()->addMonths(6),
            ]);

            DB::connection('tenant')->commit();
            DB::connection('mysql')->commit();
        } catch (\Throwable $e) {
            DB::connection('tenant')->rollBack();
            DB::connection('mysql')->rollBack();

            $existingInvoice = $this->findExistingInvoice($tenant->id, $orderId);

            if ($existingInvoice) {
                $this->attachExistingInvoiceToOrder($orderId, $existingInvoice);

                return $this->generatedView($existingInvoice->uuid);
            }

            throw $e;
        }

        $token = data_get($orderData, 'token.token_code');


        $qrCode = new Generator();

        $qr = null;
        $kitchenQr = null;
        $tokenQr = null;
        $tokenUrl = null;

        try {
            $qr = $qrCode->format('svg')->size(120)->generate($url);
            if ($token) {
                $kitchenUrl = url("pos#/kitchen?mode=staff&token=$token");
                $tokenUrl = url("/pos#/tokens/$uuid");

                $kitchenQr = $qrCode->format('svg')->size(120)->generate($kitchenUrl);
                $tokenQr = $qrCode->format('svg')->size(120)->generate($tokenUrl);

            }
        } catch (\Exception $e) {
            $qr = null; // fallback (important for production)
            $kitchenQr = null; // fallback (important for production)
            $tokenQr = null;
        }
        
        $totals = $this->calculateGST($orderData,$tenant->taxConfig);

        // We will add condition here based on settings 
        // It runs only if customer display is on
        event(new \App\Events\CustomerDisplayUpdated([
            'uuid' => $uuid,
        ]));
        
        return [
            'html'=>view($template,[
                'order'=>$orderData,
                'branding'=>$tenant->branding,
                'tax'=>$tenant->taxConfig,
                'totals'=>$totals,
                'qr'=>$qr,
                'url'=>$url,
                'config'=>$config
            ])->render(),
            'url'=>$url,
            'qr'=> base64_encode($qr),
            'kitchenQr'=> base64_encode($kitchenQr),
            'tokenQr'=> base64_encode($tokenQr),
            'tokenUrl' => $tokenUrl,
        ];
    }

    private function normalizeOrder($order)
    {
        return data_get($order, 'data.data', $order);
    }

    private function extractOrderId(array $order): ?int
    {
        $id = data_get($order, 'id');

        return is_numeric($id) ? (int) $id : null;
    }

    private function findExistingInvoice(int $tenantId, int $orderId): ?Invoice
    {
        $invoice = Invoice::where('tenant_id', $tenantId)
            ->where('order_id', $orderId)
            ->first();

        if ($invoice) {
            return $invoice;
        }

        return Invoice::where('tenant_id', $tenantId)
            ->where(function ($query) use ($orderId) {
                $query->where('order_data->id', $orderId)
                    ->orWhere('order_data->id', (string) $orderId);
            })
            ->oldest()
            ->first();
    }

    private function configureTenantConnection(Tenant $tenant): void
    {
        $base = config('database.connections.mysql');

        Config::set('database.connections.tenant', array_merge($base, [
            'database' => $tenant->database,
        ]));

        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    private function updateOrderInvoiceData(int $orderId, Invoice $invoice, string $url): void
    {
        $order = Order::whereKey($orderId)->lockForUpdate()->first();

        if (!$order) {
            throw new \Exception("Order not found");
        }

        $meta = $order->meta ?? [];
        $meta['invoice'] = [
            'id' => $invoice->id,
            'number' => $invoice->uuid,
            'url' => $url,
        ];

        $order->update([
            'invoice_id' => $invoice->id,
            'invoice_no' => $invoice->uuid,
            'meta' => $meta,
        ]);
    }

    private function attachExistingInvoiceToOrder(int $orderId, Invoice $invoice): void
    {
        DB::connection('mysql')->transaction(function () use ($orderId, $invoice) {
            if (!$invoice->order_id) {
                $invoice->update(['order_id' => $orderId]);
            }
        });

        DB::connection('tenant')->transaction(function () use ($orderId, $invoice) {
            $this->updateOrderInvoiceData(
                $orderId,
                $invoice,
                url("/pos#/invoices/{$invoice->uuid}")
            );
        });
    }

    private function nullableTrim($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }


    public function view($uuid)
    {
        $inv = \App\Models\Invoice::where('uuid',$uuid)->firstOrFail();

        $config = config("invoice.industries.$inv->industry");
    
        if(!$config){
            throw new \Exception("Invalid industry");
        }

        $template = $config['templates'][$inv->paper_size] ?? null;
        
        $tenant = Tenant::where('id', $inv->tenant_id)->first();

        $totals = $this->calculateGST($inv->order_data,$tenant->taxConfig);

        return view(
            $template,
            [
                'order'=>$inv->order_data,
                'branding'=>$inv->tenant->branding,
                'tax'=>$inv->tenant->taxConfig,
                'totals'=>$totals,
                'qr'=>null,
                'url'=>request()->url()
            ]
        );
    }

    public function viewToken($uuid)
    {
        $inv = \App\Models\Invoice::where('uuid',$uuid)->firstOrFail();

        $config = config("invoice.industries.$inv->industry");
    
        if(!$config){
            throw new \Exception("Invalid industry");
        }

        $template = $config['templates'][$inv->paper_size] ?? null;
        
        $tenant = Tenant::where('id', $inv->tenant_id)->first();

        $totals = $this->calculateGST($inv->order_data,$tenant->taxConfig);

        $qrCode = new Generator();

        $url = url("/pos#/invoices/$uuid");
        $qr = null;
        $kitchenQr = null;

        try {
            $qr = $qrCode->format('svg')->size(120)->generate($url);
            $token = $inv->order_data['token']['token_code'] ?? null;
            if ($token) {
                $kitchenUrl = url("pos#/kitchen?mode=staff&token=$token");
                $kitchenQr = $qrCode->format('svg')->size(120)->generate($kitchenUrl);
            }
        } catch (\Exception $e) {
            $qr = null; // fallback (important for production)
            $kitchenQr = null; // fallback (important for production)
        }

        return [
            'orderData' => $inv->order_data,
            'token' => $inv->order_data['token'] ?? null,
            'qr'=> base64_encode($qr),
            'kitchenQr'=> base64_encode($kitchenQr),
        ];
    }

    public function generatedView($uuid)
    {   
        $inv = \App\Models\Invoice::where('uuid',$uuid)->firstOrFail();

        $config = config("invoice.industries.$inv->industry");

    
        if(!$config){
            throw new \Exception("Invalid industry");
        }

        $template = $config['templates'][$inv->paper_size] ?? null;

        $tenant = Tenant::where('id', $inv->tenant_id)->first();

        if(!$template){
            throw new \Exception("Template not found");
        }

        $order = $inv->order_data;
        $orderData = $this->normalizeOrder($order);

        
        $url = url("/pos#/invoices/$uuid");
        
        $token = $order['token']['token_code'] ?? null;


        // dd($order, $token);
        $qrCode = new Generator();

        $qr = null;
        $kitchenQr = null;
        $tokenQr = null;
        $tokenUrl = null;

        try {
            $qr = $qrCode->format('svg')->size(120)->generate($url);
            if ($token) {
                $kitchenUrl = url("pos#/kitchen?mode=staff&token=$token");
                $tokenUrl = url("/pos#/tokens/$uuid");

                $kitchenQr = $qrCode->format('svg')->size(120)->generate($kitchenUrl);
                $tokenQr = $qrCode->format('svg')->size(120)->generate($tokenUrl);

            }
        } catch (\Exception $e) {
            $qr = null; // fallback (important for production)
            $kitchenQr = null; // fallback (important for production)
            $tokenQr = null;
        }
        
        $totals = $this->calculateGST($orderData,$tenant->taxConfig);
        
        return [
            'html'=>view($template,[
                'order'=>$orderData,
                'branding'=>$tenant->branding,
                'tax'=>$tenant->taxConfig,
                'totals'=>$totals,
                'qr'=>$qr,
                'url'=>$url,
                'config'=>$config
            ])->render(),
            'url'=>$url,
            'qr'=> base64_encode($qr),
            'kitchenQr'=> base64_encode($kitchenQr),
            'tokenQr'=> base64_encode($tokenQr),
            'tokenUrl' => $tokenUrl,
            'orderData' => $orderData,
            'tokenData' => $orderData['token'] ?? null
        ];
    }

    private function calculateGST($order,$tax)
    {
        // dd($order);
        $subtotal = collect($order['items'] ?? [])
            ->sum(fn($i)=>$i['quantity']*$i['price']);

        if(!$tax || !$tax->is_gst_enabled){
            return [
                'subtotal'=>$subtotal,
                'gst'=>0,
                'cgst'=>0,
                'sgst'=>0,
                'total'=>$subtotal
            ];
        }

        $rate = 0.18;

        $gst = $subtotal * $rate;
        $cgst = $gst / 2;
        $sgst = $gst / 2;

        return [
            'subtotal'=>$subtotal,
            'gst'=>$gst,
            'cgst'=>$cgst,
            'sgst'=>$sgst,
            'total'=>$subtotal + $gst
        ];
    }

    public static function generateInvoiceNumber(): string
    {
        $characters = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

        do {

            $random = '';

            for ($i = 0; $i < 8; $i++) {
                $random .= $characters[random_int(0, strlen($characters) - 1)];
            }

            $invoice = 'PC' . now()->format('y') . '-' . $random;

        } while (
            DB::table('invoices')
                ->where('uuid', $invoice)
                ->exists()
        );

        return $invoice;
    }
}
