<?php

namespace App\Filament\Store\Widgets;

use App\Filament\Widgets\BaseStatWidget;
use App\Services\WidgetDataService;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StoreRevenueWidget extends BaseStatWidget
{
    protected static ?int $sort = 1;

    public function getHeading(): string
    {
        return __('widgets.store.stats.revenue');
    }

    protected function getStats(): array
    {
        $storeId = auth('store')->id();
        $service = app(WidgetDataService::class);
        $stats = $service->getStoreRevenueStats($storeId, $this->period);

        $description = match ($this->period) {
            'today' => __('widgets.store.revenue_vs.today', ['change' => $stats['percentage_change']]),
            'week' => __('widgets.store.revenue_vs.week', ['change' => $stats['percentage_change']]),
            default => __('widgets.store.revenue_vs.default', ['change' => $stats['percentage_change']]),
        };

        $icon = $stats['percentage_change'] >= 0
            ? Heroicon::ArrowTrendingUp
            : Heroicon::ArrowTrendingDown;

        return [
            Stat::make(__('widgets.store.stats.revenue'), '$'.number_format($stats['current'], 2))
                ->description($description)
                ->icon($icon)
                ->color($stats['percentage_change'] >= 0 ? 'success' : 'danger'),
        ];
    }
}
