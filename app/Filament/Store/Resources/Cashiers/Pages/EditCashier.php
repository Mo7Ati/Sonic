<?php

namespace App\Filament\Store\Resources\Cashiers\Pages;

use App\Filament\Store\Resources\Cashiers\CashierResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCashier extends EditRecord
{
    protected static string $resource = CashierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
