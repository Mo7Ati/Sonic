<?php

namespace App\Filament\Widgets;

use App\Services\WidgetDataService;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class AdminOrdersStatusWidget extends ChartWidget
{
    protected static ?int $sort = 11;

    public function getHeading(): string|Htmlable|null
    {
        return __('widgets.admin.charts.orders_by_status_heading');
    }

    public function getDescription(): string|Htmlable|null
    {
        return __('widgets.admin.charts.orders_by_status_description');
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getData(): array
    {
        $service = app(WidgetDataService::class);
        $data = $service->getAdminOrdersStatusChart();

        return [
            'datasets' => [
                [
                    'label' => __('widgets.charts.dataset_orders'),
                    'data' => $data->pluck('value')->toArray(),
                    'backgroundColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(34, 197, 94)',
                        'rgb(239, 68, 68)',
                        'rgb(251, 191, 36)',
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(34, 197, 94)',
                        'rgb(239, 68, 68)',
                        'rgb(251, 191, 36)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $data->pluck('label')->toArray(),
        ];
    }
}
