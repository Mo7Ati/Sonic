<?php

namespace App\Filament\Clusters\Settings\Pages;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use App\Filament\Clusters\Settings\SettingsCluster;
use App\Settings\CustomPages;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CustomPagesSettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string $settings = CustomPages::class;

    protected static ?string $cluster = SettingsCluster::class;

    protected static ?int $navigationSort = 3;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('forms.custom_pages.pages_section_heading'))
                    ->description(__('forms.custom_pages.pages_section_description'))
                    ->schema([
                        Repeater::make('pages')
                            ->label(__('forms.custom_pages.pages'))
                            ->addActionLabel(__('forms.custom_pages.add_page'))
                            ->collapsible()
                            ->schema([
                                TranslatableTabs::make()
                                    ->schema([
                                        TextInput::make('title')
                                            ->label(__('forms.custom_pages.page_title'))
                                            ->required()
                                            ->maxLength(255),
                                    ]),

                                Textarea::make('content')
                                    ->label(__('forms.custom_pages.page_content'))
                                    ->required()
                                    ->rows(4)
                                    ->columnSpanFull(),

                            ])
                            ->defaultItems(0)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function getTitle(): string
    {
        return __('general.settings.custom_pages_settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('general.settings.custom_pages_settings');
    }

    public static function getNavigationGroup(): string
    {
        return __('general.navigation_groups.mobile_application');
    }
}
