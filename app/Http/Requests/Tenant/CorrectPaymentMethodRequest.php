<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class CorrectPaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_id' => ['nullable', 'integer'],
            'new_method' => ['required', 'string', 'in:cash,upi,phonepe'],
            'upi_profile_id' => ['nullable', 'integer'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }
}
