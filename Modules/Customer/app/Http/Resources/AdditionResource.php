<?php

namespace Modules\Customer\Http\Resources;

use App\Models\Addition;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Addition
 */
class AdditionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->pivot->price,
        ];
    }
}
