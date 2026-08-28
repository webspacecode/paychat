<?php

namespace App\Http\Resources\Tenant;

use App\Services\KitchenBatchService;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Schema;

class OrderResource extends JsonResource
{
    public function toArray($request)
    {
        $kitchenOperationMode = app(KitchenBatchService::class)->operationMode();

        return [

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */
            'id' => $this->id,
            'order_no' => $this->order_no,
            'invoice_id' => $this->invoice_id,
            'invoice_no' => $this->invoice_no,
            'reference_no' => $this->reference_no,

            /*
            |--------------------------------------------------------------------------
            | Context
            |--------------------------------------------------------------------------
            */
            'order_type' => $this->order_type,
            'source' => $this->source,
            'delivery_channel' => $this->delivery_channel,
            'delivery_channel_label' => $this->delivery_channel_label,
            'external_order_reference' => $this->external_order_reference,
            'delivery' => [
                'channel' => $this->delivery_channel,
                'label' => $this->delivery_channel_label,
                'external_reference' => $this->external_order_reference,
            ],

            'location' => [
                'id' => $this->location_id,
                'name' => optional($this->location)->name,
                'address' => optional($this->location)->address,
            ],

            'table' => $this->table ? [
                'id' => $this->table->id,
                'name' => $this->table->name,
                'code' => $this->table->code,
            ] : null,
            'tables' => $this->tablePayloads(),
            'primary_table' => $this->primaryTablePayload(),
            'linked_tables' => $this->linkedTablePayloads(),
            'table_display' => $this->tableDisplay(),
            'table_session_id' => $this->table_session_id,
            'guest_count' => $this->guest_count,
            'dining_flow' => $this->dining_flow,

            'warehouse_id' => $this->warehouse_id,

            /*
            |--------------------------------------------------------------------------
            | Customer
            |--------------------------------------------------------------------------
            */
            'customer' => $this->customer ? [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'email' => $this->customer->email,
                'phone' => $this->customer->phone,
                'address' => $this->customer->address,
                'loyalty_points' => (int) $this->customer->loyalty_points,
                'total_visits' => (int) $this->customer->total_visits,
                'total_spend' => (float) $this->customer->total_spend,
            ] : null,

            'walk_in_customer' => [
                'name' => $this->customer_name,
                'phone' => $this->customer_phone,
            ],

            'loyalty_award' => $this->when($this->loyalty_award !== null, $this->loyalty_award),
            'loyalty_context' => $this->when($this->loyalty_context !== null, $this->loyalty_context),

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            
            'token' => $this->token ? [
                'id' => $this->token->id,
                'token_code' => $this->token->token_code,
                'status' => $this->token->status,
            ] : null,
            /*  
            |--------------------------------------------------------------------------
            | Financials
            |--------------------------------------------------------------------------
            */
            'currency' => $this->currency,

            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'tax' => $this->tax,
            'shipping' => $this->shipping,
            'service_charge' => $this->service_charge,
            'rounding' => $this->rounding,
            'total' => $this->total,

            'paid_amount' => $this->paid_amount,
            'balance_due' => $this->balance_due,
            'change_returned' => $this->change_returned,

            /*
            |--------------------------------------------------------------------------
            | Items
            |--------------------------------------------------------------------------
            */
            'items' => $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => optional($item->product)->name,
                    'sku' => optional($item->product)->sku,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'discount' => $item->discount ?? 0,
                    'tax' => $item->tax ?? 0,
                    'subtotal' => $item->subtotal,
                    'total' => $item->total ?? $item->subtotal,
                    'kitchen_status' => $item->kitchen_status,
                    'kitchen_batch_id' => $item->kitchen_batch_id,
                    'sent_to_kitchen_at' => $item->sent_to_kitchen_at,
                    'item_status' => $item->item_status,
                ];
            }),

            'kitchen_batches' => $this->kitchenBatches->map(function ($batch) use ($kitchenOperationMode) {
                return [
                    'id' => $batch->id,
                    'location_id' => $batch->location_id,
                    'order_id' => $batch->order_id,
                    'table_session_id' => $batch->table_session_id,
                    'table_id' => $batch->table_id,
                    'table_display' => $this->batchTableDisplay($batch),
                    'batch_number' => $batch->batch_number,
                    'batch_code' => $batch->batch_code,
	                    'business_date' => $batch->business_date,
	                    'kitchen_operation_mode' => $kitchenOperationMode,
	                    'status' => $batch->status,
	                    'dispatch_channel' => $batch->dispatch_channel ?? 'board',
	                    'sent_at' => $batch->sent_at,
                    'created_at' => $batch->created_at,
                    'updated_at' => $batch->updated_at,
                    'items' => $batch->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'product_name' => optional($item->product)->name,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'total' => $item->total,
                            'kitchen_status' => $item->kitchen_status,
                            'kitchen_batch_id' => $item->kitchen_batch_id,
                            'sent_to_kitchen_at' => $item->sent_to_kitchen_at,
                            'item_status' => $item->item_status,
                        ];
                    }),
                ];
            }),

            /*
            |--------------------------------------------------------------------------
            | Payments
            |--------------------------------------------------------------------------
            */
            'payments' => $this->payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'payment_method' => $payment->payment_method,
                    'upi_profile_id' => $payment->upi_profile_id,
                    'upi_profile' => $this->paymentUpiProfile($payment),
                    'upi_qr_url' => $payment->upi_qr_url ?? data_get($payment->meta, 'upi_qr_url'),
                    'amount' => $payment->amount,
                    'transaction_id' => $payment->transaction_id ?? null,
                    'status' => $payment->status,
                    'paid_at' => $payment->updated_at,
                    'meta' => [
                        'upi_qr_url' => data_get($payment->meta, 'upi_qr_url'),
                    ],
                ];
            }),

            /*
            |--------------------------------------------------------------------------
            | Staff Tracking
            |--------------------------------------------------------------------------
            */
            'created_by' => $this->created_by,
            'completed_by' => $this->completed_by,
            'cancelled_by' => $this->cancelled_by,
            'cancel_reason_type' => $this->cancel_reason_type,
            'cancel_reason' => $this->cancel_reason,

            /*
            |--------------------------------------------------------------------------
            | Notes & Meta
            |--------------------------------------------------------------------------
            */
            'notes' => $this->notes,
            'meta' => $this->meta,

            /*
            |--------------------------------------------------------------------------
            | Time Tracking
            |--------------------------------------------------------------------------
            */
            'ordered_at' => $this->ordered_at,
            'paid_at' => $this->paid_at,
            'completed_at' => $this->completed_at,
            'cancelled_at' => $this->cancelled_at,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function paymentUpiProfile($payment): ?array
    {
        $profile = $payment->meta['upi_profile'] ?? null;

        if (! is_array($profile)) {
            return null;
        }

        return [
            'id' => $profile['id'] ?? null,
            'label' => $profile['label'] ?? null,
            'payee_name' => $profile['payee_name'] ?? null,
        ];
    }

    private function tablePayloads()
    {
        $session = $this->loadedTableSession();

        if ($session && $this->hasTableSessionTables()) {
            return $session->tables
                ->map(fn ($table) => $this->tablePayload($table))
                ->filter()
                ->values();
        }

        return $this->table
            ? collect([$this->tablePayload($this->table)])
            : collect();
    }

    private function primaryTablePayload(): ?array
    {
        $session = $this->loadedTableSession();
        $table = ($session && $this->hasTableSessionTables() ? $session->primaryTable : null) ?: $this->table;

        return $this->tablePayload($table);
    }

    private function linkedTablePayloads()
    {
        $session = $this->loadedTableSession();

        if (! $session || ! $this->hasTableSessionTables()) {
            return collect();
        }

        return $session->linkedTables
            ->map(fn ($table) => $this->tablePayload($table))
            ->filter()
            ->values();
    }

    private function tableDisplay(): ?string
    {
        $session = $this->loadedTableSession();

        if ($session?->table_display) {
            return $session->table_display;
        }

        if (! $this->table) {
            return null;
        }

        return $this->table->name ?: $this->table->code;
    }

    private function loadedTableSession()
    {
        $session = $this->tableSession;

        if (! $session) {
            return null;
        }

        if ($this->hasTableSessionTables()) {
            $session->loadMissing(['tables', 'primaryTable', 'linkedTables']);
        }

        return $session;
    }

    private function hasTableSessionTables(): bool
    {
        return Schema::connection('tenant')->hasTable('table_session_tables');
    }

    private function tablePayload($table): ?array
    {
        if (! $table) {
            return null;
        }

        return [
            'id' => $table->id,
            'name' => $table->name,
            'code' => $table->code,
        ];
    }

    private function batchTableDisplay($batch): ?string
    {
        $session = $batch->tableSession ?: $this->loadedTableSession();

        if ($session && $this->hasTableSessionTables()) {
            $session->loadMissing(['tables']);

            if ($session->table_display) {
                return $session->table_display;
            }
        }

        if ($batch->table) {
            return $batch->table->name ?: $batch->table->code;
        }

        return $this->tableDisplay();
    }
}
