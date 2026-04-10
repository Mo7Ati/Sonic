<?php

namespace App\Filament\Store\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('tables.common.name'))
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label(__('tables.products.category'))
                    ->searchable(),
                TextColumn::make('price')
                    ->label(__('tables.products.price'))
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label(__('tables.products.quantity'))
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('tables.common.is_active'))
                    ->boolean(),
                IconColumn::make('is_accepted')
                    ->label(__('tables.products.accepted'))
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
            ->recordActions([
                DeleteAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
