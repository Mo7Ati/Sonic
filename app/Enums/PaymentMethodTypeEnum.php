<?php

namespace App\Enums;

enum PaymentMethodTypeEnum: string
{
    case BOP = 'bop';
    case PALPAY = 'palpay';
    case JAWWAL_PAY = 'jawwal_pay';

    public function label(): string
    {
        return match ($this) {
            self::BOP => __('enums.payment_method_type.bop'),
            self::PALPAY => __('enums.payment_method_type.palpay'),
            self::JAWWAL_PAY => __('enums.payment_method_type.jawwal_pay'),
        };
    }

    /**
     * Whether this method is identified by a bank account number or a phone number.
     */
    public function identifier(): string
    {
        return match ($this) {
            self::BOP => 'account_number',
            self::PALPAY, self::JAWWAL_PAY => 'phone_number',
        };
    }

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return array<array{value: string, label: string}>
     */
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

    public function model(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label(),
        ];
    }
}
