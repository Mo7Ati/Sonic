<?php

namespace App\Filament\Cashier\Resources\BranchProducts\Tables;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BranchProductsTable
{
    public static function configure(Table $table): Table
    {
        $branchId = auth()->guard('cashier')->user()?->branch_id;

        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('tables.common.name'))
                    ->searchable(),

                TextColumn::make('category.name')
                    ->label(__('tables.products.category'))
                    ->searchable(),

                TextColumn::make('price')
                    ->label(__('tables.products.base_price'))
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('branches.pivot.price')
                    ->label(__('tables.products.branch_price'))
                    ->money('USD')
                    ->placeholder(__('tables.products.uses_base_price'))
                    ->state(function (Product $record) use ($branchId): ?string {
                        $pivot = $record->branches->firstWhere('id', $branchId)?->pivot;

                        return $pivot?->price;
                    }),

                TextColumn::make('branches.pivot.quantity')
                    ->label(__('tables.products.quantity'))
                    ->placeholder('-')
                    ->state(function (Product $record) use ($branchId): ?int {
                        $pivot = $record->branches->firstWhere('id', $branchId)?->pivot;

                        return $pivot?->quantity;
                    }),

                IconColumn::make('branches.pivot.is_available')
                    ->label(__('tables.products.available'))
                    ->boolean()
                    ->state(function (Product $record) use ($branchId): bool {
                        $pivot = $record->branches->firstWhere('id', $branchId)?->pivot;

                        return (bool) $pivot?->is_available;
                    }),
            ])
            ->recordActions([
                Action::make('edit_availability')
                    ->label(__('actions.products.edit_availability'))
                    ->icon('heroicon-o-pencil-square')
                    ->schema([
                        TextInput::make('price')
                            ->label(__('forms.products.branch_price_override'))
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->nullable()
                            ->helperText(__('forms.products.leave_empty_for_base_price')),
                        TextInput::make('quantity')
                            ->label(__('forms.products.quantity'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(65535)
                            ->nullable(),
                        Toggle::make('is_available')
                            ->label(__('forms.products.is_available'))
                            ->default(true),
                    ])
                    ->fillForm(function (Product $record) use ($branchId): array {
                        $pivot = $record->branches->firstWhere('id', $branchId)?->pivot;

                        return [
                            'price' => $pivot?->price,
                            'quantity' => $pivot?->quantity,
                            'is_available' => (bool) $pivot?->is_available,
                        ];
                    })
                    ->action(function (Product $record, array $data) use ($branchId): void {
                        $record->branches()->updateExistingPivot($branchId, [
                            'price' => $data['price'],
                            'quantity' => $data['quantity'],
                            'is_available' => $data['is_available'],
                        ]);
                    }),

                Action::make('toggle_availability')
                    ->label(function (Product $record) use ($branchId): string {
                        $isAvailable = $record->branches->firstWhere('id', $branchId)?->pivot?->is_available;

                        return $isAvailable
                            ? __('actions.products.mark_unavailable')
                            : __('actions.products.mark_available');
                    })
                    ->icon(function (Product $record) use ($branchId): string {
                        $isAvailable = $record->branches->firstWhere('id', $branchId)?->pivot?->is_available;

                        return $isAvailable ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle';
                    })
                    ->color(function (Product $record) use ($branchId): string {
                        $isAvailable = $record->branches->firstWhere('id', $branchId)?->pivot?->is_available;

                        return $isAvailable ? 'danger' : 'success';
                    })
                    ->requiresConfirmation()
                    ->action(function (Product $record) use ($branchId): void {
                        $pivot = $record->branches->firstWhere('id', $branchId)?->pivot;
                        $record->branches()->updateExistingPivot($branchId, [
                            'is_available' => ! $pivot?->is_available,
                        ]);
                    }),
            ]);
    }
}
