<?php

namespace App\Filament\Clusters\Settings\Pages;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use App\Filament\Clusters\Settings\SettingsCluster;
use App\Settings\OnboardingSettings;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class OnboardingSettingsPage extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string $settings = OnboardingSettings::class;

    protected static ?string $cluster = SettingsCluster::class;

    protected static ?int $navigationSort = 2;


    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('forms.onboarding.steps_section_heading'))
                    ->description(__('forms.onboarding.steps_section_description'))
                    ->schema([
                        Repeater::make('steps')
                            ->label(__('forms.onboarding.steps'))
                            ->addActionLabel(__('forms.onboarding.add_step'))
                            ->reorderable()
                            ->collapsible()
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TranslatableTabs::make()
                                            ->schema([
                                                TextInput::make('title')
                                                    ->label(__('forms.onboarding.step_title'))
                                                    ->required()
                                                    ->maxLength(255),
                                                Textarea::make('description')
                                                    ->label(__('forms.onboarding.step_description'))
                                                    ->required()
                                                    ->rows(4)
                                                    ->columnSpanFull(),
                                            ]),
                                        Grid::make(1)
                                            ->schema([
                                                Select::make('color')
                                                    ->label(__('forms.onboarding.color'))
                                                    ->options([
                                                        'black' => __('forms.onboarding.colors.black'),
                                                        'white' => __('forms.onboarding.colors.white'),
                                                        'amber' => __('forms.onboarding.colors.amber'),
                                                        'blue' => __('forms.onboarding.colors.blue'),
                                                        'green' => __('forms.onboarding.colors.green'),
                                                        'red' => __('forms.onboarding.colors.red'),
                                                    ])
                                                    ->required()
                                                    ->native(false),
                                                FileUpload::make('image')
                                                    ->label(__('forms.common.image'))
                                                    ->image()
                                                    ->imageEditor()
                                                    ->disk('public')
                                                    ->directory('onboarding-steps')
                                                    ->visibility('public'),
                                            ]),
                                    ]),
                            ])
                            ->defaultItems(0)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function getTitle(): string
    {
        return __('general.settings.onboarding_settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('general.settings.onboarding_settings');
    }

    public static function getNavigationGroup(): string
    {
        return __('general.navigation_groups.mobile_application');
    }
}
