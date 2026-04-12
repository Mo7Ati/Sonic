<?php

namespace Modules\Cashier\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'delivery_time_from' => $this->delivery_time_from,
            'delivery_time_to' => $this->delivery_time_to,
            'delivery_fee' => $this->delivery_fee,
            'is_active' => $this->is_active,
            'location' => $this->location,
        ];
    }
}
