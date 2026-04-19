<?php

namespace App\Filament\Store\Resources\Branches;

use App\Filament\Store\Resources\Branches\Pages\CreateBranch;
use App\Filament\Store\Resources\Branches\Pages\EditBranch;
use App\Filament\Store\Resources\Branches\Pages\ListBranches;
use App\Filament\Store\Resources\Branches\Schemas\BranchForm;
use App\Filament\Store\Resources\Branches\Tables\BranchesTable;
use App\Models\Branch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 40;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return BranchForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BranchesTable::configure($table);
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
            'index' => ListBranches::route('/'),
            'create' => CreateBranch::route('/create'),
            'edit' => EditBranch::route('/{record}/edit'),
        ];
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
        return __('general.model_labels.branch');
    }

    public static function getPluralModelLabel(): string
    {
        return __('general.plural_model_labels.branches');
    }

    public static function getNavigationLabel(): string
    {
        return __('general.nav_labels.branches');
    }

    public static function getNavigationGroup(): string
    {
        return __('general.navigation_groups.store_group_operations');
    }
}
