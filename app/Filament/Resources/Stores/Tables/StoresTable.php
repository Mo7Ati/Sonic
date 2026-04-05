<?php

namespace App\Filament\Resources\Stores\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class StoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('logo')
                    ->circular()
                    ->label(__('tables.stores.logo'))
                    ->collection('store_images')
                    ->toggleable(),

                SpatieMediaLibraryImageColumn::make('cover_image')
                    ->label(__('tables.stores.cover_image'))
                    ->collection('store_cover_images')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('name')
                    ->label(__('tables.common.name'))
                    ->searchable(),

                TextColumn::make('email')
                    ->label(__('tables.common.email'))
                    ->searchable(),

                TextColumn::make('category.name')
                    ->label(__('tables.stores.category'))
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label(__('tables.common.is_active')),

                TextColumn::make('deleted_at')
                    ->label(__('tables.common.deleted_at'))
                    ->dateTime('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('tables.common.created_at'))
                    ->dateTime('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('tables.common.updated_at'))
                    ->dateTime('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label(__('tables.stores.category'))
                    ->relationship('category', 'name')
                    ->preload()
                    ->searchable(),

                TernaryFilter::make('is_active')
                    ->label(__('tables.common.is_active'))
                    ->placeholder(__('tables.common.all')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
