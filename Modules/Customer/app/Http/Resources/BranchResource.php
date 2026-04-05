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
            'delivery_time_from' => $this->delivery_time_from,
            'delivery_time_to' => $this->delivery_time_to,
            'delivery_time' => $this->delivery_time,
            'delivery_fee' => $this->delivery_fee,
            'is_active' => $this->is_active,
            'status' => $this->status?->value,
            'store' => $this->when(
                $this->relationLoaded('store'),
                fn(): array => [
                    'id' => $this->store->id,
                    'name' => $this->store->name,
                    'slug' => $this->store->slug,
                    'logo' => $this->store->getMedia('store_images')?->first()?->getUrl(),
                ],
            ),
        ];
    }
}
