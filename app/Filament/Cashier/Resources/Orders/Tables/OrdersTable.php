<?php

namespace App\Filament\Cashier\Resources\Orders\Tables;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
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
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('accept')
                    ->label(__('actions.orders.accept'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Order $record) => $record->update(['status' => OrderStatusEnum::PREPARING]))
                    ->visible(fn (Order $record): bool => $record->status === OrderStatusEnum::PENDING),

                Action::make('ready')
                    ->label(__('actions.orders.ready'))
                    ->icon('heroicon-o-truck')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->action(fn (Order $record) => $record->update(['status' => OrderStatusEnum::ON_THE_WAY]))
                    ->visible(fn (Order $record): bool => $record->status === OrderStatusEnum::PREPARING),

                Action::make('complete')
                    ->label(__('actions.orders.complete'))
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Order $record) => $record->update(['status' => OrderStatusEnum::COMPLETED]))
                    ->visible(fn (Order $record): bool => $record->status === OrderStatusEnum::ON_THE_WAY),

                Action::make('reject')
                    ->label(__('actions.orders.reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->schema([
                        Textarea::make('cancelled_reason')
                            ->label(__('actions.orders.reject_reason'))
                            ->required(),
                    ])
                    ->action(fn (Order $record, array $data) => $record->update([
                        'status' => OrderStatusEnum::REJECTED,
                        'cancelled_reason' => $data['cancelled_reason'],
                    ]))
                    ->visible(fn (Order $record): bool => $record->status === OrderStatusEnum::PENDING),
            ])
            ->poll('15s');
    }
}
