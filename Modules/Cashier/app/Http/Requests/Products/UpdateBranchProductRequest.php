<?php

namespace Modules\Cashier\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBranchProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'price' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_available' => ['sometimes', 'boolean'],
        ];
    }
}
