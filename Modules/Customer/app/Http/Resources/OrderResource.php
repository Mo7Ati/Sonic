<?php

namespace Modules\Customer\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */
class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'payment_status' => [
                'value' => $this->payment_status->value,
                'label' => $this->payment_status->label(),
            ],
            'payment_method' => $this->payment_method_data,
            'payment_proof' => $this->payment_proof_url,
            'address_data' => $this->address_data,
            'total_items_amount' => $this->total_items_amount,
            'delivery_amount' => $this->delivery_amount,
            'total' => $this->total,
            'notes' => $this->notes,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
