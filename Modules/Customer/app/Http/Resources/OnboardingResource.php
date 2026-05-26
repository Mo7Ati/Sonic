<?php

namespace Modules\Customer\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class OnboardingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'title' => Arr::get($this['title'], app()->getLocale()),
            'description' => Arr::get($this['description'], app()->getLocale()),
            'background_color' => $this['color'],
            'color' => $this['color'],
            'image' => $this['image'] ? asset('storage/' . $this['image']) : null,
        ];
    }
}
