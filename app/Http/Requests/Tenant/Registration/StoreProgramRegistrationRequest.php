<?php

namespace App\Http\Requests\Tenant\Registration;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProgramRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'participant_profile_id' => ['required', 'integer', 'exists:participant_profiles,id'],
            'program_id' => ['required', 'integer', 'exists:programs,id'],
            'program_batch_id' => ['nullable', 'integer', 'exists:program_batches,id'],
            'registered_on' => ['nullable', 'date'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'fee_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'discount_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'status' => ['nullable', Rule::in(['active', 'on_hold'])],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
