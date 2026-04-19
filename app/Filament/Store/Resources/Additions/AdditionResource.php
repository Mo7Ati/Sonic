<?php

namespace App\Filament\Store\Resources\Additions;

use App\Filament\Store\Resources\Additions\Pages\CreateAddition;
use App\Filament\Store\Resources\Additions\Pages\EditAddition;
use App\Filament\Store\Resources\Additions\Pages\ListAdditions;
use App\Filament\Store\Resources\Additions\Schemas\AdditionForm;
use App\Filament\Store\Resources\Additions\Tables\AdditionsTable;
use App\Models\Addition;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AdditionResource extends Resource
{
    protected static ?string $model = Addition::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPlusCircle;

    protected static ?int $navigationSort = 23;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AdditionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdditionsTable::configure($table);
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
            'index' => ListAdditions::route('/'),
            'create' => CreateAddition::route('/create'),
            'edit' => EditAddition::route('/{record}/edit'),
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
        return __('general.model_labels.addition');
    }

    public static function getPluralModelLabel(): string
    {
        return __('general.plural_model_labels.additions');
    }

    public static function getNavigationLabel(): string
    {
        return __('general.nav_labels.additions');
    }

    public static function getNavigationGroup(): string
    {
        return __('general.navigation_groups.store_group_catalog');
    }
}
