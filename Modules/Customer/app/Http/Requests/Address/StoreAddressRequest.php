<?php

namespace Modules\Customer\Http\Requests\Address;

use App\Settings\AddressSettings;
use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $settings = app(AddressSettings::class);
        $fieldRules = [];

        foreach ($settings->fields as $index => $field) {
            $rule = ['nullable', 'string', 'max:255'];
            if (! empty($field['is_required'])) {
                $rule[0] = 'required';
            }
            $fieldRules["fields.{$field['key']}"] = $rule;
        }

        return array_merge([
            'name' => ['required', 'string', 'max:100'],
            'fields' => ['required', 'array'],
        ], $fieldRules);
    }

    public function after(): array
    {
        return [
            function ($validator) {
                if (! $this->user() && ! $this->header('X-Session-Id')) {
                    $validator->errors()->add('session', 'Authentication or X-Session-Id header is required.');
                }
            },
        ];
    }

    /**
     * Transform the flat fields input into the stored array format.
     */
    public function addressFields(): array
    {
        $settings = app(AddressSettings::class);
        $input = $this->input('fields', []);
        $result = [];

        foreach ($settings->fields as $field) {
            $key = $field['key'];
            if (isset($input[$key]) && $input[$key] !== '') {
                $result[] = [
                    'key' => $key,
                    'value' => $input[$key],
                ];
            }
        }

        return $result;
    }
}
