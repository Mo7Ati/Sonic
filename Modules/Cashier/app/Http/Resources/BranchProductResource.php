<?php

namespace Modules\Cashier\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $pivot = $this->branches->first()?->pivot;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ] : null,
            'base_price' => $this->price,
            'base_compare_price' => $this->compare_price,
            'image' => $this->getFirstMediaUrl('product_images') ?: null,
            'is_active' => $this->is_active,
            'branch_price' => $pivot?->price,
            'branch_compare_price' => $pivot?->compare_price,
            'quantity' => $pivot?->quantity,
            'is_available' => (bool) $pivot?->is_available,
            'effective_price' => $pivot?->price ?? $this->price,
        ];
    }
}
