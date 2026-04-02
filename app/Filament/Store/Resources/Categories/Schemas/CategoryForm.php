<?php

namespace App\Filament\Store\Resources\Categories\Schemas;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(4)
                    ->schema([
                        Section::make(__('forms.categories.content'))
                            ->description(__('forms.categories.content_description'))
                            ->schema([
                                TranslatableTabs::make()
                                    ->schema([
                                        TextInput::make('name')
                                            ->label(__('forms.common.name'))
                                            ->required()
                                            ->maxLength(255),
                                        Textarea::make('description')
                                            ->label(__('forms.common.description'))
                                            ->rows(4)
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columnSpan(2),
                        Section::make(__('forms.categories.visibility'))
                            ->description(__('forms.categories.visibility_description'))
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
