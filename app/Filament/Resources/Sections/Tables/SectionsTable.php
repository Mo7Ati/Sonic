<?php

namespace App\Filament\Resources\Sections\Tables;

use App\Enums\SectionEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label(__('forms.section.type'))
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function (SectionEnum $state): string {
                        return $state->getLabel();
                    }),

                TextColumn::make('title')
                    ->label(__('forms.common.title'))
                    ->searchable()
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label(__('forms.common.is_active')),

                TextColumn::make('created_at')
                    ->label(__('forms.common.created_at'))
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('forms.common.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('forms.section.type'))
                    ->options(SectionEnum::getOptions()),

                SelectFilter::make('group_id')
                    ->label(__('forms.section.group'))
                    ->relationship('group', 'name'),

                TernaryFilter::make('is_active')
                    ->label(__('forms.common.is_active')),
            ])
            ->recordActions([
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
