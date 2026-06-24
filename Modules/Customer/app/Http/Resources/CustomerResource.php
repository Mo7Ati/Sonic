<?php

namespace Modules\Customer\Http\Resources;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Customer
 */
class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'phone_verified_at' => $this->phone_verified_at,
            'last_seen_at' => $this->last_seen_at?->diffForHumans(),
        ];
    }
}
