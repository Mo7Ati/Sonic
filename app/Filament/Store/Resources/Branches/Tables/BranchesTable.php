<?php

namespace App\Filament\Store\Resources\Branches\Tables;

use App\Enums\BranchStatusEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class BranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('tables.common.name'))
                    ->searchable(),

                TextColumn::make('address')
                    ->label(__('tables.common.address'))
                    ->searchable(),


                TextColumn::make('status')
                    ->formatStateUsing(function (BranchStatusEnum $state): string {
                        return $state->label();
                    })
                    ->badge()
                    ->color(fn(BranchStatusEnum $state): string => match ($state) {
                        BranchStatusEnum::AVAILABLE => 'success',
                        BranchStatusEnum::BUSY => 'warning',
                        BranchStatusEnum::COMING_SOON => 'info',
                        BranchStatusEnum::CLOSED => 'danger',
                    })
                    ->label(__('tables.branches.status'))
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
            ->filters([
                SelectFilter::make('status')
                    ->label(__('tables.branches.status'))
                    ->options(
                        collect(BranchStatusEnum::cases())->mapWithKeys(
                            fn(BranchStatusEnum $case): array => [$case->value => $case->label()]
                        )
                    )
                    ->searchable(),


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
