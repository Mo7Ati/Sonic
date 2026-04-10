<?php

namespace App\Filament\Store\Resources\Orders\Pages;

use App\Filament\Store\Resources\Orders\OrderResource;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;
}
