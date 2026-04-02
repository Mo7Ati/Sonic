<?php

namespace App\Filament\Clusters\Settings;

use App\Filament\Clusters\Settings\Pages\PlatformSettingsPage;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class SettingsCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    public static function getNavigationLabel(): string
    {
        return __('general.nav_labels.settings');
    }
}
