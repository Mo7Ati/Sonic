<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;

abstract class BaseChartWidget extends ChartWidget
{
    protected string $period = 'month';

    protected function getData(): array
    {
        $data = $this->getChartData();

        return [
            'datasets' => [
                [
                    'label' => $this->getChartLabel(),
                    'data' => $data->pluck('value')->toArray(),
                    'borderColor' => $this->getBorderColor(),
                    'backgroundColor' => $this->getBackgroundColor(),
                    'fill' => $this->shouldFill(),
                    'tension' => 0.4,
                ],
            ],
            'labels' => $data->pluck('label')->toArray(),
        ];
    }

    abstract protected function getChartData(): Collection;

    abstract protected function getChartLabel(): string;

    protected function getBorderColor(): string
    {
        return 'rgb(59, 130, 246)';
    }

    protected function getBackgroundColor(): string|array
    {
        return 'rgba(59, 130, 246, 0.1)';
    }

    protected function shouldFill(): bool
    {
        return true;
    }

    protected function setPeriod(string $period): void
    {
        $this->period = $period;
    }
}
