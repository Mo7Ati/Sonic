<?php

namespace App\Filament\Resources\Sections\Schemas;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use App\Enums\SectionEnum;
use App\Enums\SectionItemEnum;
use App\Filament\Admin\Resources\SectionResource\Components\SectionItemRepeater;
use App\Models\StoreCategory;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->heading(__('forms.section.setting'))
                    ->schema([
                        Grid::make()
                            ->schema([
                                TranslatableTabs::make()
                                    ->schema([
                                        TextInput::make('title')
                                            ->label(__('forms.common.name'))
                                            ->required(),

                                        Textarea::make('description')
                                            ->label(__('forms.common.description'))
                                            ->rows(2),
                                    ]),

                                Select::make('type')
                                    ->label(__('forms.section.type'))
                                    ->options(SectionEnum::getOptions())
                                    ->required()
                                    ->searchable()
                                    ->live(),

                                Toggle::make('is_active')
                                    ->label(__('forms.common.is_active'))
                                    ->default(true)
                                    ->inline(false),
                            ]),
                    ])->columnSpanFull(),

                Section::make()
                    ->heading(__('forms.section.type_details'))
                    ->schema([
                        // store Categories
                        Select::make('data.store_categories')
                            ->label(__('forms.section.store_categories'))
                            ->options(StoreCategory::query()->get()->where('parent_id', null)->pluck('name', 'id')->toArray())
                            ->required()
                            ->multiple()->visible(fn ($get): bool => $get('type') === SectionEnum::STORE_CATEGORY->value),

                        // written_banner
                        Grid::make()->schema([
                            TextInput::make('data.name')
                                ->label(__('forms.common.name'))
                                ->columnSpanFull()
                                ->required()
                                ->translatableTabs(),
                            Group::make()->schema([
                                ColorPicker::make('data.text_color')
                                    ->label(__('forms.section.text_color')),
                                ColorPicker::make('data.background_color')
                                    ->label(__('forms.section.background_color')),
                            ]),
                        ])->visible(fn ($get): bool => $get('type') === SectionEnum::WRITTEN_BANNER->value),

                        // main_banners type
                        SectionItemRepeater::make('main_banners')
                            ->label(__('forms.section.types.main_banners'))
                            ->relationship('items')
                            ->visible(fn ($get): bool => $get('type') === SectionEnum::MAIN_BANNERS->value),

                        // square_banners type
                        SectionItemRepeater::make('square_banners')
                            ->label(__('forms.section.types.square_banners'))
                            ->relationship('items')
                            ->visible(fn ($get): bool => $get('type') === SectionEnum::SQUIRE_BANNERS->value),

                        // rectangle_banners type
                        SectionItemRepeater::make('rectangle_banners')
                            ->label(__('forms.section.types.rectangle_banners'))
                            ->relationship('items')
                            ->visible(fn ($get): bool => $get('type') === SectionEnum::RECTANGLE_BANNERS->value),

                        // list_items
                        Grid::make()->schema([
                            Select::make('data.type')
                                ->label(__('forms.section.section_item_type'))
                                ->options([
                                    SectionItemEnum::GROUP->value => SectionItemEnum::GROUP->getLabel(),
                                    SectionItemEnum::STORE_CATEGORY->value => SectionItemEnum::STORE_CATEGORY->getLabel(),
                                ])
                                ->required()
                                ->searchable()
                                ->live(),

                            // group type
                            Select::make('data.group_id')
                                ->label(__('forms.section.group'))
                                ->options(\App\Models\Group::query()->get()->pluck('name', 'id')->toArray())
                                ->searchable()
                                ->required()
                                ->visible(fn ($get): bool => $get('data.type') === SectionItemEnum::GROUP->value),

                            // store  type
                            Select::make('data.store_category_id')
                                ->label(__('forms.section.store_category'))
                                ->options(StoreCategory::query()->get()->pluck('name', 'id')->toArray())
                                ->searchable()
                                ->required()
                                ->visible(fn ($get): bool => $get('data.type') === SectionItemEnum::STORE_CATEGORY->value),

                        ])->visible(fn ($get): bool => $get('type') === SectionEnum::LIST_ITEMS->value),
                    ])->columnSpanFull()
                    ->visible(fn ($get): bool => in_array($get('type'), [
                        SectionEnum::WRITTEN_BANNER->value,
                        SectionEnum::MAIN_BANNERS->value,
                        SectionEnum::SQUIRE_BANNERS->value,
                        SectionEnum::RECTANGLE_BANNERS->value,
                        SectionEnum::STORE_CATEGORY->value,
                        SectionEnum::LIST_ITEMS->value,
                    ])),
            ]);
    }
}
