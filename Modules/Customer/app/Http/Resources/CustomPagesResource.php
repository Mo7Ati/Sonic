<?php

namespace Modules\Customer\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomPagesResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'title' => $this['title'][app()->getLocale()],
            'content' => $this['content'],
        ];
    }
}
