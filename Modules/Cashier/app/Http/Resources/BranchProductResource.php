<?php

namespace Modules\Cashier\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category_id,
                    'name' => $this->category->name,
                ];
            }),
            'store_price' => $this->price,
            'store_compare_price' => $this->compare_price,
            'store_quantity' => $this->quantity,
            'image' => $this->getFirstMediaUrl('product_images') ?: null,
            'is_active' => $this->is_active,
            'branch_price' => $this->pivot?->price,
            'branch_compare_price' => $this->pivot?->compare_price,
            'quantity' => $this->pivot?->quantity,
            'is_available' => (bool) $this->pivot?->is_available,
            'effective_price' => $this->pivot?->price ?? $this->price,
        ];
    }
}
