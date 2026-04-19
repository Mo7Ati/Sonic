<?php

namespace App\Filament\Widgets;

use App\Services\WidgetDataService;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class AdminBranchPerformanceWidget extends ChartWidget
{
    /**
     * Not shown on the dashboard by default; attach from a resource or page when needed.
     */
    protected static bool $isDiscovered = false;

    public function getHeading(): string|Htmlable|null
    {
        return __('widgets.admin.charts.branch_performance_heading');
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $service = app(WidgetDataService::class);
        $data = $service->getAdminBranchPerformance();

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
