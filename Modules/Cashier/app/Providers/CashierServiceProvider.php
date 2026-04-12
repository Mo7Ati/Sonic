<?php

namespace Modules\Cashier\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;

class CashierServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Cashier';

    protected string $nameLower = 'cashier';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];
}
