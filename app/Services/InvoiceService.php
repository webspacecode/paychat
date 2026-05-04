<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Tenant;
use Illuminate\Support\Str;
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

        $uuid = Str::uuid();

        $orderData = $this->normalizeOrder($order);

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
}