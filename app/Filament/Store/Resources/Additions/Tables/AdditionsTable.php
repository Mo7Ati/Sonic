<?php

namespace App\Filament\Store\Resources\Additions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class AdditionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('tables.common.name'))
                    ->searchable(),
                TextColumn::make('products_count')
                    ->label(__('tables.additions.products_count'))
                    ->counts('products')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('tables.common.is_active'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('tables.common.created_at'))
                    ->dateTime('d-m-Y')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('tables.common.updated_at'))
                    ->dateTime('d-m-Y')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                DeleteAction::make(),
                EditAction::make(),
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
