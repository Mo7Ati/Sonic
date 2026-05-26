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
        return [
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'options' => ['nullable', 'array'],
            'options.*.group_id' => ['required_with:options', 'integer'],
            'options.*.group_name' => ['required_with:options', 'string'],
            'options.*.item_id' => ['required_with:options', 'integer'],
            'options.*.item_name' => ['required_with:options', 'string'],
            'options.*.price' => ['required_with:options', 'numeric', 'min:0'],
            'additions' => ['nullable', 'array'],
            'additions.*.id' => ['required_with:additions', 'integer'],
            'additions.*.name' => ['required_with:additions', 'string'],
            'additions.*.price' => ['required_with:additions', 'numeric', 'min:0'],
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
