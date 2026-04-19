<?php

namespace App\Filament\Store\Widgets;

use App\Services\WidgetDataService;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StoreOverviewStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getDescription(): ?string
    {
        return __('widgets.store.overview_description');
    }

    protected function getStats(): array
    {
        $storeId = auth('store')->id();
        $svc = app(WidgetDataService::class);

        $revenue = $svc->getStoreRevenue30dStats($storeId);
        $orders = $svc->getStoreOrders30dStats($storeId);
        $customers = $svc->getStoreCustomers30dStats($storeId);
        $branches = $svc->getStoreBranchSnapshot30d($storeId);
        $products = $svc->getStoreProductSnapshot30d($storeId);
        $categories = $svc->getStoreCategorySnapshot30d($storeId);

        return [
            $this->makeRevenueStat($svc, $storeId, $revenue),
            $this->makeOrdersStat($svc, $storeId, $orders),
            $this->makeCustomersStat($svc, $storeId, $customers),
            $this->makeBranchesStat($svc, $storeId, $branches),
            $this->makeProductsStat($svc, $storeId, $products),
            $this->makeCategoriesStat($svc, $storeId, $categories),
        ];
    }

    /**
     * @param  array{current: float, previous: float, percentage_change: float, period: string}  $stats
     */
    private function makeRevenueStat(WidgetDataService $svc, int|string $storeId, array $stats): Stat
    {
        $positive = $stats['percentage_change'] >= 0;

        return Stat::make(__('widgets.store.stats.revenue'), $this->formatMoneyCompact((float) $stats['current']))
            ->description($this->formatPercentTrend((float) $stats['percentage_change']))
            ->descriptionIcon($positive ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown, IconPosition::Before)
            ->descriptionColor($positive ? 'success' : 'danger')
            ->chart($svc->getStoreSparklineRevenueLast30Days($storeId))
            ->chartColor($positive ? 'success' : 'danger')
            ->color($positive ? 'success' : 'danger');
    }

    /**
     * @param  array{current: int, previous: int, percentage_change: float, pending: int, period: string}  $stats
     */
    private function makeOrdersStat(WidgetDataService $svc, int|string $storeId, array $stats): Stat
    {
        $positive = $stats['percentage_change'] >= 0;

        return Stat::make(__('widgets.store.stats.orders'), number_format($stats['current']))
            ->description(__('widgets.store.orders_stat_description', [
                'pending' => $stats['pending'],
                'trend' => $this->formatPercentTrend((float) $stats['percentage_change']),
            ]))
            ->descriptionIcon($positive ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown, IconPosition::Before)
            ->descriptionColor($positive ? 'success' : 'danger')
            ->chart($svc->getStoreSparklineOrdersLast30Days($storeId))
            ->chartColor($positive ? 'success' : 'danger')
            ->color('primary');
    }

    /**
     * @param  array{current: int, previous: int, percentage_change: float, period: string}  $stats
     */
    private function makeCustomersStat(WidgetDataService $svc, int|string $storeId, array $stats): Stat
    {
        $positive = $stats['percentage_change'] >= 0;

        return Stat::make(__('widgets.store.stats.new_customers'), number_format($stats['current']))
            ->description($this->formatPercentTrend((float) $stats['percentage_change']))
            ->descriptionIcon($positive ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown, IconPosition::Before)
            ->descriptionColor($positive ? 'success' : 'danger')
            ->chart($svc->getStoreSparklineCustomersLast30Days($storeId))
            ->chartColor($positive ? 'success' : 'danger')
            ->color('warning');
    }

    /**
     * @param  array{total: int, new_current: int, new_previous: int, percentage_change: float}  $stats
     */
    private function makeBranchesStat(WidgetDataService $svc, int|string $storeId, array $stats): Stat
    {
        $positive = $stats['percentage_change'] >= 0;

        return Stat::make(__('widgets.store.stats.branches'), number_format($stats['total']))
            ->description(__('widgets.store.snapshot_new_trend', [
                'count' => $stats['new_current'],
                'trend' => $this->formatPercentTrend((float) $stats['percentage_change']),
            ]))
            ->descriptionIcon($positive ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown, IconPosition::Before)
            ->descriptionColor($positive ? 'success' : 'danger')
            ->chart($svc->getStoreSparklineBranchesLast30Days($storeId))
            ->chartColor($positive ? 'success' : 'danger')
            ->color('info');
    }

    /**
     * @param  array{total: int, new_current: int, new_previous: int, percentage_change: float}  $stats
     */
    private function makeProductsStat(WidgetDataService $svc, int|string $storeId, array $stats): Stat
    {
        $positive = $stats['percentage_change'] >= 0;

        return Stat::make(__('widgets.store.stats.products'), number_format($stats['total']))
            ->description(__('widgets.store.snapshot_new_trend', [
                'count' => $stats['new_current'],
                'trend' => $this->formatPercentTrend((float) $stats['percentage_change']),
            ]))
            ->descriptionIcon($positive ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown, IconPosition::Before)
            ->descriptionColor($positive ? 'success' : 'danger')
            ->chart($svc->getStoreSparklineProductsLast30Days($storeId))
            ->chartColor($positive ? 'success' : 'danger')
            ->color('secondary');
    }

    /**
     * @param  array{total: int, new_current: int, new_previous: int, percentage_change: float}  $stats
     */
    private function makeCategoriesStat(WidgetDataService $svc, int|string $storeId, array $stats): Stat
    {
        $positive = $stats['percentage_change'] >= 0;

        return Stat::make(__('widgets.store.stats.categories'), number_format($stats['total']))
            ->description(__('widgets.store.snapshot_new_trend', [
                'count' => $stats['new_current'],
                'trend' => $this->formatPercentTrend((float) $stats['percentage_change']),
            ]))
            ->descriptionIcon($positive ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown, IconPosition::Before)
            ->descriptionColor($positive ? 'success' : 'danger')
            ->chart($svc->getStoreSparklineCategoriesLast30Days($storeId))
            ->chartColor($positive ? 'success' : 'danger')
            ->color('success');
    }

    private function formatMoneyCompact(float $amount): string
    {
        if ($amount >= 1_000_000) {
            return __('widgets.store.money_m', ['amountM' => number_format($amount / 1_000_000, 1)]);
        }

        if ($amount >= 1_000) {
            return __('widgets.store.money_k', ['amountk' => number_format($amount / 1_000, 1)]);
        }

        return __('widgets.store.money_full', ['amount' => number_format($amount, 2)]);
    }

    private function formatPercentTrend(float $percentageChange): string
    {
        $rounded = abs(round($percentageChange, 1));

        if ($percentageChange > 0.05) {
            return __('widgets.store.trend_increase', ['value' => $rounded]);
        }

        if ($percentageChange < -0.05) {
            return __('widgets.store.trend_decrease', ['value' => $rounded]);
        }

        return __('widgets.store.trend_flat');
    }
}
