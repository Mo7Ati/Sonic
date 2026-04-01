<?php

namespace App\Enums;

enum PermissionsEnum: string
{
    // dashboard permissions
    case DASHBOARD_INDEX = 'dashboard.index';

    // admin permissions
    case ADMINS_INDEX = 'admins.index';
    case ADMINS_SHOW = 'admins.show';
    case ADMINS_CREATE = 'admins.create';
    case ADMINS_UPDATE = 'admins.update';
    case ADMINS_DESTROY = 'admins.destroy';

    // roles permissions
    case ROLES_INDEX = 'roles.index';
    case ROLES_SHOW = 'roles.show';
    case ROLES_CREATE = 'roles.create';
    case ROLES_UPDATE = 'roles.update';
    case ROLES_DESTROY = 'roles.destroy';

    // stores permissions
    case STORES_INDEX = 'stores.index';
    case STORES_SHOW = 'stores.show';
    case STORES_CREATE = 'stores.create';
    case STORES_UPDATE = 'stores.update';
    case STORES_DESTROY = 'stores.destroy';

    // store categories permissions
    case STORE_CATEGORIES_INDEX = 'store-categories.index';
    case STORE_CATEGORIES_SHOW = 'store-categories.show';
    case STORE_CATEGORIES_CREATE = 'store-categories.create';
    case STORE_CATEGORIES_UPDATE = 'store-categories.update';
    case STORE_CATEGORIES_DESTROY = 'store-categories.destroy';

    // products permissions
    case PRODUCTS_INDEX = 'products.index';
    case PRODUCTS_SHOW = 'products.show';
    case PRODUCTS_CREATE = 'products.create';
    case PRODUCTS_UPDATE = 'products.update';
    case PRODUCTS_DESTROY = 'products.destroy';

    // orders permissions
    case ORDERS_INDEX = 'orders.index';
    case ORDERS_SHOW = 'orders.show';
    case ORDERS_CREATE = 'orders.create';
    case ORDERS_UPDATE = 'orders.update';
    case ORDERS_DESTROY = 'orders.destroy';

    // users permissions
    case USERS_INDEX = 'users.index';
    case USERS_SHOW = 'users.show';
    case USERS_CREATE = 'users.create';
    case USERS_UPDATE = 'users.update';
    case USERS_DESTROY = 'users.destroy';

    // sections permissions
    case SECTIONS_INDEX = 'sections.index';
    case SECTIONS_SHOW = 'sections.show';
    case SECTIONS_CREATE = 'sections.create';
    case SECTIONS_UPDATE = 'sections.update';
    case SECTIONS_DESTROY = 'sections.destroy';

    // transactions permissions
    case TRANSACTIONS_INDEX = 'transactions.index';

    // wallets permissions
    case WALLETS_INDEX = 'wallets.index';

    public function label(): string
    {
        return match ($this) {
            self::DASHBOARD_INDEX => __('enums.permissions.dashboard.index'),
            self::ADMINS_INDEX => __('enums.permissions.admins.index'),
            self::ADMINS_SHOW => __('enums. permissions.admins.show'),
            self::ADMINS_CREATE => __('enums.permissions.admins.create'),
            self::ADMINS_UPDATE => __('enums.permissions.admins.update'),
            self::ADMINS_DESTROY => __('enums.permissions.admins.destroy'),
            self::ROLES_INDEX => __('enums.permissions.roles.index'),
            self::ROLES_SHOW => __('enums.permissions.roles.show'),
            self::ROLES_CREATE => __('enums.permissions.roles.create'),
            self::ROLES_UPDATE => __('enums.permissions.roles.update'),
            self::ROLES_DESTROY => __('enums.permissions.roles.destroy'),
            self::STORES_INDEX => __('enums.permissions.stores.index'),
            self::STORES_SHOW => __('enums.permissions.stores.show'),
            self::STORES_CREATE => __('enums.permissions.stores.create'),
            self::STORES_UPDATE => __('enums.permissions.stores.update'),
            self::STORES_DESTROY => __('enums.permissions.stores.destroy'),
            self::STORE_CATEGORIES_INDEX => __('enums.permissions.store-categories.index'),
            self::STORE_CATEGORIES_SHOW => __('enums.permissions.store-categories.show'),
            self::STORE_CATEGORIES_CREATE => __('enums.permissions.store-categories.create'),
            self::STORE_CATEGORIES_UPDATE => __('enums.permissions.store-categories.update'),
            self::STORE_CATEGORIES_DESTROY => __('enums.permissions.store-categories.destroy'),
            self::PRODUCTS_INDEX => __('enums.permissions.products.index'),
            self::PRODUCTS_SHOW => __('enums.permissions.products.show'),
            self::PRODUCTS_CREATE => __('enums.permissions.products.create'),
            self::PRODUCTS_UPDATE => __('enums.permissions.products.update'),
            self::PRODUCTS_DESTROY => __('enums.permissions.products.destroy'),
            self::ORDERS_INDEX => __('enums.permissions.orders.index'),
            self::ORDERS_SHOW => __('enums.permissions.orders.show'),
            self::ORDERS_CREATE => __('enums.permissions.orders.create'),
            self::ORDERS_UPDATE => __('enums.permissions.orders.update'),
            self::ORDERS_DESTROY => __('enums.permissions.orders.destroy'),
            self::USERS_INDEX => __('enums.permissions.users.index'),
            self::USERS_SHOW => __('enums.permissions.users.show'),
            self::USERS_CREATE => __('enums.permissions.users.create'),
            self::USERS_UPDATE => __('enums.permissions.users.update'),
            self::USERS_DESTROY => __('enums.permissions.users.destroy'),
            self::SECTIONS_INDEX => __('enums.permissions.sections.index'),
            self::SECTIONS_SHOW => __('enums.permissions.sections.show'),
            self::SECTIONS_CREATE => __('enums.permissions.sections.create'),
            self::SECTIONS_UPDATE => __('enums.permissions.sections.update'),
            self::SECTIONS_DESTROY => __('enums.permissions.sections.destroy'),
            self::TRANSACTIONS_INDEX => __('enums.permissions.transactions.index'),
            self::WALLETS_INDEX => __('enums.permissions.wallets.index'),
        };
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
}
