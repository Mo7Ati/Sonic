<?php

namespace App\Filament\Store\Resources\Options\Schemas;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(4)
                    ->schema([
                        Section::make(__('forms.options.content'))
                            ->description(__('forms.options.content_description'))
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
                        Section::make(__('forms.options.visibility'))
                            ->description(__('forms.options.visibility_description'))
                            ->schema([
                                Toggle::make('is_active')
                                    ->label(__('forms.common.is_active'))
                                    ->default(true)
                                    ->required(),
                            ])
                            ->columnSpan(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
