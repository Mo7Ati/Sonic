<?php

namespace Modules\Customer\Http\Resources;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CartItem
 */
class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'name' => $this->product?->name,
            'image' => $this->product?->getFirstMediaUrl('product_images') ?: 'https://img.freepik.com/free-photo/top-view-table-full-food_23-2149209253.jpg?semt=ais_incoming&w=740&q=80',
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'compare_price' => $this->product?->compare_price,
            'options_data' => $this->options_data,
            'options_amount' => $this->options_amount,
            'additions_data' => $this->additions_data,
            'additions_amount' => $this->additions_amount,
            'total_price' => $this->total_price,
        ];
    }
}
