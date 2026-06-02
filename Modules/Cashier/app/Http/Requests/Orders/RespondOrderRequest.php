<?php

namespace Modules\Cashier\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RespondOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'string', Rule::in(['accept', 'reject'])],
            'cancelled_reason' => ['required_if:action,reject', 'nullable', 'string', 'max:500'],
        ];
    }
}
