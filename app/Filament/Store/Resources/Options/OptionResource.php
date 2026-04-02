<?php

namespace App\Filament\Store\Resources\Options;

use App\Filament\Store\Resources\Options\Pages\CreateOption;
use App\Filament\Store\Resources\Options\Pages\EditOption;
use App\Filament\Store\Resources\Options\Pages\ListOptions;
use App\Filament\Store\Resources\Options\Schemas\OptionForm;
use App\Filament\Store\Resources\Options\Tables\OptionsTable;
use App\Models\Option;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OptionResource extends Resource
{
    protected static ?string $model = Option::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return OptionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OptionsTable::configure($table);
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
            'index' => ListOptions::route('/'),
            'create' => CreateOption::route('/create'),
            'edit' => EditOption::route('/{record}/edit'),
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
        return __('general.model_labels.option');
    }

    public static function getPluralModelLabel(): string
    {
        return __('general.plural_model_labels.options');
    }

    public static function getNavigationLabel(): string
    {
        return __('general.nav_labels.options');
    }

    public static function getNavigationGroup(): string
    {
        return __('general.navigation_groups.store_management');
    }
}
