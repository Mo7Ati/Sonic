<?php

namespace App\Providers\Filament;

use App\Filament\Store\Pages\StoreDashboard;
use App\Filament\Store\Widgets\StoreOrdersStatusWidget;
use App\Filament\Store\Widgets\StoreOverviewStatsWidget;
use App\Filament\Store\Widgets\StoreRevenueChartWidget;
use App\Filament\Store\Widgets\StoreTopProductsWidget;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use CraftForge\FilamentLanguageSwitcher\FilamentLanguageSwitcherPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
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
            ->navigationGroups([
                NavigationGroup::make()
                    ->label(fn (): string => __('general.navigation_groups.store_group_orders')),
                NavigationGroup::make()
                    ->label(fn (): string => __('general.navigation_groups.store_group_catalog')),
                NavigationGroup::make()
                    ->label(fn (): string => __('general.navigation_groups.store_group_operations')),
            ])
            ->discoverResources(in: app_path('Filament/Store/Resources'), for: 'App\Filament\Store\Resources')
            ->pages([
                StoreDashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Store/Widgets'), for: 'App\Filament\Store\Widgets')
            ->widgets([
                StoreOverviewStatsWidget::class,
                StoreRevenueChartWidget::class,
                StoreOrdersStatusWidget::class,
                StoreTopProductsWidget::class,
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
