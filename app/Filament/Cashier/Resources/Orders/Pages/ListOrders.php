<?php

namespace App\Filament\Cashier\Resources\Orders\Pages;

use App\Filament\Cashier\Resources\Orders\OrderResource;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;
}
