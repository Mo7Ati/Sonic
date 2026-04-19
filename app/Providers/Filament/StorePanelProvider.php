<?php

namespace App\Providers\Filament;

use App\Filament\Store\Widgets\StoreBranchPerformanceWidget;
use App\Filament\Store\Widgets\StoreCustomersWidget;
use App\Filament\Store\Widgets\StoreOrdersStatusWidget;
use App\Filament\Store\Widgets\StoreOrdersWidget;
use App\Filament\Store\Widgets\StoreProductsWidget;
use App\Filament\Store\Widgets\StoreRevenueChartWidget;
use App\Filament\Store\Widgets\StoreRevenueWidget;
use App\Filament\Store\Widgets\StoreTopProductsWidget;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use CraftForge\FilamentLanguageSwitcher\FilamentLanguageSwitcherPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class StorePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('store')
            ->path('store')
            ->spa()
            ->login()
            ->authGuard('store')
            ->profile()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Store/Resources'), for: 'App\Filament\Store\Resources')
            ->discoverPages(in: app_path('Filament/Store/Pages'), for: 'App\Filament\Store\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Store/Widgets'), for: 'App\Filament\Store\Widgets')
            ->widgets([
                StoreRevenueWidget::class,
                StoreOrdersWidget::class,
                StoreCustomersWidget::class,
                StoreProductsWidget::class,
                StoreRevenueChartWidget::class,
                StoreOrdersStatusWidget::class,
                StoreTopProductsWidget::class,
                StoreBranchPerformanceWidget::class,
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])->plugins([
                FilamentShieldPlugin::make(),
                FilamentLanguageSwitcherPlugin::make()
                    ->locales([
                        ['code' => 'ar', 'name' => 'Arabic', 'flag' => 'ps'],
                        ['code' => 'en', 'name' => 'English', 'flag' => 'us'],
                    ])
                    ->rememberLocale(days: 365)
                    ->showOnAuthPages(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
