<?php

namespace App\Filament\Store\Resources\OptionGroups\Pages;

use App\Filament\Store\Resources\OptionGroups\OptionGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditOptionGroup extends EditRecord
{
    protected static string $resource = OptionGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
