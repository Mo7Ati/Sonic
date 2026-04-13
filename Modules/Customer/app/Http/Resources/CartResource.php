<?php

namespace Modules\Customer\Http\Resources;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Cart
 */
class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'branch' => BranchResource::make($this->whenLoaded('branch')),
            'items' => CartItemResource::collection($this->whenLoaded('items')),
            'items_count' => $this->items_count,
            'subtotal' => $this->subtotal,
        ];
    }
}
