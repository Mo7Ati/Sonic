<?php

namespace Modules\Customer\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateCartItemRequest extends FormRequest
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
            'quantity' => ['required', 'integer', 'min:1'],
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
