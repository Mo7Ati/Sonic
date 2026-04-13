<?php

namespace App\Filament\Store\Resources\OptionGroups\Pages;

use App\Filament\Store\Resources\OptionGroups\OptionGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOptionGroups extends ListRecords
{
    protected static string $resource = OptionGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
