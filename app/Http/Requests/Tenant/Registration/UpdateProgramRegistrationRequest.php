<?php

namespace App\Http\Requests\Tenant\Registration;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProgramRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'program_batch_id' => ['sometimes', 'nullable', 'integer', 'exists:program_batches,id'],
            'registered_on' => ['sometimes', 'date'],
            'starts_on' => ['sometimes', 'nullable', 'date'],
            'ends_on' => ['sometimes', 'nullable', 'date'],
            'fee_amount' => ['sometimes', 'numeric', 'min:0', 'max:9999999999.99'],
            'discount_amount' => ['sometimes', 'numeric', 'min:0', 'max:9999999999.99'],
            'status' => ['sometimes', Rule::in(['active', 'on_hold', 'completed'])],
            'notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }
}
