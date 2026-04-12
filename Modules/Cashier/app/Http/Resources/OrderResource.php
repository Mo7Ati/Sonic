<?php

namespace Modules\Cashier\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'customer' => $this->customer ? [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'phone_number' => $this->customer->phone_number,
            ] : null,
            'customer_data' => $this->customer_data,
            'address_data' => $this->address_data,
            'total_items_amount' => $this->total_items_amount,
            'delivery_amount' => $this->delivery_amount,
            'total' => $this->total,
            'notes' => $this->notes,
            'cancelled_reason' => $this->cancelled_reason,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
