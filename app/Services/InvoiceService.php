<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\Tenant\Order;
use App\Models\ReviewSession;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Support\IndustryNormalizer;
use App\Support\Observability;
use SimpleSoftwareIO\QrCode\Generator;
use Spatie\Browsershot\Browsershot;

class InvoiceService
{
    public function generate($order,$tenant,$industry,$paper, bool $includeCustomerInfo = false)
    {
        return $this->generateInternal($order, $tenant, $industry, $paper, null, $includeCustomerInfo);
    }

    public function generateWithPreferredInvoiceNumber($order, $tenant, $industry, $paper, string $preferredInvoiceNumber, bool $includeCustomerInfo = false)
    {
        return $this->generateInternal($order, $tenant, $industry, $paper, $preferredInvoiceNumber, $includeCustomerInfo);
    }

    private function generateInternal($order, $tenant, $industry, $paper, ?string $preferredInvoiceNumber = null, bool $includeCustomerInfo = false)
    {
        $startedAt = microtime(true);
        [$config, $template] = $this->resolveInvoiceTemplate($industry, $paper);

        $orderData = $this->normalizeOrder($order);
        $orderId = $this->extractOrderId($orderData);
        $preferredInvoiceNumber = $this->normalizePreferredInvoiceNumber($preferredInvoiceNumber);

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

        $this->logInvoiceConnectionContext('invoice.generation.connection_context', $tenant, $orderId);

        $existingInvoice = $this->findExistingInvoice($tenant->id, $orderId);

        if ($existingInvoice) {
            if ($preferredInvoiceNumber && $existingInvoice->uuid !== $preferredInvoiceNumber) {
                throw new \RuntimeException('Offline invoice number already exists. Cannot change customer-facing invoice number.');
            }

            $this->attachExistingInvoiceToOrder($orderId, $existingInvoice);

            return $this->generatedView($existingInvoice->uuid, $includeCustomerInfo);
        }

        if ($preferredInvoiceNumber) {
            $existingPreferredInvoice = $this->findInvoiceByNumber($preferredInvoiceNumber);

            if ($existingPreferredInvoice) {
                if ($this->canReusePreferredInvoice($existingPreferredInvoice, $tenant->id, $orderId, $orderData)) {
                    $this->attachExistingInvoiceToOrder($orderId, $existingPreferredInvoice);

                    return $this->generatedView($existingPreferredInvoice->uuid, $includeCustomerInfo);
                }

                throw new \RuntimeException('Offline invoice number already exists. Cannot change customer-facing invoice number.');
            }
        }

        $uuid = $preferredInvoiceNumber ?: $this->generateInvoiceNumber();
        $reviewToken = 'PCRV-' . strtoupper(Str::uuid()->toString());

        $orderData['review_token'] =  $reviewToken;

        $url = $this->publicInvoiceUrl($uuid, $includeCustomerInfo);

        DB::connection('mysql')->beginTransaction();
        DB::connection('tenant')->beginTransaction();

        try {
            $invoice = $this->invoiceQuery()->create([
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

            $this->logInvoiceConnectionContext('invoice.created.connection_context', $tenant, $orderId, [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->uuid,
            ]);

            DB::connection('tenant')->commit();
            DB::connection('mysql')->commit();
        } catch (\Throwable $e) {
            DB::connection('tenant')->rollBack();
            DB::connection('mysql')->rollBack();

            $existingInvoice = $this->findExistingInvoice($tenant->id, $orderId);

            if ($existingInvoice) {
                $this->attachExistingInvoiceToOrder($orderId, $existingInvoice);

                return $this->generatedView($existingInvoice->uuid, $includeCustomerInfo);
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
                $tokenUrl = $this->publicTokenUrl($uuid, $includeCustomerInfo);

                $kitchenQr = $qrCode->format('svg')->size(120)->generate($kitchenUrl);
                $tokenQr = $qrCode->format('svg')->size(120)->generate($tokenUrl);

            }
        } catch (\Exception $e) {
            Observability::logWarning('invoice.qr_generation.failed', $e, [
                'tenant_id' => $tenant->id,
                'tenant_slug' => $tenant->slug,
                'order_id' => $orderId,
                'invoice_number' => $uuid,
                'paper_size' => $paper,
            ]);
            $qr = null; // fallback (important for production)
            $kitchenQr = null; // fallback (important for production)
            $tokenQr = null;
        }
        
        $totals = $this->calculateGST($orderData,$tenant->taxConfig);
        $receipt = $this->buildReceiptData($orderData, $tenant, $uuid, $url, $qr, null, false, $includeCustomerInfo);

        // We will add condition here based on settings
        // It runs only if customer display is on
        try {
            event(new \App\Events\CustomerDisplayUpdated([
                'uuid' => $uuid,
            ]));
        } catch (\Throwable $e) {
            Observability::logWarning('invoice.customer_display_broadcast.failed', $e, [
                'tenant_id' => $tenant->id,
                'tenant_slug' => $tenant->slug,
                'order_id' => $orderId,
                'invoice_number' => $uuid,
                'error_code' => 'broadcast_failed',
            ]);
        }

        Observability::logInfo('invoice.generated', [
            'tenant_id' => $tenant->id,
            'tenant_slug' => $tenant->slug,
            'order_id' => $orderId,
            'invoice_number' => $uuid,
            'paper_size' => $paper,
            'template' => $template,
            'has_invoice_qr' => $qr !== null,
            'has_kitchen_qr' => $kitchenQr !== null,
            'has_token_qr' => $tokenQr !== null,
            'duration_ms' => Observability::durationMs($startedAt),
        ]);
        
        return [
            'html'=>view($template,[
                'order'=>$orderData,
                'branding'=>$tenant->branding,
                'tax'=>$tenant->taxConfig,
                'totals'=>$totals,
                'receipt'=>$receipt,
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

    private function publicInvoiceUrl(string $uuid, bool $includeCustomerInfo = false): string
    {
        return url("/billing/invoices/$uuid") . ($includeCustomerInfo ? '?custinfo=1' : '');
    }

    private function apiInvoiceUrl(string $uuid, bool $includeCustomerInfo = false): string
    {
        return url("/api/invoice/$uuid") . ($includeCustomerInfo ? '?custinfo=1' : '');
    }

    private function publicTokenUrl(string $uuid, bool $includeCustomerInfo = false): string
    {
        return url("/billing/tokens/$uuid") . ($includeCustomerInfo ? '?custinfo=1' : '');
    }

    private function includeCustomerInfo(?bool $includeCustomerInfo = null): bool
    {
        return $includeCustomerInfo ?? request()->boolean('custinfo');
    }

    private function resolveInvoiceTemplate(?string $industry, ?string $paper): array
    {
        $normalizedIndustry = IndustryNormalizer::normalize($industry);
        $config = config("invoice.industries.$normalizedIndustry");

        if (!$config) {
            Observability::logWarningMessage('invoice.template.invalid_industry', [
                'industry' => $industry,
                'normalized_industry' => $normalizedIndustry,
                'paper_size' => $paper,
            ]);
            throw new \Exception("Invalid industry");
        }

        $template = $config['templates'][$paper] ?? null;

        if ($template && view()->exists($template)) {
            Observability::logInfo('invoice.template.resolved', [
                'industry' => $normalizedIndustry,
                'paper_size' => $paper,
                'template' => $template,
                'fallback' => false,
            ]);
            return [$config, $template];
        }

        if ($normalizedIndustry === 'services') {
            $fallbackTemplate = $this->resolveServicesFallbackTemplate($paper);

            if ($fallbackTemplate) {
                Observability::logWarningMessage('invoice.template.fallback_used', [
                    'industry' => $normalizedIndustry,
                    'paper_size' => $paper,
                    'configured_template' => $template,
                    'fallback_template' => $fallbackTemplate,
                ]);
                return [$config, $fallbackTemplate];
            }
        }

        if (!$template) {
            Observability::logWarningMessage('invoice.template.missing_config', [
                'industry' => $normalizedIndustry,
                'paper_size' => $paper,
            ]);
            throw new \Exception("Template not found");
        }

        Observability::logWarningMessage('invoice.template.view_missing', [
            'industry' => $normalizedIndustry,
            'paper_size' => $paper,
            'template' => $template,
        ]);

        throw new \Exception("Template not found");
    }

    private function resolveServicesFallbackTemplate(?string $paper): ?string
    {
        $candidates = array_filter([
            $paper ? "invoices.services.$paper" : null,
            $paper ? config("invoice.industries.cafe.templates.$paper") : null,
            $paper === 'a4' ? 'invoices.cafe.a4' : null,
            'invoices.cafe.a4',
            config('invoice.industries.cafe.templates.80mm'),
        ]);

        foreach ($candidates as $template) {
            if (view()->exists($template)) {
                return $template;
            }
        }

        return null;
    }

    private function extractOrderId(array $order): ?int
    {
        $id = data_get($order, 'id');

        return is_numeric($id) ? (int) $id : null;
    }

    private function findExistingInvoice(int $tenantId, int $orderId): ?Invoice
    {
        $invoice = $this->invoiceQuery()
            ->where('tenant_id', $tenantId)
            ->where('order_id', $orderId)
            ->first();

        if ($invoice) {
            return $invoice;
        }

        return $this->invoiceQuery()
            ->where('tenant_id', $tenantId)
            ->where(function ($query) use ($orderId) {
                $query->where('order_data->id', $orderId)
                    ->orWhere('order_data->id', (string) $orderId);
            })
            ->oldest()
            ->first();
    }

    private function findInvoiceByNumber(string $invoiceNumber): ?Invoice
    {
        return $this->invoiceQuery()
            ->where('uuid', $invoiceNumber)
            ->first();
    }

    private function canReusePreferredInvoice(Invoice $invoice, int $tenantId, int $orderId, array $orderData): bool
    {
        if ((int) $invoice->tenant_id !== $tenantId) {
            return false;
        }

        if ((int) $invoice->order_id === $orderId) {
            return true;
        }

        $invoiceOrderId = data_get($invoice->order_data, 'id');

        if (is_numeric($invoiceOrderId) && (int) $invoiceOrderId === $orderId) {
            return true;
        }

        $incomingLocalOrderId = data_get($orderData, 'meta.local_order_id');
        $invoiceLocalOrderId = data_get($invoice->order_data, 'meta.local_order_id');

        return $incomingLocalOrderId
            && $invoiceLocalOrderId
            && (string) $incomingLocalOrderId === (string) $invoiceLocalOrderId
            && empty($invoice->order_id);
    }

    private function normalizePreferredInvoiceNumber(?string $invoiceNumber): ?string
    {
        if ($invoiceNumber === null) {
            return null;
        }

        $invoiceNumber = trim($invoiceNumber);

        if ($invoiceNumber === '') {
            return null;
        }

        if (strlen($invoiceNumber) > 50 || ! preg_match('/^[A-Za-z0-9_\/-]+$/', $invoiceNumber)) {
            throw new \InvalidArgumentException('Invalid offline invoice number.');
        }

        return $invoiceNumber;
    }

    private function invoiceQuery()
    {
        return Invoice::on(Invoice::CENTRAL_CONNECTION);
    }

    private function centralTenantQuery()
    {
        return Tenant::on(Invoice::CENTRAL_CONNECTION);
    }

    private function logInvoiceConnectionContext(string $event, Tenant $tenant, int $orderId, array $extra = []): void
    {
        Observability::logInfo($event, array_merge([
            'tenant_id' => $tenant->id,
            'tenant_slug' => $tenant->slug,
            'order_id' => $orderId,
            'invoice_connection' => Invoice::CENTRAL_CONNECTION,
            'invoice_database' => DB::connection(Invoice::CENTRAL_CONNECTION)->getDatabaseName(),
            'tenant_connection' => 'tenant',
            'tenant_database' => DB::connection('tenant')->getDatabaseName(),
            'default_connection' => DB::getDefaultConnection(),
        ], $extra));
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
                url("/billing/invoices/{$invoice->uuid}")
            );
        });
    }

    private function nullableTrim($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }


    public function view($uuid, ?bool $includeCustomerInfo = null)
    {
        $includeCustomerInfo = $this->includeCustomerInfo($includeCustomerInfo);
        $inv = $this->invoiceQuery()->where('uuid',$uuid)->firstOrFail();

        [$config, $template] = $this->resolveInvoiceTemplate($inv->industry, $inv->paper_size);
        
        $tenant = $this->centralTenantQuery()->where('id', $inv->tenant_id)->first();

        $totals = $this->calculateGST($inv->order_data,$tenant->taxConfig);
        $url = request()->url() . ($includeCustomerInfo ? '?custinfo=1' : '');
        $qr = $this->safeQrSvg($url);
        $receipt = $this->buildReceiptData(
            $inv->order_data,
            $tenant,
            $inv->uuid,
            $url,
            $qr,
            $inv,
            false,
            $includeCustomerInfo
        );

        return view(
            $template,
            [
                'order'=>$inv->order_data,
                'branding'=>$tenant->branding,
                'tax'=>$tenant->taxConfig,
                'totals'=>$totals,
                'receipt'=>$receipt,
                'qr'=>$qr,
                'url'=>$url,
                'pdfUrl'=>route('invoice.pdf', array_filter([
                    'uuid' => $uuid,
                    'custinfo' => $includeCustomerInfo ? 1 : null,
                ])),
                'logoSrc'=>$this->invoiceLogoSrc($tenant->branding),
                'isPdf'=>false
            ]
        );
    }

    public function downloadPdf($uuid, ?bool $includeCustomerInfo = null)
    {
        $includeCustomerInfo = $this->includeCustomerInfo($includeCustomerInfo);
        $startedAt = microtime(true);
        $inv = $this->invoiceQuery()->where('uuid',$uuid)->firstOrFail();

        [$config, $template] = $this->resolveInvoiceTemplate($inv->industry, $inv->paper_size);

        $tenant = $this->centralTenantQuery()->where('id', $inv->tenant_id)->first();
        $orderData = $this->normalizeOrder($inv->order_data);
        $totals = $this->calculateGST($orderData,$tenant->taxConfig);
        $url = $this->apiInvoiceUrl($uuid, $includeCustomerInfo);
        $qr = $this->safeQrSvg($url);
        $receipt = $this->buildReceiptData(
            $orderData,
            $tenant,
            $inv->uuid,
            $url,
            $qr,
            $inv,
            true,
            $includeCustomerInfo
        );

        $html = view($template, [
            'order'=>$orderData,
            'branding'=>$tenant->branding,
            'tax'=>$tenant->taxConfig,
            'totals'=>$totals,
            'receipt'=>$receipt,
            'qr'=>$qr,
            'url'=>$url,
            'pdfUrl'=>null,
            'logoSrc'=>$this->invoiceLogoSrc($tenant->branding, true),
            'isPdf'=>true
        ])->render();

        try {
            $receiptHeightPx = (float) $this->configuredBrowsershot($html)
                ->evaluate('document.querySelector(".receipt").getBoundingClientRect().height');
            $receiptHeightMm = max(40, (int) ceil(($receiptHeightPx * 25.4 / 96) + 2));

            $pdf = $this->configuredBrowsershot($html)
                ->paperSize(80, $receiptHeightMm)
                ->margins(0, 0, 0, 0)
                ->showBackground()
                ->pdf();
        } catch (\Throwable $e) {
            Observability::logFailure('invoice.pdf.render_failed', $e, [
                'tenant_id' => $tenant?->id,
                'tenant_slug' => $tenant?->slug,
                'order_id' => $inv->order_id,
                'invoice_id' => $inv->id,
                'invoice_number' => $inv->uuid,
                'paper_size' => $inv->paper_size,
                'template' => $template,
                'duration_ms' => Observability::durationMs($startedAt),
            ]);

            throw $e;
        }

        Observability::logInfo('invoice.pdf.rendered', [
            'tenant_id' => $tenant?->id,
            'tenant_slug' => $tenant?->slug,
            'order_id' => $inv->order_id,
            'invoice_id' => $inv->id,
            'invoice_number' => $inv->uuid,
            'paper_size' => $inv->paper_size,
            'template' => $template,
            'receipt_height_mm' => $receiptHeightMm,
            'duration_ms' => Observability::durationMs($startedAt),
        ]);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="invoice-'.$uuid.'.pdf"',
        ]);
    }

    public function viewToken($uuid, ?bool $includeCustomerInfo = null)
    {
        $includeCustomerInfo = $this->includeCustomerInfo($includeCustomerInfo);
        $inv = $this->invoiceQuery()->where('uuid',$uuid)->firstOrFail();

        [$config, $template] = $this->resolveInvoiceTemplate($inv->industry, $inv->paper_size);
        
        $tenant = $this->centralTenantQuery()->where('id', $inv->tenant_id)->first();

        $totals = $this->calculateGST($inv->order_data,$tenant->taxConfig);

        $qrCode = new Generator();

        $url = $this->publicInvoiceUrl($uuid, $includeCustomerInfo);
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
            Observability::logWarning('invoice.token_qr_generation.failed', $e, [
                'tenant_id' => $tenant?->id,
                'tenant_slug' => $tenant?->slug,
                'invoice_id' => $inv->id,
                'invoice_number' => $inv->uuid,
            ]);
            $qr = null; // fallback (important for production)
            $kitchenQr = null; // fallback (important for production)
        }

        $orderData = $inv->order_data;

        if ($includeCustomerInfo) {
            data_set($orderData, 'meta.invoice.url', $url);
        }

        return [
            'orderData' => $orderData,
            'token' => $inv->order_data['token'] ?? null,
            'qr'=> base64_encode($qr),
            'kitchenQr'=> base64_encode($kitchenQr),
            'invoice_url' => $url,
            'invoiceUrl' => $url,
        ];
    }

    public function generatedView($uuid, ?bool $includeCustomerInfo = null)
    {   
        $includeCustomerInfo = $this->includeCustomerInfo($includeCustomerInfo);
        $inv = $this->invoiceQuery()->where('uuid',$uuid)->firstOrFail();

        [$config, $template] = $this->resolveInvoiceTemplate($inv->industry, $inv->paper_size);

        $tenant = $this->centralTenantQuery()->where('id', $inv->tenant_id)->first();

        $order = $inv->order_data;
        $orderData = $this->normalizeOrder($order);

        
        $url = $this->publicInvoiceUrl($uuid, $includeCustomerInfo);
        
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
                $tokenUrl = $this->publicTokenUrl($uuid, $includeCustomerInfo);

                $kitchenQr = $qrCode->format('svg')->size(120)->generate($kitchenUrl);
                $tokenQr = $qrCode->format('svg')->size(120)->generate($tokenUrl);

            }
        } catch (\Exception $e) {
            Observability::logWarning('invoice.generated_view_qr_generation.failed', $e, [
                'tenant_id' => $tenant?->id,
                'tenant_slug' => $tenant?->slug,
                'invoice_id' => $inv->id,
                'invoice_number' => $inv->uuid,
            ]);
            $qr = null; // fallback (important for production)
            $kitchenQr = null; // fallback (important for production)
            $tokenQr = null;
        }
        
        $totals = $this->calculateGST($orderData,$tenant->taxConfig);
        $receipt = $this->buildReceiptData($orderData, $tenant, $uuid, $url, $qr, $inv, false, $includeCustomerInfo);
        
        return [
            'html'=>view($template,[
                'order'=>$orderData,
                'branding'=>$tenant->branding,
                'tax'=>$tenant->taxConfig,
                'totals'=>$totals,
                'receipt'=>$receipt,
                'qr'=>$qr,
                'url'=>$url,
                'pdfUrl'=>route('invoice.pdf', array_filter([
                    'uuid' => $uuid,
                    'custinfo' => $includeCustomerInfo ? 1 : null,
                ])),
                'logoSrc'=>$this->invoiceLogoSrc($tenant->branding),
                'isPdf'=>false,
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

    private function buildReceiptData(
        array $order,
        Tenant $tenant,
        string $invoiceNo,
        ?string $invoiceUrl,
        ?string $qrSvg = null,
        ?Invoice $invoice = null,
        bool $inlineLogos = false,
        bool $includeCustomerInfo = false
    ): array {
        $branding = $tenant->branding;
        $taxConfig = $tenant->taxConfig;
        $payments = collect($order['payments'] ?? [])
            ->map(fn ($payment) => [
                'method' => strtoupper((string) ($payment['payment_method'] ?? '')),
                'amount' => (float) ($payment['amount'] ?? 0),
                'status' => $payment['status'] ?? null,
                'paid_at' => $payment['paid_at'] ?? null,
            ])
            ->values();

        $successfulPaymentAmount = $payments
            ->filter(fn ($payment) => ($payment['status'] ?? null) === 'success')
            ->sum('amount');

        $paidAmount = (float) ($order['paid_amount'] ?? 0);

        if ($paidAmount <= 0 && $successfulPaymentAmount > 0) {
            $paidAmount = $successfulPaymentAmount;
        }

        $kotCodes = collect($order['kitchen_batches'] ?? [])
            ->pluck('batch_code')
            ->filter()
            ->unique()
            ->values()
            ->all();

        return [
            'merchant' => [
                'name' => $branding?->company_name ?? $tenant->name ?? 'Cafe',
                'phone' => $branding?->phone ?? $tenant->phone ?? null,
                'address' => $branding?->address ?? $tenant->address ?? null,
                'gstin' => $taxConfig?->gst_number ?? $tenant->gst_number ?? null,
                'logo_url' => $this->invoiceLogoSrc($branding, $inlineLogos),
            ],
            'platform' => [
                'paychat_logo_url' => $this->paychatLogoSrc($inlineLogos),
            ],
            'invoice' => [
                'invoice_no' => $invoiceNo,
                'order_no' => $order['order_no'] ?? null,
                'date_time' => $invoice?->created_at?->format('d M Y h:i A')
                    ?? data_get($order, 'completed_at')
                    ?? data_get($order, 'updated_at')
                    ?? now()->format('d M Y h:i A'),
            ],
            'dining' => [
                'order_type' => $order['order_type'] ?? null,
                'dining_flow' => $order['dining_flow'] ?? null,
                'table_name' => data_get($order, 'table_display') ?? data_get($order, 'table.name') ?? data_get($order, 'table.code'),
                'guest_count' => $order['guest_count'] ?? null,
                'token_code' => data_get($order, 'token.token_code'),
                'kot_codes' => $kotCodes,
            ],
            'customer' => $includeCustomerInfo ? [
                'name' => $this->nullableTrim(data_get($order, 'customer.name'))
                    ?? $this->nullableTrim(data_get($order, 'walk_in_customer.name'))
                    ?? $this->nullableTrim(data_get($order, 'customer_name')),
                'phone' => $this->nullableTrim(data_get($order, 'customer.phone'))
                    ?? $this->nullableTrim(data_get($order, 'walk_in_customer.phone'))
                    ?? $this->nullableTrim(data_get($order, 'customer_phone')),
            ] : null,
            'items' => collect($order['items'] ?? [])
                ->map(fn ($item) => [
                    'name' => $item['product_name'] ?? $item['name'] ?? 'Item',
                    'qty' => (float) ($item['quantity'] ?? $item['qty'] ?? 0),
                    'rate' => (float) ($item['price'] ?? $item['rate'] ?? 0),
                    'total' => (float) ($item['total'] ?? $item['subtotal'] ?? (($item['quantity'] ?? 0) * ($item['price'] ?? 0))),
                ])
                ->values()
                ->all(),
            'totals' => [
                'subtotal' => (float) ($order['subtotal'] ?? 0),
                'discount' => (float) ($order['discount'] ?? 0),
                'tax' => (float) ($order['tax'] ?? 0),
                'service_charge' => (float) ($order['service_charge'] ?? 0),
                'rounding' => (float) ($order['rounding'] ?? 0),
                'grand_total' => (float) ($order['total'] ?? 0),
            ],
            'payments' => $payments->all(),
            'paid_amount' => $paidAmount,
            'qr' => [
                'invoice_url' => $invoiceUrl,
                'review_url' => $invoiceUrl,
                'qr_svg_or_url' => $qrSvg,
            ],
        ];
    }

    private function invoiceLogoSrc($branding, bool $inline = false): ?string
    {
        $logo = $branding->logo ?? null;

        if (!$logo) {
            return null;
        }

        if (str_starts_with($logo, 'data:') || preg_match('/^https?:\/\//', $logo)) {
            return $logo;
        }

        $relativePath = ltrim($logo, '/');
        $publicPath = public_path($relativePath);

        if ($inline && is_file($publicPath)) {
            $mime = mime_content_type($publicPath) ?: 'image/png';

            return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($publicPath));
        }

        if ($inline && ! is_file($publicPath)) {
            Observability::logWarningMessage('invoice.logo_resolution.failed', [
                'logo' => $logo,
                'public_path' => $publicPath,
                'inline' => $inline,
            ]);
        }

        return asset($relativePath);
    }

    private function paychatLogoSrc(bool $inline = false): ?string
    {
        $relativePath = 'color-paychat-logo-main.svg';
        $publicPath = public_path($relativePath);

        if (! is_file($publicPath)) {
            return null;
        }

        if ($inline) {
            return 'data:image/svg+xml;base64,'.base64_encode(file_get_contents($publicPath));
        }

        return asset($relativePath);
    }

    private function safeQrSvg(string $url): ?string
    {
        try {
            return (new Generator())->format('svg')->size(120)->generate($url);
        } catch (\Throwable $e) {
            Observability::logWarning('invoice.safe_qr_generation.failed', $e, [
                'url_host' => parse_url($url, PHP_URL_HOST),
            ]);
            return null;
        }
    }

    private function configuredBrowsershot(string $html): Browsershot
    {
        $browser = Browsershot::html($html);

        if ($nodeBinary = config('services.browsershot.node_binary')) {
            $browser->setNodeBinary($nodeBinary);
        }

        if ($npmBinary = config('services.browsershot.npm_binary')) {
            $browser->setNpmBinary($npmBinary);
        }

        if ($chromePath = config('services.browsershot.chrome_path')) {
            $browser->setChromePath($chromePath);
        }

        return $browser->noSandbox();
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
            DB::connection(Invoice::CENTRAL_CONNECTION)
                ->table('invoices')
                ->where('uuid', $invoice)
                ->exists()
        );

        return $invoice;
    }
}
