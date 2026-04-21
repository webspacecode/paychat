<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */
            'id' => $this->id,
            'order_no' => $this->order_no,
            'invoice_no' => $this->invoice_no,
            'reference_no' => $this->reference_no,

            /*
            |--------------------------------------------------------------------------
            | Context
            |--------------------------------------------------------------------------
            */
            'order_type' => $this->order_type,

            'location' => [
                'id' => $this->location_id,
                'name' => optional($this->location)->name,
            ],

            'table' => $this->resource ? [
                'id' => $this->resource->id,
                'name' => $this->resource->name,
            ] : null,

            'warehouse_id' => $this->warehouse_id,

            /*
            |--------------------------------------------------------------------------
            | Customer
            |--------------------------------------------------------------------------
            */
            'customer' => $this->customer ? [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'phone' => $this->customer->phone,
            ] : null,

            'walk_in_customer' => [
                'name' => $this->customer_name,
                'phone' => $this->customer_phone,
            ],

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
                    'method' => $payment->method,
                    'amount' => $payment->amount,
                    'reference' => $payment->transaction_reference ?? null,
                    'status' => $payment->status,
                    'paid_at' => $payment->paid_at,
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
}