<?php

namespace Modules\Customer\Http\Requests\DeviceTokens;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use NotificationChannels\Expo\ExpoPushToken;

class StoreDeviceTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'expo_token' => ['required', 'string', ExpoPushToken::rule()],
            'platform' => ['nullable', 'string', Rule::in(['ios', 'android', 'web'])],
        ];
    }
}
