<?php

namespace App\Enums;

enum PaymentStatusEnum: string
{
    case UNPAID = 'unpaid';
    case PAID = 'paid';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::UNPAID => __('enums.payment_status.unpaid'),
            self::PAID => __('enums.payment_status.paid'),
            self::FAILED => __('enums.payment_status.failed'),
            self::REFUNDED => __('enums.payment_status.refunded'),
        };
    }

    /**
     * Get the color class for this status
     *
     * @return string
     */
    public function color(): string
    {
        return match ($this) {
            self::UNPAID => 'warning',
            self::PAID => 'success',
            self::FAILED => 'destructive',
            self::REFUNDED => 'muted',
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

