<?php

namespace App\Filament\Store\Widgets;

use App\Services\WidgetDataService;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class StoreBranchPerformanceWidget extends ChartWidget
{
    public function getHeading(): string|Htmlable|null
    {
        return __('widgets.store.charts.branch_performance_heading');
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $storeId = auth('store')->id();
        $service = app(WidgetDataService::class);
        $data = $service->getStoreBranchPerformance($storeId);

        return [
            'datasets' => [
                [
                    'label' => __('widgets.charts.dataset_revenue'),
                    'data' => $data->pluck('revenue')->toArray(),
                    'backgroundColor' => 'rgb(34, 197, 94)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => __('widgets.charts.dataset_orders'),
                    'data' => $data->pluck('orders')->toArray(),
                    'backgroundColor' => 'rgb(59, 130, 246)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $data->pluck('label')->toArray(),
        ];
    }
}
