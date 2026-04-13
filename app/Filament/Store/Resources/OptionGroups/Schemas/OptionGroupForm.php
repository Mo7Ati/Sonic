<?php

namespace App\Filament\Store\Resources\OptionGroups\Schemas;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OptionGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(4)
                    ->schema([
                        Section::make(__('forms.option_groups.content'))
                            ->description(__('forms.option_groups.content_description'))
                            ->schema([
                                TranslatableTabs::make()
                                    ->schema([
                                        TextInput::make('name')
                                            ->label(__('forms.common.name'))
                                            ->required()
                                            ->maxLength(255),
                                    ]),
                            ])
                            ->columnSpan(2),
                        Section::make(__('forms.option_groups.visibility'))
                            ->description(__('forms.option_groups.visibility_description'))
                            ->schema([
                                Toggle::make('is_active')
                                    ->label(__('forms.common.is_active'))
                                    ->default(true)
                                    ->required(),
                            ])
                            ->columnSpan(2),
                    ])
                    ->columnSpanFull(),
                Section::make(__('forms.option_groups.options'))
                    ->description(__('forms.option_groups.options_description'))
                    ->schema([
                        Repeater::make('options')
                            ->relationship()
                            ->schema([
                                TranslatableTabs::make()
                                    ->schema([
                                        TextInput::make('name')
                                            ->label(__('forms.common.name'))
                                            ->required()
                                            ->maxLength(255),
                                    ]),
                                Toggle::make('is_active')
                                    ->label(__('forms.common.is_active'))
                                    ->default(true),
                            ])
                            ->defaultItems(0)
                            ->addActionLabel(__('forms.option_groups.add_option'))
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['name'][app()->getLocale()] ?? null),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
