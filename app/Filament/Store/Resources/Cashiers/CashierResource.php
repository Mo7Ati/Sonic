<?php

namespace App\Filament\Store\Resources\Cashiers;

use App\Filament\Store\Resources\Cashiers\Pages\CreateCashier;
use App\Filament\Store\Resources\Cashiers\Pages\EditCashier;
use App\Filament\Store\Resources\Cashiers\Pages\ListCashiers;
use App\Filament\Store\Resources\Cashiers\Schemas\CashierForm;
use App\Filament\Store\Resources\Cashiers\Tables\CashiersTable;
use App\Models\Cashier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CashierResource extends Resource
{
    protected static ?string $model = Cashier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CashierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CashiersTable::configure($table);
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
            'index' => ListCashiers::route('/'),
            'create' => CreateCashier::route('/create'),
            'edit' => EditCashier::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('branch', function (Builder $query): void {
                $query->where('store_id', auth()->guard('store')->id());
            })
            ->with('branch');
    }

    public static function getModelLabel(): string
    {
        return __('general.model_labels.cashier');
    }

    public static function getPluralModelLabel(): string
    {
        return __('general.plural_model_labels.cashiers');
    }

    public static function getNavigationLabel(): string
    {
        return __('general.nav_labels.cashiers');
    }

    public static function getNavigationGroup(): string
    {
        return __('general.navigation_groups.store_management');
    }
}
