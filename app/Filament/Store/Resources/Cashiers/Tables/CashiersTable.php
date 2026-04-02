<?php

namespace App\Filament\Store\Resources\Cashiers\Tables;

use App\Models\Cashier;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CashiersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('tables.common.name'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('tables.common.email'))
                    ->searchable(),
                TextColumn::make('phone_number')
                    ->label(__('tables.common.phone'))
                    ->searchable(),
                TextColumn::make('branch.name')
                    ->label(__('tables.cashiers.branch'))
                    ->searchable(),
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
