<?php

namespace Modules\Customer\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 */
class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $pivot = $this->pivot;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'keywords' => $this->keywords,
            'price' => $pivot?->price ?? $this->price,
            'compare_price' => $pivot?->compare_price ?? $this->compare_price,
            'quantity' => $pivot?->quantity ?? null,
            'image' => $this->getFirstMediaUrl('product_images') ?: null,
        ];
    }
}
