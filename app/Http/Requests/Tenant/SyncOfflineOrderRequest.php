<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyncOfflineOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'local_order_id' => ['required', 'string', 'max:100'],
            'offline_created_at' => ['nullable', 'date'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'order_type' => ['nullable', 'string', 'max:50'],
            'table_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string'],

            'customer' => ['nullable', 'array'],
            'customer.id' => ['nullable', 'integer', 'exists:pos_customers,id'],
            'customer.name' => ['nullable', 'string', 'max:150'],
            'customer.phone' => ['nullable', 'string', 'max:50'],
            'customer.email' => ['nullable', 'email', 'max:150'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.name' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.price' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax' => ['nullable', 'numeric', 'min:0'],
            'items.*.total' => ['nullable', 'numeric', 'min:0'],

            'discount' => ['nullable', 'array'],
            'discount.type' => ['nullable', 'string', 'max:50'],
            'discount.value' => ['nullable', 'numeric', 'min:0'],
            'discount.amount' => ['nullable', 'numeric', 'min:0'],

            'tax_summary' => ['nullable', 'array'],
            'tax_summary.is_gst_enabled' => ['nullable', 'boolean'],
            'tax_summary.cgst' => ['nullable', 'numeric', 'min:0'],
            'tax_summary.sgst' => ['nullable', 'numeric', 'min:0'],
            'tax_summary.igst' => ['nullable', 'numeric', 'min:0'],
            'tax_summary.total_tax' => ['nullable', 'numeric', 'min:0'],

            'payment' => ['required', 'array'],
            'payment.method' => ['required', Rule::in(['cash', 'upi'])],
            'payment.amount' => ['required', 'numeric', 'gt:0'],
            'payment.status' => ['required', Rule::in(['success'])],
            'payment.reference' => ['nullable', 'string', 'max:150'],
            'payment.upi_transaction_id' => ['nullable', 'string', 'max:150'],
            'payment.proof' => ['nullable'],

            'totals' => ['required', 'array'],
            'totals.subtotal' => ['required', 'numeric', 'min:0'],
            'totals.discount_total' => ['nullable', 'numeric', 'min:0'],
            'totals.tax_total' => ['nullable', 'numeric', 'min:0'],
            'totals.grand_total' => ['required', 'numeric', 'min:0'],
            'totals.paid_amount' => ['required', 'numeric', 'min:0'],
            'totals.balance_amount' => ['nullable', 'numeric'],

            'invoice' => ['nullable', 'array'],
            'invoice.offline_invoice_number' => ['nullable', 'string', 'max:100'],
            'invoice.should_generate' => ['nullable', 'boolean'],

            'token' => ['nullable', 'array'],
            'token.offline_token_number' => ['nullable', 'string', 'max:100'],
            'token.should_generate' => ['nullable', 'boolean'],
        ];
    }
}
