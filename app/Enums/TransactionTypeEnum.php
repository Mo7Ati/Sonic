<?php

namespace App\Enums;

enum TransactionTypeEnum: string
{
    case DEPOSIT_ORDER_TOTAL_IN_STORE_WALLET = 'ORDER_PAYMENT';
    case WITHDRAW_PLATFORM_FEE_FROM_STORE_WALLET = 'WITHDRAW_PLATFORM_FEE_FROM_STORE_WALLET';
    case DEPOSIT_PLATFORM_FEE_TO_PLATFORM_WALLET = 'DEPOSIT_PLATFORM_FEE_TO_PLATFORM_WALLET';
    case DEPOSIT_STORE_SUBSCRIPTION_TO_PLATFORM_WALLET = 'DEPOSIT_STORE_SUBSCRIPTION_TO_PLATFORM_WALLET';

    public function label(): string
    {
        return match ($this) {
            self::DEPOSIT_ORDER_TOTAL_IN_STORE_WALLET => __('enums.transaction_type.deposit_order_total_in_store_wallet'),
            self::WITHDRAW_PLATFORM_FEE_FROM_STORE_WALLET => __('enums.transaction_type.withdraw_platform_fee_from_store_wallet'),
            self::DEPOSIT_PLATFORM_FEE_TO_PLATFORM_WALLET => __('enums.transaction_type.deposit_platform_fee_to_platform_wallet'),
            self::DEPOSIT_STORE_SUBSCRIPTION_TO_PLATFORM_WALLET => __('enums.transaction_type.deposit_store_subscription_to_platform_wallet'),
        };
    }
}

