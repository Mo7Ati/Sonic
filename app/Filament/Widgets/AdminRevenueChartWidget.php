<?php

namespace App\Filament\Widgets;

use App\Services\WidgetDataService;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

class AdminRevenueChartWidget extends BaseChartWidget
{
    protected static ?int $sort = 10;

    protected string $period = '30d';

    public function getHeading(): string|Htmlable|null
    {
        return __('widgets.admin.charts.revenue_trend_heading');
    }

    public function getDescription(): string|Htmlable|null
    {
        return __('widgets.admin.charts.revenue_trend_description');
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getChartData(): Collection
    {
        $service = app(WidgetDataService::class);

        return $service->getAdminRevenueChart($this->period);
    }

    protected function getChartLabel(): string
    {
        return __('widgets.charts.dataset_revenue');
    }

    protected function getBorderColor(): string
    {
        return 'rgb(34, 197, 94)';
    }

    protected function getBackgroundColor(): string|array
    {
        return 'rgba(34, 197, 94, 0.1)';
    }
}
