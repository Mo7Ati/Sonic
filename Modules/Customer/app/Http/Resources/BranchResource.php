<?php

namespace Modules\Customer\Http\Resources;

use App\Models\Branch;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Branch
 */
class BranchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->full_name,
            'description' => $this->whenLoaded('store', fn ($store) => $store->description),
            'logo' => $this->whenLoaded('store', fn ($store) => $store->getFirstMediaUrl('store_images')),
            'cover_image' => $this->whenLoaded('store', fn ($store) => $store->getFirstMediaUrl('store_cover_images')),
            'address' => $this->address,
            'location' => $this->location,
            'delivery_time' => $this->delivery_time,
            'delivery_fee' => $this->delivery_fee,
            'status' => [
                'label' => $this->status->label(),
                'value' => $this->status->value,
            ],
            'categories' => $this->when(
                $this->relationLoaded('store') && $this->store->relationLoaded('categories'),
                fn () => CategoryResource::collection($this->store->categories),
            ),
            'products' => $this->when(
                $this->relationLoaded('availableProducts'),
                fn () => $this->availableProducts
                    ->groupBy(fn (Product $product) => $product->category?->name ?? __('Uncategorized'))
                    ->map(fn ($products) => ProductResource::collection($products)),
            ),
        ];
    }
}
