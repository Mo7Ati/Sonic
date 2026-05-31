<?php

namespace Modules\Cashier\Http\Requests\PaymentMethods;

use App\Enums\PaymentMethodTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StorePaymentMethodRequest extends FormRequest
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
        $branchId = Auth::guard('cashier')->user()->branch_id;

        return [
            'type' => [
                'required',
                'string',
                Rule::in(PaymentMethodTypeEnum::values()),
                Rule::unique('branch_payment_methods', 'type')->where('branch_id', $branchId),
            ],
            'beneficiary_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required_if:type,bop', 'nullable', 'string', 'max:255'],
            'phone_number' => ['required_if:type,palpay,jawwal_pay', 'nullable', 'string', 'max:255'],
            'instructions' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
