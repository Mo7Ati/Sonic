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
}
