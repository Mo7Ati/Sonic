<?php

namespace App\Enums;

enum PaymentStatusEnum: string
{
    case WAIT_FOR_CONFIRMATION = 'wait_for_confirmation';
    case CONFIRMED = 'confirmed';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::WAIT_FOR_CONFIRMATION => __('enums.payment_status.wait_for_confirmation'),
            self::CONFIRMED => __('enums.payment_status.confirmed'),
            self::REJECTED => __('enums.payment_status.rejected'),
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
            self::WAIT_FOR_CONFIRMATION => 'warning',
            self::CONFIRMED => 'success',
            self::REJECTED => 'destructive',
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

