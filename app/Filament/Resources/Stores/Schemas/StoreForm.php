<?php

namespace App\Filament\Resources\Stores\Schemas;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class StoreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Section::make(__('forms.stores.general_information'))
                            ->description(__('forms.stores.general_information_description'))
                            ->schema([
                                TranslatableTabs::make()
                                    ->schema([
                                        TextInput::make('name')
                                            ->label(__('forms.common.name'))
                                            ->required()
                                            ->maxLength(255),
                                        Textarea::make('description')
                                            ->label(__('forms.common.description'))
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        TagsInput::make('keywords')
                                            ->label(__('forms.common.keywords')),
                                    ]),

                                Section::make(__('forms.common.social_media'))
                                    ->schema([
                                        Repeater::make('social_media')
                                            ->label(__('forms.common.social_media'))
                                            ->schema([
                                                Select::make('platform')
                                                    ->label(__('forms.common.platform'))
                                                    ->options([
                                                        'facebook' => 'Facebook',
                                                        'twitter' => 'Twitter',
                                                        'instagram' => 'Instagram',
                                                        'linkedin' => 'Linkedin',
                                                        'youtube' => 'Youtube',
                                                        'tiktok' => 'Tiktok',
                                                        'website' => 'Website',
                                                    ])->required(),

                                                TextInput::make('url')
                                                    ->label(__('forms.common.url'))
                                                    ->required(),
                                            ])
                                            ->defaultItems(0)
                                            ->columns(2),
                                    ]),
                            ])
                            ->columnSpan(2),

                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                Section::make(__('forms.stores.contact_and_security'))
                                    ->schema([
                                        TextInput::make('email')
                                            ->label(__('forms.common.email'))
                                            ->email()
                                            ->unique(ignoreRecord: true)
                                            ->required(),

                                        TextInput::make('password')
                                            ->label(__('forms.common.password'))
                                            ->password()
                                            ->revealable()
                                            ->required(fn (string $operation): bool => $operation === 'create')
                                            ->dehydrated(fn (?string $state): bool => filled($state))
                                            ->helperText(fn (string $operation): ?string => $operation === 'edit' ? __('forms.common.leave_blank_to_keep_current_password') : null),

                                        TextInput::make('phone')
                                            ->label(__('forms.common.phone'))
                                            ->tel()
                                            ->nullable()
                                            ->unique(ignoreRecord: true),
                                    ]),

                                Section::make(__('forms.stores.store_details'))
                                    ->schema([
                                        // Select::make('primary_category')
                                        //     ->label(__('forms.stores.category'))
                                        //     ->relationship('category', 'name', fn(Builder $query) => $query->roots())
                                        //     ->preload()
                                        //     ->live()
                                        //     ->required()
                                        //     ->searchable(),

                                        // Select::make('category_id')
                                        //     ->label(__('forms.stores.category'))
                                        //     ->relationship('category', 'name', fn(Builder $query) => $query->roots())
                                        //     ->preload()
                                        //     ->searchable(),

                                        SelectTree::make('storeCategories')
                                            ->relationship('storeCategories', 'name', 'parent_id')
                                            ->withCount()
                                            // ->multiple()
                                            ->enableBranchNode(),

                                        SpatieMediaLibraryFileUpload::make('logo')
                                            ->disk('public')
                                            ->label(__('forms.stores.logo'))
                                            ->image()
                                            ->collection('store_images')
                                            ->preserveFilenames()
                                            ->visibility('public'),

                                        SpatieMediaLibraryFileUpload::make('cover_image')
                                            ->disk('public')
                                            ->label(__('forms.stores.cover_image'))
                                            ->image()
                                            ->collection('store_cover_images')
                                            ->preserveFilenames()
                                            ->visibility('public')
                                            ->helperText(__('forms.stores.cover_image_helper')),

                                        Toggle::make('is_active')
                                            ->label(__('forms.common.is_active'))
                                            ->default(true)
                                            ->required(),
                                    ]),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
