<?php

namespace App\Filament\Resources\StoreCategories\RelationManagers;

use App\Filament\Resources\StoreCategories\Schemas\StoreCategoryForm;
use App\Models\StoreCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChildrenRelationManager extends RelationManager
{
    protected static string $relationship = 'children';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('general.relation_managers.store_category_children');
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        if (! parent::canViewForRecord($ownerRecord, $pageClass)) {
            return false;
        }

        return $ownerRecord instanceof StoreCategory && $ownerRecord->parent_id === null;
    }

    public function form(Schema $schema): Schema
    {
        return StoreCategoryForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle(function (StoreCategory $record): string {
                return $record->getTranslation('name', app()->getLocale())
                    ?: $record->getTranslation('name', 'en');
            })
            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query
                    ->withoutGlobalScopes([
                        SoftDeletingScope::class,
                    ])
                    ->withCount('stores');
            })
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->circular()
                    ->label(__('tables.common.image'))
                    ->collection('store_categories_images')
                    ->toggleable(),

                TextColumn::make('name')
                    ->label(__('tables.common.name'))
                    ->searchable(),

                TextColumn::make('stores_count')
                    ->label(__('tables.store_categories.stores_count'))
                    ->sortable(),

                TextColumn::make('deleted_at')
                    ->label(__('tables.common.deleted_at'))
                    ->dateTime('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime('d-m-Y')
                    ->label(__('tables.common.created_at'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('tables.common.updated_at'))
                    ->dateTime('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
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
