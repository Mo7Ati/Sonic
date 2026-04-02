<?php

namespace App\Filament\Store\Resources\Categories;

use App\Filament\Store\Resources\Categories\Pages\CreateCategory;
use App\Filament\Store\Resources\Categories\Pages\EditCategory;
use App\Filament\Store\Resources\Categories\Pages\ListCategories;
use App\Filament\Store\Resources\Categories\Schemas\CategoryForm;
use App\Filament\Store\Resources\Categories\Tables\CategoriesTable;
use App\Models\Category;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('store_id', auth()->guard('store')->id());
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getModelLabel(): string
    {
        return __('general.model_labels.category');
    }

    public static function getPluralModelLabel(): string
    {
        return __('general.plural_model_labels.categories');
    }

    public static function getNavigationLabel(): string
    {
        return __('general.nav_labels.categories');
    }

    public static function getNavigationGroup(): string
    {
        return __('general.navigation_groups.store_management');
    }
}
