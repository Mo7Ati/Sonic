<?php

namespace App\Filament\Store\Widgets;

use App\Filament\Widgets\BaseStatWidget;
use App\Services\WidgetDataService;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StoreOrdersWidget extends BaseStatWidget
{
    protected static ?int $sort = 2;

    public function getHeading(): string
    {
        return __('widgets.store.stats.orders');
    }

    protected function getStats(): array
    {
        $storeId = auth('store')->id();
        $service = app(WidgetDataService::class);
        $stats = $service->getStoreOrderStats($storeId, $this->period);

        $description = match ($this->period) {
            'today' => __('widgets.store.orders_vs.today', [
                'pending' => $stats['pending'],
                'change' => $stats['percentage_change'],
            ]),
            'week' => __('widgets.store.orders_vs.week', [
                'pending' => $stats['pending'],
                'change' => $stats['percentage_change'],
            ]),
            default => __('widgets.store.orders_vs.default', [
                'pending' => $stats['pending'],
                'change' => $stats['percentage_change'],
            ]),
        };

        return [
            Stat::make(__('widgets.store.stats.orders'), $stats['current'])
                ->description($description)
                ->icon(Heroicon::ShoppingCart)
                ->color('info'),
        ];
    }
}
