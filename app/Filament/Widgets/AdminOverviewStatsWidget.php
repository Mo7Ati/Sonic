<?php

namespace App\Filament\Widgets;

use App\Services\WidgetDataService;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminOverviewStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = null;

    protected ?string $description = null;

    protected function getDescription(): ?string
    {
        return __('widgets.admin.overview_description');
    }

    protected function getStats(): array
    {
        $svc = app(WidgetDataService::class);

        $revenue = $svc->getAdminRevenue30dStats();
        $orders = $svc->getAdminOrders30dStats();
        $customers = $svc->getAdminCustomers30dStats();
        $stores = $svc->getAdminStoreSnapshot30d();
        $branches = $svc->getAdminBranchSnapshot30d();
        $products = $svc->getAdminProductSnapshot30d();

        return [
            $this->makeRevenueStat($svc, $revenue),
            $this->makeOrdersStat($svc, $orders),
            $this->makeCustomersStat($svc, $customers),
            $this->makeStoresStat($svc, $stores),
            $this->makeBranchesStat($svc, $branches),
            $this->makeProductsStat($svc, $products),
        ];
    }

    /**
     * @param  array{current: float, previous: float, percentage_change: float, period: string}  $stats
     */
    private function makeRevenueStat(WidgetDataService $svc, array $stats): Stat
    {
        $positive = $stats['percentage_change'] >= 0;

        return Stat::make(__('widgets.admin.stats.revenue'), $this->formatMoneyCompact((float) $stats['current']))
            ->description($this->formatPercentTrend((float) $stats['percentage_change']))
            ->descriptionIcon($positive ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown, IconPosition::Before)
            ->descriptionColor($positive ? 'success' : 'danger')
            ->chart($svc->getAdminSparklineRevenueLast30Days())
            ->chartColor($positive ? 'success' : 'danger')
            ->color($positive ? 'success' : 'danger');
    }

    /**
     * @param  array{current: int, previous: int, percentage_change: float, pending: int, period: string}  $stats
     */
    private function makeOrdersStat(WidgetDataService $svc, array $stats): Stat
    {
        $positive = $stats['percentage_change'] >= 0;

        return Stat::make(__('widgets.admin.stats.orders'), number_format($stats['current']))
            ->description(__('widgets.admin.orders_stat_description', [
                'pending' => $stats['pending'],
                'trend' => $this->formatPercentTrend((float) $stats['percentage_change']),
            ]))
            ->descriptionIcon($positive ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown, IconPosition::Before)
            ->descriptionColor($positive ? 'success' : 'danger')
            ->chart($svc->getAdminSparklineOrdersLast30Days())
            ->chartColor($positive ? 'success' : 'danger')
            ->color('primary');
    }

    /**
     * @param  array{current: int, previous: int, percentage_change: float, period: string}  $stats
     */
    private function makeCustomersStat(WidgetDataService $svc, array $stats): Stat
    {
        $positive = $stats['percentage_change'] >= 0;

        return Stat::make(__('widgets.admin.stats.new_customers'), number_format($stats['current']))
            ->description($this->formatPercentTrend((float) $stats['percentage_change']))
            ->descriptionIcon($positive ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown, IconPosition::Before)
            ->descriptionColor($positive ? 'success' : 'danger')
            ->chart($svc->getAdminSparklineCustomersLast30Days())
            ->chartColor($positive ? 'success' : 'danger')
            ->color('warning');
    }

    /**
     * @param  array{total: int, new_current: int, new_previous: int, percentage_change: float}  $stats
     */
    private function makeStoresStat(WidgetDataService $svc, array $stats): Stat
    {
        $positive = $stats['percentage_change'] >= 0;

        return Stat::make(__('widgets.admin.stats.active_stores'), number_format($stats['total']))
            ->description(__('widgets.admin.snapshot_new_trend', [
                'count' => $stats['new_current'],
                'trend' => $this->formatPercentTrend((float) $stats['percentage_change']),
            ]))
            ->descriptionIcon($positive ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown, IconPosition::Before)
            ->descriptionColor($positive ? 'success' : 'danger')
            ->chart($svc->getAdminSparklineStoresLast30Days())
            ->chartColor($positive ? 'success' : 'danger')
            ->color('secondary');
    }

    /**
     * @param  array{total: int, new_current: int, new_previous: int, percentage_change: float}  $stats
     */
    private function makeBranchesStat(WidgetDataService $svc, array $stats): Stat
    {
        $positive = $stats['percentage_change'] >= 0;

        return Stat::make(__('widgets.admin.stats.branches'), number_format($stats['total']))
            ->description(__('widgets.admin.snapshot_new_trend', [
                'count' => $stats['new_current'],
                'trend' => $this->formatPercentTrend((float) $stats['percentage_change']),
            ]))
            ->descriptionIcon($positive ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown, IconPosition::Before)
            ->descriptionColor($positive ? 'success' : 'danger')
            ->chart($svc->getAdminSparklineBranchesLast30Days())
            ->chartColor($positive ? 'success' : 'danger')
            ->color('info');
    }

    /**
     * @param  array{total: int, new_current: int, new_previous: int, percentage_change: float}  $stats
     */
    private function makeProductsStat(WidgetDataService $svc, array $stats): Stat
    {
        $positive = $stats['percentage_change'] >= 0;

        return Stat::make(__('widgets.admin.stats.products'), number_format($stats['total']))
            ->description(__('widgets.admin.snapshot_new_trend', [
                'count' => $stats['new_current'],
                'trend' => $this->formatPercentTrend((float) $stats['percentage_change']),
            ]))
            ->descriptionIcon($positive ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown, IconPosition::Before)
            ->descriptionColor($positive ? 'success' : 'danger')
            ->chart($svc->getAdminSparklineProductsLast30Days())
            ->chartColor($positive ? 'success' : 'danger')
            ->color('success');
    }

    private function formatMoneyCompact(float $amount): string
    {
        if ($amount >= 1_000_000) {
            return __('widgets.admin.money_m', ['amount' => number_format($amount / 1_000_000, 1)]);
        }

        if ($amount >= 1_000) {
            return __('widgets.admin.money_k', ['amount' => number_format($amount / 1_000, 1)]);
        }

        return __('widgets.admin.money_full', ['amount' => number_format($amount, 2)]);
    }

    private function formatPercentTrend(float $percentageChange): string
    {
        $rounded = abs(round($percentageChange, 1));

        if ($percentageChange > 0.05) {
            return __('widgets.admin.trend_increase', ['value' => $rounded]);
        }

        if ($percentageChange < -0.05) {
            return __('widgets.admin.trend_decrease', ['value' => $rounded]);
        }

        return __('widgets.admin.trend_flat');
    }
}
