<?php

namespace App\Filament\Store\Resources\Orders;

use App\Filament\Store\Resources\Orders\Pages\ListOrders;
use App\Filament\Store\Resources\Orders\Pages\ViewOrder;
use App\Filament\Store\Resources\Orders\Tables\OrdersTable;
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
        return parent::getEloquentQuery()
            ->whereHas('branch', function ($query) {
                $query->where('store_id', auth()->guard('store')->id());
            })
            ->with(['branch', 'customer'])
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

    public static function getNavigationGroup(): string
    {
        return __('general.navigation_groups.store_management');
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
