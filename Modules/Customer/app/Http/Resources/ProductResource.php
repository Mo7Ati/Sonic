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
            'image' =>  $this->getFirstMediaUrl('product_images') ?: "https://img.freepik.com/free-photo/top-view-table-full-food_23-2149209253.jpg?semt=ais_incoming&w=740&q=80",
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'options' => $this->when(
                $this->relationLoaded('options'),
                fn() => $this->options
                    ->filter(fn($option) => $option->pivot->is_available)
                    ->groupBy(fn($option) => $option->optionGroup?->id)
                    ->map(fn($options, $groupId) => [
                        'group_id' => $groupId,
                        'group' => $options->first()->optionGroup?->name,
                        'items' => OptionResource::collection($options->values()),
                    ])
                    ->values(),
            ),
            'additions' => AdditionResource::collection($this->whenLoaded('additions')),
        ];
    }
}
