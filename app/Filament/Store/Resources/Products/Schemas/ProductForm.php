<?php

namespace App\Filament\Store\Resources\Products\Schemas;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(4)
                    ->schema([
                        Section::make(__('forms.products.content'))
                            ->description(__('forms.products.content_description'))
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

                                        TagsInput::make('keywords')
                                            ->label(__('forms.common.keywords')),
                                    ]),

                            ])
                            ->columnSpan(2),
                        Section::make(__('forms.products.details'))
                            ->description(__('forms.products.details_description'))
                            ->schema([
                                Select::make('category_id')
                                    ->label(__('forms.products.category'))
                                    ->options(Category::query()->where('store_id', auth()->guard('store')->id())->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->rules([
                                        'nullable',
                                        Rule::exists('categories', 'id')->where('store_id', auth()->guard('store')->id()),
                                    ]),
                                TextInput::make('quantity')
                                    ->label(__('forms.products.quantity'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(65535)
                                    ->nullable(),
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
