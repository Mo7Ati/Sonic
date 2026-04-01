<?php

namespace App\Enums;

enum PanelsEnum: string
{
    case ADMIN = 'admin';
    case STORE = 'store';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::STORE => 'Store',
        };
    }

    /**
     * Get all enum cases as an array with value and label
     *
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
}
