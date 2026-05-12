<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\ReviewSession;
use Illuminate\Support\Str;
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

        $uuid = $this->generateInvoiceNumber();
        $reviewToken = 'PCRV-' . strtoupper(Str::uuid()->toString());

        $orderData = $this->normalizeOrder($order);
        $orderData['review_token'] =  $reviewToken;

        Invoice::create([
            'tenant_id'=>$tenant->id,
            'uuid'=>$uuid,
            'industry'=>$industry,
            'paper_size'=>$paper,
            'order_data'=>$orderData
        ]);
        
        $url = url("/pos#/invoices/$uuid");
        
        $token = $order['data']['data']['token']['token_code'] ?? null;


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

        // Now create a review token 
        $name = !empty(trim($order['data']['data']['customer']['name'] ?? ''))
                ? $order['data']['data']['customer']['name']
                : null;
        $phone = !empty(trim($order['data']['data']['customer']['phone'] ?? ''))
                ? $order['data']['data']['customer']['phone']
                : null;

        // We will add condition here based on settings
        // It runs only if customer review is on
        ReviewSession::create([
            'tenant_id' => $tenant->id,
            'tenant_slug' => $tenant->slug,
            'tenant_api_key' => $tenant->api_key,
            'invoice_number' => $uuid,
            'order_id' => $order['data']['data']['id'],
            'customer_name' => $name,
            'customer_phone' => $phone,
            'review_token' => $reviewToken,
            'expires_at' => now()->addMonths(6),
        ]);

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

        try {
            $qr = $qrCode->format('svg')->size(120)->generate($url);
            $token = $inv->order_data['token']['token_code'];
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
            'token' => $inv->order_data['token'],
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
            'tokenData' => $orderData['token']
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