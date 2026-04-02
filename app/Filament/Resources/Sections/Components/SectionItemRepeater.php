<?php

namespace App\Filament\Admin\Resources\SectionResource\Components;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use App\Enums\SectionEnum;
use App\Enums\SectionItemEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;

class SectionItemRepeater
{

    public static function make($name)
    {
        return Repeater::make($name)
            ->label(__("forms.section.types.$name"))
            ->schema([
                \Filament\Schemas\Components\Grid::make()->schema([
                    Group::make()
                        ->schema([
                            Select::make('type')
                                ->label(__('forms.section.section_item_type'))
                                ->options(SectionItemEnum::getOptions())
                                ->required()
                                ->searchable()
                                ->columnSpanFull()
                                ->live(),
                        ]),

                    Group::make()
                        ->schema([
                            TextInput::make('data.title')
                                ->label(__('forms.common.name'))
                                ->required()
                                ->translatableTabs()
                                ->visible(fn($get): bool => $name === SectionEnum::SQUIRE_BANNERS->value),


                            SpatieMediaLibraryFileUpload::make('image')
                                ->disk('public')
                                ->label(__('forms.section.section_item_image'))
                                ->image()
                                ->imageEditor()
                                ->imageEditorMode(2)
                                ->collection('section-item')
                                ->visibility('public')
                                ->preserveFilenames(),

                            //group type
                            Select::make('group_id')
                                ->label(__('forms.section.group'))
                                ->options(\App\Models\Group::query()->get()->pluck('name', 'id')->toArray())
                                ->searchable()
                                ->required()
                                ->visible(fn($get): bool => $get('type') === SectionItemEnum::GROUP->value),

                            //store type
                            Select::make('store_id')
                                ->label(__('forms.section.store'))
                                ->options(\App\Models\Store::query()->get()->pluck('name', 'id')->toArray())
                                ->searchable()
                                ->required()
                                ->visible(fn($get): bool => $get('type') === SectionItemEnum::STORE->value),

                            //external_link
                            TextInput::make('data.external_link')
                                ->label(__('forms.section.section_item_external_link'))
                                ->url()
                                ->required()
                                ->visible(fn($get): bool => $get('type') === SectionItemEnum::EXTERNAL_LINK->value),

                            //category
                            Select::make('store_category_id')
                                ->label(__('forms.section_item.store_category'))
                                ->options(\App\Models\StoreCategory::query()->get()->pluck('name', 'id')->toArray())
                                ->searchable()
                                ->required()
                                ->visible(fn($get): bool => $get('type') === SectionItemEnum::STORE_CATEGORY->value),
                        ]),
                ])
            ]);
    }

}
