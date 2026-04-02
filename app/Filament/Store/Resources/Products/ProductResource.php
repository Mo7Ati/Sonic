<?php

namespace App\Filament\Store\Resources\Products;

use App\Filament\Store\Resources\Products\Pages\CreateProduct;
use App\Filament\Store\Resources\Products\Pages\EditProduct;
use App\Filament\Store\Resources\Products\Pages\ListProducts;
use App\Filament\Store\Resources\Products\Schemas\ProductForm;
use App\Filament\Store\Resources\Products\Tables\ProductsTable;
use App\Models\Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
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
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('store_id', auth()->guard('store')->id())
            ->with('category');
    }

    public static function getModelLabel(): string
    {
        return __('general.model_labels.product');
    }

    public static function getPluralModelLabel(): string
    {
        return __('general.plural_model_labels.products');
    }

    public static function getNavigationLabel(): string
    {
        return __('general.nav_labels.products');
    }

    public static function getNavigationGroup(): string
    {
        return __('general.navigation_groups.store_management');
    }
}
