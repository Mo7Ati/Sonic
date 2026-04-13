<?php

namespace App\Filament\Store\Resources\OptionGroups;

use App\Filament\Store\Resources\OptionGroups\Pages\CreateOptionGroup;
use App\Filament\Store\Resources\OptionGroups\Pages\EditOptionGroup;
use App\Filament\Store\Resources\OptionGroups\Pages\ListOptionGroups;
use App\Filament\Store\Resources\OptionGroups\Schemas\OptionGroupForm;
use App\Filament\Store\Resources\OptionGroups\Tables\OptionGroupsTable;
use App\Models\OptionGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OptionGroupResource extends Resource
{
    protected static ?string $model = OptionGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return OptionGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OptionGroupsTable::configure($table);
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
            'index' => ListOptionGroups::route('/'),
            'create' => CreateOptionGroup::route('/create'),
            'edit' => EditOptionGroup::route('/{record}/edit'),
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
        return __('general.model_labels.option_group');
    }

    public static function getPluralModelLabel(): string
    {
        return __('general.plural_model_labels.option_groups');
    }

    public static function getNavigationLabel(): string
    {
        return __('general.nav_labels.option_groups');
    }

    public static function getNavigationGroup(): string
    {
        return __('general.navigation_groups.store_management');
    }
}
