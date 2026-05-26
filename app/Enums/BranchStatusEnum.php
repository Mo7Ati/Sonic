<?php

namespace App\Enums;

enum BranchStatusEnum: string
{
    case BUSY = 'busy';
    case AVAILABLE = 'available';
    case COMING_SOON = 'coming_soon';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::BUSY => __('enums.branch_status.busy'),
            self::AVAILABLE => __('enums.branch_status.available'),
            self::COMING_SOON => __('enums.branch_status.coming_soon'),
            self::CLOSED => __('enums.branch_status.closed'),
        };
    }

    public function backgroundColor(): string
    {
        return match ($this) {
            self::AVAILABLE => '#DCFCE7',
            self::BUSY => '#FFEDD5',
            self::COMING_SOON => '#FEF3C7',
            self::CLOSED => '#F3F4F6',
        };
    }

    public function textColor(): string
    {
        return match ($this) {
            self::AVAILABLE => '#15803D',
            self::BUSY => '#C2410C',
            self::COMING_SOON => '#B45309',
            self::CLOSED => '#6B7280',
        };
    }

    public function fullModel()
    {
        return [
            'label' => $this->label(),
            'value' => $this->value,
            'textColor' => $this->textColor(),
            'backgroundColor' => $this->backgroundColor(),
        ];
    }
}
