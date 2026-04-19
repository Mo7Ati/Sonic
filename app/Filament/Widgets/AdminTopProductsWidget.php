<?php

namespace App\Filament\Widgets;

use App\Services\WidgetDataService;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class AdminTopProductsWidget extends ChartWidget
{
    protected static ?int $sort = 12;

    public function getHeading(): string|Htmlable|null
    {
        return __('widgets.admin.charts.top_products_heading');
    }

    public function getDescription(): string|Htmlable|null
    {
        return __('widgets.admin.charts.top_products_description');
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $service = app(WidgetDataService::class);
        $data = $service->getAdminTopProducts(limit: 10);

        return [
            'datasets' => [
                [
                    'label' => __('widgets.charts.dataset_units_sold'),
                    'data' => $data->pluck('value')->toArray(),
                    'backgroundColor' => 'rgb(59, 130, 246)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $data->pluck('label')->toArray(),
        ];
    }
}
