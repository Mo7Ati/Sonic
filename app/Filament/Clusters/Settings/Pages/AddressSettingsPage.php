<?php

namespace App\Filament\Clusters\Settings\Pages;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use App\Filament\Clusters\Settings\SettingsCluster;
use App\Settings\AddressSettings;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class AddressSettingsPage extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string $settings = AddressSettings::class;

    protected static ?string $cluster = SettingsCluster::class;

    protected static ?int $navigationSort = 3;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('forms.addresses.fields_section_heading'))
                    ->description(__('forms.addresses.fields_section_description'))
                    ->schema([
                        Repeater::make('fields')
                            ->label(__('forms.addresses.fields'))
                            ->addActionLabel(__('forms.addresses.add_field'))
                            ->reorderable()
                            ->collapsible()
                            ->schema([
                                TextInput::make('key')
                                    ->label(__('forms.addresses.field_key'))
                                    ->required()
                                    ->alphaDash()
                                    ->maxLength(50),

                                TranslatableTabs::make()
                                    ->schema([
                                        TextInput::make('label')
                                            ->label(__('forms.addresses.field_label'))
                                            ->required()
                                            ->maxLength(255),
                                    ]),

                                Toggle::make('is_required')
                                    ->label(__('forms.addresses.is_required'))
                                    ->default(false),
                            ])
                            ->defaultItems(0)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function getTitle(): string
    {
        return __('general.settings.address_settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('general.settings.address_settings');
    }

    public static function getNavigationGroup(): string
    {
        return __('general.navigation_groups.mobile_application');
    }
}
