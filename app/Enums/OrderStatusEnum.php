<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case PENDING = 'pending';
    case PREPARING = 'preparing';
    case ON_THE_WAY = 'on_the_way';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('enums.order_status.pending'),
            self::PREPARING => __('enums.order_status.preparing'),
            self::ON_THE_WAY => __('enums.order_status.on_the_way'),
            self::COMPLETED => __('enums.order_status.completed'),
            self::CANCELLED => __('enums.order_status.cancelled'),
            self::REJECTED => __('enums.order_status.rejected'),
        };
    }

    /**
     * Get all enum values as an array
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }


    public static function toArray(): array
    {
        return array_map(
            fn(self $case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases()
        );
    }

    
    public function model()
    {
        return [
            'value' => $this->value,
            'label' => $this->label(),
        ];
    }
}

