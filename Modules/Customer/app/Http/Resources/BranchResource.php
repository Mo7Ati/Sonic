<?php

namespace Modules\Customer\Http\Resources;

use App\Models\Branch;
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
            'name' => $this->name,
            'address' => $this->address,
            'location' => $this->location,
            'delivery_time' => $this->delivery_time,
            'delivery_fee' => $this->delivery_fee,
            'status' => $this->status?->value,
            'store' => StoreResource::make($this->store),
        ];
    }

    public function serializeForShow(): array
    {
        $store = $this->relationLoaded('store') ? $this->store : null;

        return [
            'id' => $this->id,
            'store_name' => $store?->name,
            'branch_name' => $this->name,
            'address' => $this->address,
            'location' => $this->location,
            'delivery_time' => $this->delivery_time,
            'status' => [
                'label' => $this->status->label(),
                'value' => $this->status->value,
            ],
            'delivery_fee' => $this->delivery_fee,
            'categories' => $store?->relationLoaded('categories')
                ? CategoryResource::collection($store->categories)
                : [],
            'products' => $store?->relationLoaded('products')
                ? $store->products
                    ->groupBy(fn ($product) => $product->category?->name ?? __('No Category'))
                    ->map(fn ($items, $categoryName) => [
                        'category' => $categoryName,
                        'products' => ProductResource::collection($items),
                    ])
                    ->values()
                : [],
        ];
    }
}
