<?php

namespace App\Filament\Store\Resources\Orders\Tables;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Branch;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('tables.orders.order_id'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('customer.name')
                    ->label(__('tables.orders.customer'))
                    ->searchable(),

                TextColumn::make('branch.name')
                    ->label(__('tables.orders.branch'))
                    ->searchable(),

                TextColumn::make('status')
                    ->label(__('tables.orders.status'))
                    ->formatStateUsing(fn (OrderStatusEnum $state): string => $state->label())
                    ->badge()
                    ->color(fn (OrderStatusEnum $state): string => match ($state) {
                        OrderStatusEnum::PENDING => 'warning',
                        OrderStatusEnum::PREPARING => 'info',
                        OrderStatusEnum::ON_THE_WAY => 'primary',
                        OrderStatusEnum::COMPLETED => 'success',
                        OrderStatusEnum::CANCELLED => 'danger',
                        OrderStatusEnum::REJECTED => 'danger',
                    }),

                TextColumn::make('payment_status')
                    ->label(__('tables.orders.payment_status'))
                    ->formatStateUsing(fn (PaymentStatusEnum $state): string => $state->label())
                    ->badge()
                    ->color(fn (PaymentStatusEnum $state): string => $state->color()),

                TextColumn::make('total')
                    ->label(__('tables.orders.total'))
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('tables.common.created_at'))
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('tables.orders.status'))
                    ->options(
                        collect(OrderStatusEnum::cases())->mapWithKeys(
                            fn (OrderStatusEnum $case): array => [$case->value => $case->label()]
                        )
                    ),

                SelectFilter::make('payment_status')
                    ->label(__('tables.orders.payment_status'))
                    ->options(
                        collect(PaymentStatusEnum::cases())->mapWithKeys(
                            fn (PaymentStatusEnum $case): array => [$case->value => $case->label()]
                        )
                    ),

                SelectFilter::make('branch_id')
                    ->label(__('tables.orders.branch'))
                    ->options(fn (): array => Branch::where('store_id', auth()->guard('store')->id())
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
