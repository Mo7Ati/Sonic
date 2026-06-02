<?php

namespace Modules\Cashier\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Customer\Http\Resources\AddressResource;
use Modules\Customer\Http\Resources\CustomerResource;

class OrderResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'isNew' => $this->is_new,
            'status' => $this->status->model(),
            'payment_method_type' => $this->payment_method_type->model(),
            'customer_name' => $this->customer_data['name'],
            'address' => collect($this->address_data['fields'])
                ->map(fn($field) => $field['value'])->join(', '),
            'total' => $this->total,
            'created_at' => $this->created_at->format('H:i:s A'),
        ];
    }
    public function serializeForShow(): array
    {
        return [
            'id' => $this->id,
            'isNew' => $this->is_new,
            'status' => $this->status->model(),
            'payment_method_type' => $this->payment_method_type->model(),
            'payment_status' => $this->payment_status->model(),
            'payment_proof' => $this->payment_proof_url,

            'customer' => [
                'name' => $this->customer_data['name'],
                'email' => $this->customer_data['email'],
                'phone_number' => $this->customer_data['phone_number'],
            ],

            'address' => collect($this->address_data['fields'])->map(fn($field) => $field['value'])->join(', '),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),

            'total_items_amount' => $this->total_items_amount,
            'total' => $this->total,

            'notes' => $this->notes,
            'cancelled_reason' => $this->cancelled_reason,
            'created_at' => $this->created_at->format('H:i:s A'),
            'updated_at' => $this->updated_at->format('H:i:s A'),
        ];
    }
}
