<?php

namespace Modules\Customer\Http\Requests\Orders;

use App\Enums\PaymentMethodTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
            'payment_method_type' => ['required', 'string', Rule::in(PaymentMethodTypeEnum::values())],
            'notes' => ['nullable', 'string', 'max:1000'],
            'proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
