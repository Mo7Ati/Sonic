<?php

namespace App\Filament\Store\Widgets;

use App\Filament\Widgets\BaseStatWidget;
use App\Services\WidgetDataService;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StoreProductsWidget extends BaseStatWidget
{
    protected static ?int $sort = 4;

    public function getHeading(): string
    {
        return __('widgets.store.stats.products');
    }

    protected function getStats(): array
    {
        $storeId = auth('store')->id();
        $service = app(WidgetDataService::class);
        $stats = $service->getStoreProductStats($storeId);

        return [
            Stat::make(__('widgets.store.stats.total_products'), $stats['total'])
                ->description(__('widgets.store.products_active', ['count' => $stats['active']]))
                ->icon(Heroicon::Square3Stack3d)
                ->color('primary'),
        ];
    }
}
