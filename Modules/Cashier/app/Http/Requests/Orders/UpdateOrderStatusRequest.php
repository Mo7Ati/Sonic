<?php

namespace Modules\Cashier\Http\Requests\Orders;

use App\Enums\OrderStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(['on_the_way', 'completed'])],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $order = $this->route('order');
                $currentStatus = $order->status->value;
                $newStatus = $this->input('status');

                $allowedTransitions = [
                    'preparing' => ['on_the_way'],
                    'on_the_way' => ['completed'],
                ];

                $allowed = $allowedTransitions[$currentStatus] ?? [];

                if (! in_array($newStatus, $allowed)) {
                    $validator->errors()->add('status', "Cannot transition from {$currentStatus} to {$newStatus}.");
                }
            },
        ];
    }
}
