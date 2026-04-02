<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Settings\PlatformSettings;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class PlatformSettingsPage extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string $settings = PlatformSettings::class;

    protected static ?string $cluster = SettingsCluster::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('forms.common.social_media'))
                    ->schema([
                        Repeater::make('social_media')
                            ->label(__('forms.common.social_media'))
                            ->schema([
                                Select::make('platform')
                                    ->label(__('forms.common.platform'))
                                    ->options([
                                        'facebook' => 'Facebook',
                                        'twitter' => 'Twitter',
                                        'instagram' => 'Instagram',
                                        'linkedin' => 'Linkedin',
                                        'youtube' => 'Youtube',
                                        'tiktok' => 'Tiktok',
                                        'website' => 'Website',
                                    ])->required(),

                                TextInput::make('url')
                                    ->label(__('forms.common.url'))
                                    ->required(),
                            ])
                            ->defaultItems(0)
                            ->columns(2),
                    ])->columnSpanFull()
            ]);
    }


    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('general.navigation_groups.control_panel');
    }

    public function getTitle(): string
    {
        return __('general.settings.platform_settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('general.settings.platform_settings');
    }

}
