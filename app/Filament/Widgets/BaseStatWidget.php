<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;

abstract class BaseStatWidget extends BaseStatsOverviewWidget
{
    protected string $period = 'today';

    protected function getStats(): array
    {
        return [];
    }

    protected function formatPeriodLabel(): string
    {
        return match ($this->period) {
            'today' => __('widgets.store.period.today'),
            'week' => __('widgets.store.period.week'),
            'month' => __('widgets.store.period.month'),
            default => __('widgets.store.period.default'),
        };
    }

    protected function setPeriod(string $period): void
    {
        $this->period = $period;
    }
}
