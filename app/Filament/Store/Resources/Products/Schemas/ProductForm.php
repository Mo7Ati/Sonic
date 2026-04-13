<?php

namespace App\Filament\Store\Resources\Products\Schemas;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use App\Models\Addition;
use App\Models\Category;
use App\Models\Option;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make(__('forms.products.product'))
                    ->tabs([
                        Tab::make(__('forms.products.basic'))
                            ->schema([
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
                                                TextInput::make('price')
                                                    ->label(__('forms.products.price'))
                                                    ->numeric()
                                                    ->required()
                                                    ->minValue(0)
                                                    ->step(0.01)
                                                    ->prefix(__('forms.branches.currency_prefix')),
                                                TextInput::make('compare_price')
                                                    ->label(__('forms.products.compare_price'))
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->step(0.01)
                                                    ->prefix(__('forms.branches.currency_prefix'))
                                                    ->nullable(),
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
                            ]),
                        Tab::make(__('forms.products.options'))
                            ->schema([
                                Repeater::make('productOptions')
                                    ->relationship()
                                    ->schema([
                                        Select::make('option_id')
                                            ->label(__('forms.products.option'))
                                            ->options(
                                                Option::query()
                                                    ->where('store_id', auth()->guard('store')->id())
                                                    ->where('is_active', true)
                                                    ->with('optionGroup')
                                                    ->get()
                                                    ->groupBy(fn ($option) => $option->optionGroup?->getTranslation('name', app()->getLocale()) ?? __('forms.products.ungrouped'))
                                                    ->map(fn ($options) => $options->pluck('name', 'id'))
                                                    ->toArray()
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->distinct(),
                                        TextInput::make('price')
                                            ->label(__('forms.products.price'))
                                            ->numeric()
                                            ->required()
                                            ->minValue(0)
                                            ->step(0.01)
                                            ->default(0)
                                            ->prefix(__('forms.branches.currency_prefix')),
                                        Toggle::make('is_available')
                                            ->label(__('forms.products.option_available'))
                                            ->default(true),
                                        TextInput::make('quantity')
                                            ->label(__('forms.products.quantity'))
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(65535)
                                            ->nullable(),
                                    ])
                                    ->columns(4)
                                    ->defaultItems(0)
                                    ->addActionLabel(__('forms.products.add_option'))
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => Option::find($state['option_id'] ?? null)?->getTranslation('name', app()->getLocale())),
                            ]),
                        Tab::make(__('forms.products.additions'))
                            ->schema([
                                Repeater::make('productAdditions')
                                    ->relationship()
                                    ->schema([
                                        Select::make('addition_id')
                                            ->label(__('forms.products.addition'))
                                            ->options(
                                                Addition::query()
                                                    ->where('store_id', auth()->guard('store')->id())
                                                    ->where('is_active', true)
                                                    ->pluck('name', 'id')
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->distinct(),
                                        TextInput::make('price')
                                            ->label(__('forms.products.price'))
                                            ->numeric()
                                            ->required()
                                            ->minValue(0)
                                            ->step(0.01)
                                            ->default(0)
                                            ->prefix(__('forms.branches.currency_prefix')),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(0)
                                    ->addActionLabel(__('forms.products.add_addition'))
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => Addition::find($state['addition_id'] ?? null)?->getTranslation('name', app()->getLocale())),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
