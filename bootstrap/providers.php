<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\CashierPanelProvider;
use App\Providers\Filament\StorePanelProvider;
use App\Providers\WhatsAppServiceProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    CashierPanelProvider::class,
    StorePanelProvider::class,
    WhatsAppServiceProvider::class,
];
