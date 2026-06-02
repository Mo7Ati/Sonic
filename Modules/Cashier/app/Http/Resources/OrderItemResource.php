<?php

namespace Modules\Cashier\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_data' => [
                'id' => $this->product_id,
                'name' => Arr::get($this->product_data['name'], app()->getLocale()),
            ],
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'options_amount' => $this->options_amount,
            'options_data' => OptionResource::collection($this->options_data ?? []),
            'additions_amount' => $this->additions_amount,
            'additions_data' => AdditionResource::collection($this->additions_data ?? []),
            'total_price' => $this->total_price,
        ];
    }
}
