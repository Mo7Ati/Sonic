<?php

namespace App\Providers;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use App\Listeners\PruneExpoPushToken;
use App\Policies\RolePolicy;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Role::class, RolePolicy::class);

        Event::listen(NotificationFailed::class, PruneExpoPushToken::class);

        TranslatableTabs::configureUsing(function (TranslatableTabs $component) {
            $component
                // locales labels
                ->localesLabels([
                    'ar' => __('general.locales.ar'),
                    'en' => __('general.locales.en'),
                ])
                // default locales
                ->locales(['ar', 'en']);
        });
    }
}
