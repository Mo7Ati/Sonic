<?php

namespace App\Filament\Store\Resources\Categories\Pages;

use App\Filament\Store\Resources\Categories\CategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;
}
