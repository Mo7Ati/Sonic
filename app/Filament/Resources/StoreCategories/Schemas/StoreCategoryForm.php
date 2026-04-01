<?php

namespace App\Filament\Resources\StoreCategories\Schemas;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StoreCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('forms.store_categories.general_information'))
                    ->description(__('forms.store_categories.general_information_description'))
                    ->schema([
                        TranslatableTabs::make(__('forms.store_categories.translation_tabs'))
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

                        SpatieMediaLibraryFileUpload::make('image')
                            ->disk('public')
                            ->label(__('forms.common.image'))
                            ->image()
                            ->collection('store_categories_images')
                            ->visibility('public')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ])->columns(2);
    }
}
