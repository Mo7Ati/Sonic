<?php

namespace App\Filament\Store\Resources\Branches\Schemas;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use App\Enums\BranchStatusEnum;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(4)
                    ->schema([
                        Section::make(__('forms.branches.general_information'))
                            ->description(__('forms.branches.general_information_description'))
                            ->schema([
                                TranslatableTabs::make()
                                    ->schema([
                                        TextInput::make('name')
                                            ->label(__('forms.common.name'))
                                            ->required()
                                            ->maxLength(255),
                                        Textarea::make('address')
                                            ->label(__('forms.branches.address'))
                                            ->rows(4)
                                            ->columnSpanFull(),
                                    ]),
                            ])->columnSpan(2),
                        Grid::make(1)
                            ->schema([
                                Section::make(__('forms.branches.status_and_visibility'))
                                    ->schema([
                                        Select::make('status')
                                            ->label(__('forms.branches.status'))
                                            ->options(collect(BranchStatusEnum::cases())->mapWithKeys(
                                                fn(BranchStatusEnum $case): array => [$case->value => $case->label()]
                                            ))
                                            ->default(BranchStatusEnum::AVAILABLE->value)
                                            ->required()
                                            ->native(false),
                                        Toggle::make('is_active')
                                            ->label(__('forms.common.is_active'))
                                            ->default(true)
                                            ->required(),
                                    ]),
                                Section::make(__('forms.branches.delivery'))
                                    ->description(__('forms.branches.delivery_description'))
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('delivery_time_from')
                                                    ->label(__('forms.branches.delivery_time_from'))
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->suffix(__('forms.branches.minutes_suffix')),
                                                TextInput::make('delivery_time_to')
                                                    ->label(__('forms.branches.delivery_time_to'))
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->suffix(__('forms.branches.minutes_suffix')),
                                            ]),
                                        TextInput::make('delivery_fee')
                                            ->label(__('forms.branches.delivery_fee'))
                                            ->numeric()
                                            ->minValue(0)
                                            ->step(0.01)
                                            ->prefix(__('forms.branches.currency_prefix')),
                                    ]),
                            ])->columnSpan(2),
                    ])
                    ->columnSpanFull(),
                // Section::make(__('forms.branches.location'))
                //     ->description(__('forms.branches.location_description'))
                //     ->schema([
                //         Grid::make(2)
                //             ->schema([
                //                 TextInput::make('location.latitude')
                //                     ->label(__('forms.branches.latitude'))
                //                     ->numeric()
                //                     ->required()
                //                     ->step(0.000001)
                //                     ->minValue(-90)
                //                     ->maxValue(90),
                //                 TextInput::make('location.longitude')
                //                     ->label(__('forms.branches.longitude'))
                //                     ->numeric()
                //                     ->required()
                //                     ->step(0.000001)
                //                     ->minValue(-180)
                //                     ->maxValue(180),
                //             ]),
                //         Textarea::make('range_of_area_polygon')
                //             ->label(__('forms.branches.delivery_area_polygon'))
                //             ->helperText(__('forms.branches.delivery_area_polygon_helper'))
                //             ->rows(8)
                //             ->columnSpanFull()
                //             ->formatStateUsing(function (mixed $state): string {
                //                 if ($state === null || $state === []) {
                //                     return '';
                //                 }

                //                 if (is_string($state)) {
                //                     return $state;
                //                 }

                //                 return (string) json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                //             })
                //             ->dehydrateStateUsing(function (?string $state): ?array {
                //                 if ($state === null || trim($state) === '') {
                //                     return null;
                //                 }

                //                 $decoded = json_decode($state, true);

                //                 return is_array($decoded) ? $decoded : null;
                //             }),
                //     ])
                //     ->columnSpanFull(),
            ]);
    }
}
