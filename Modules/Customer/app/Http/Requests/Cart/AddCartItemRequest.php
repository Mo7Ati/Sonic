<?php

namespace Modules\Customer\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AddCartItemRequest extends FormRequest
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
        // Prices (unit_price, options.*.price, additions.*.price) are intentionally
        // NOT accepted from the client. They are resolved server-side from the
        // product / pivot tables in CartController to prevent price tampering.
        return [
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],

            'options' => ['nullable', 'array'],
            'options.*' => ['required_with:options', 'integer', 'exists:options,id'],

            'additions' => ['nullable', 'array'],
            'additions.*' => ['required_with:additions', 'integer'],

            'force_replace' => ['nullable', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! $this->user('sanctum') && ! $this->header('X-Session-Id')) {
                    $validator->errors()->add('session', 'Authentication or X-Session-Id header is required.');
                }
            },
        ];
    }
}
