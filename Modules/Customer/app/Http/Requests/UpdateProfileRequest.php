<?php

namespace Modules\Customer\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => [
                'required',
                'string',
                'regex:/^05[69]\d{7}$/',
                Rule::unique('customers', 'phone_number')->ignore(auth()->user()->id)
            ],
        ];
    }

    public function verifyNewPhoneRules(): array
    {
        return [
            'new_phone_number' => ['required', 'string', 'regex:/^05[69]\d{7}$/'],
            'otp' => ['required', 'string', 'max:6'],
        ];
    }
}
