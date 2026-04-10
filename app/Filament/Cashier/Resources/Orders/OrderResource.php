<?php

namespace App\Filament\Cashier\Resources\Orders;

use App\Filament\Cashier\Resources\Orders\Pages\ListOrders;
use App\Filament\Cashier\Resources\Orders\Pages\ViewOrder;
use App\Filament\Cashier\Resources\Orders\Tables\OrdersTable;
use App\Models\Order;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static ?string $recordTitleAttribute = 'id';

    public static function table(Table $table): Table
    {
        return OrdersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'view' => ViewOrder::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $branchId = auth()->guard('cashier')->user()?->branch_id;

        return parent::getEloquentQuery()
            ->where('branch_id', $branchId)
            ->with(['customer', 'items.product'])
            ->latest();
    }

    public static function getModelLabel(): string
    {
        return __('general.model_labels.order');
    }

    public static function getPluralModelLabel(): string
    {
        return __('general.plural_model_labels.orders');
    }

    public static function getNavigationLabel(): string
    {
        return __('general.nav_labels.orders');
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
