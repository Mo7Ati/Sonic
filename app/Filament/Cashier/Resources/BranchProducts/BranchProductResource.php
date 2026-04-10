<?php

namespace App\Filament\Cashier\Resources\BranchProducts;

use App\Filament\Cashier\Resources\BranchProducts\Pages\ListBranchProducts;
use App\Filament\Cashier\Resources\BranchProducts\Tables\BranchProductsTable;
use App\Models\Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BranchProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'branch-products';

    public static function table(Table $table): Table
    {
        return BranchProductsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBranchProducts::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $branchId = auth()->guard('cashier')->user()?->branch_id;

        return parent::getEloquentQuery()
            ->whereHas('branches', fn (Builder $q) => $q->where('branches.id', $branchId))
            ->with(['category', 'branches' => fn ($q) => $q->where('branches.id', $branchId)]);
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
}
