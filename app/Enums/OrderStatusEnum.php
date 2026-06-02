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
    public function textColor(): string
    {
        return match ($this) {
            self::PENDING => '#D97706',     // amber-600
            self::PREPARING => '#0284C7',   // sky-600
            self::ON_THE_WAY => '#F59E0B',  // primary
            self::COMPLETED => '#16A34A',   // green-600
            self::CANCELLED => '#DC2626',   // red-600
            self::REJECTED => '#DC2626',
        };
    }

    public function backgroundColor(): string
    {
        return match ($this) {
            self::PENDING => '#FFFBEB',     // amber-50
            self::PREPARING => '#F0F9FF',   // sky-50
            self::ON_THE_WAY => '#FFFBEB',  // primary tint
            self::COMPLETED => '#F0FDF4',   // green-50
            self::CANCELLED => '#FEF2F2',   // red-50
            self::REJECTED => '#FEF2F2',
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
            'textColor' => $this->textColor(),
            'backgroundColor' => $this->backgroundColor(),
        ];
    }
}

