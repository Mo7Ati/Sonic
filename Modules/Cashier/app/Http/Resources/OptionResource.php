<?php


namespace Modules\Cashier\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class OptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'],
            'name' => Arr::get($this['name'],locale()),
            'price' => $this['price'],
            'group_id' => $this['group_id'],
        ];
    }
}
