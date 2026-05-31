<?php

namespace Modules\Customer\Http\Resources;

use App\Models\BranchPaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BranchPaymentMethod
 */
class PaymentMethodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->model(),
            'beneficiary_name' => $this->beneficiary_name,
            'account_number' => $this->account_number,
            'phone_number' => $this->phone_number,
            'instructions' => $this->instructions,
        ];
    }
}
