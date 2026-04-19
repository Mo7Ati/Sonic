<?php

namespace App\Filament\Store\Widgets;

use App\Filament\Widgets\BaseStatWidget;
use App\Services\WidgetDataService;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StoreCustomersWidget extends BaseStatWidget
{
    protected static ?int $sort = 3;

    public function getHeading(): string
    {
        return __('widgets.store.stats.customers');
    }

    protected function getStats(): array
    {
        $storeId = auth('store')->id();
        $service = app(WidgetDataService::class);
        $stats = $service->getStoreCustomerStats($storeId, $this->period);

        $description = match ($this->period) {
            'today' => __('widgets.store.customers_vs.today', [
                'count' => $stats['current'],
                'change' => $stats['percentage_change'],
            ]),
            'week' => __('widgets.store.customers_vs.week', [
                'count' => $stats['current'],
                'change' => $stats['percentage_change'],
            ]),
            default => __('widgets.store.customers_vs.default', [
                'count' => $stats['current'],
                'change' => $stats['percentage_change'],
            ]),
        };

        return [
            Stat::make(__('widgets.store.stats.new_customers'), $stats['current'])
                ->description($description)
                ->icon(Heroicon::Users)
                ->color('warning'),
        ];
    }
}
