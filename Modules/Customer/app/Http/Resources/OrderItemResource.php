<?php

namespace Modules\Customer\Http\Resources;

use App\Models\orderItems;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin orderItems
 */
class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $product = $this->product_data ?? [];

        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'name' => $product['name'] ?? $this->product?->name,
            'image' => $product['image'] ?? null,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'options_data' => $this->options_data,
            'options_amount' => $this->options_amount,
            'additions_data' => $this->additions_data,
            'additions_amount' => $this->additions_amount,
            'total_price' => $this->total_price,
        ];
    }
}
