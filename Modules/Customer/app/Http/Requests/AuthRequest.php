<?php

namespace Modules\Customer\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AuthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function sendOtpRules(): array
    {
        return [
            'phone_number' => ['required', 'string', 'regex:/^05[69]\d{7}$/'],
        ];
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function verifyOtpRules(): array
    {
        return [
            'phone_number' => ['required', 'string', 'regex:/^05[69]\d{7}$/'],
            'otp' => ['required', 'string', 'digits:6'],
        ];
    }
}
