<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRegistrationSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $definitions = config('modules.registration_management.settings', []);
        $rules = [];

        foreach ($definitions as $key => $definition) {
            $rules[$key] = ($definition['type'] ?? null) === 'boolean'
                ? ['sometimes', 'boolean']
                : ['sometimes', 'string', 'max:'.($definition['max'] ?? 50), 'not_regex:/[<>]/', Rule::notIn([''])];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $unknown = array_diff(array_keys($this->all()), array_keys(config('modules.registration_management.settings', [])));
        if ($unknown) {
            $this->merge(['__unknown_settings' => $unknown]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('__unknown_settings')) {
                $validator->errors()->add('settings', 'Unknown registration setting.');
            }
        });
    }

    public function validated($key = null, $default = null)
    {
        $values = parent::validated();
        unset($values['__unknown_settings']);
        return $key === null ? $values : data_get($values, $key, $default);
    }
}
