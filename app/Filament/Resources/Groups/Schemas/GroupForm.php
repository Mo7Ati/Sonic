<?php

namespace App\Filament\Resources\Groups\Schemas;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use App\Models\Store;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('forms.groups.heading'))
                    ->description(__('forms.groups.description'))
                    ->schema([
                        TranslatableTabs::make()
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('forms.common.name'))
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Select::make('stores')
                            ->label(__('forms.groups.stores'))
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->options(fn (): array => Store::query()
                                ->orderBy('id')
                                ->get()
                                ->mapWithKeys(fn (Store $store): array => [$store->id => (string) $store->name])
                                ->all())
                            ->required(),

                        Toggle::make('is_active')
                            ->label(__('forms.common.is_active'))
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
