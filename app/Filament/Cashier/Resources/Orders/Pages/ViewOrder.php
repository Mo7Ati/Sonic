<?php

namespace App\Filament\Cashier\Resources\Orders\Pages;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Filament\Cashier\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('tables.orders.order_details'))
                    ->schema([
                        TextEntry::make('id')
                            ->label(__('tables.orders.order_id')),
                        TextEntry::make('customer.name')
                            ->label(__('tables.orders.customer')),
                        TextEntry::make('status')
                            ->label(__('tables.orders.status'))
                            ->formatStateUsing(fn (OrderStatusEnum $state): string => $state->label())
                            ->badge()
                            ->color(fn (OrderStatusEnum $state): string => match ($state) {
                                OrderStatusEnum::PENDING => 'warning',
                                OrderStatusEnum::PREPARING => 'info',
                                OrderStatusEnum::ON_THE_WAY => 'primary',
                                OrderStatusEnum::COMPLETED => 'success',
                                OrderStatusEnum::CANCELLED, OrderStatusEnum::REJECTED => 'danger',
                            }),
                        TextEntry::make('payment_status')
                            ->label(__('tables.orders.payment_status'))
                            ->formatStateUsing(fn (PaymentStatusEnum $state): string => $state->label())
                            ->badge()
                            ->color(fn (PaymentStatusEnum $state): string => $state->color()),
                        TextEntry::make('total_items_amount')
                            ->label(__('tables.orders.items_total'))
                            ->money('USD'),
                        TextEntry::make('delivery_amount')
                            ->label(__('tables.orders.delivery_amount'))
                            ->money('USD'),
                        TextEntry::make('total')
                            ->label(__('tables.orders.total'))
                            ->money('USD'),
                        TextEntry::make('notes')
                            ->label(__('tables.orders.notes'))
                            ->placeholder('-'),
                        TextEntry::make('cancelled_reason')
                            ->label(__('tables.orders.cancelled_reason'))
                            ->placeholder('-')
                            ->visible(fn (Order $record): bool => in_array($record->status, [
                                OrderStatusEnum::CANCELLED,
                                OrderStatusEnum::REJECTED,
                            ])),
                        TextEntry::make('created_at')
                            ->label(__('tables.common.created_at'))
                            ->dateTime('d-m-Y H:i'),
                    ])->columns(3),

                Section::make(__('tables.orders.items'))
                    ->schema([
                        RepeatableEntry::make('items')
                            ->schema([
                                TextEntry::make('product.name')
                                    ->label(__('tables.orders.product')),
                                TextEntry::make('quantity')
                                    ->label(__('tables.orders.quantity')),
                                TextEntry::make('unit_price')
                                    ->label(__('tables.orders.unit_price'))
                                    ->money('USD'),
                                TextEntry::make('total_price')
                                    ->label(__('tables.orders.total'))
                                    ->money('USD'),
                            ])->columns(4),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('accept')
                ->label(__('actions.orders.accept'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(fn () => $this->record->update(['status' => OrderStatusEnum::PREPARING]))
                ->visible(fn (): bool => $this->record->status === OrderStatusEnum::PENDING),

            Action::make('ready')
                ->label(__('actions.orders.ready'))
                ->icon('heroicon-o-truck')
                ->color('primary')
                ->requiresConfirmation()
                ->action(fn () => $this->record->update(['status' => OrderStatusEnum::ON_THE_WAY]))
                ->visible(fn (): bool => $this->record->status === OrderStatusEnum::PREPARING),

            Action::make('complete')
                ->label(__('actions.orders.complete'))
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->action(fn () => $this->record->update(['status' => OrderStatusEnum::COMPLETED]))
                ->visible(fn (): bool => $this->record->status === OrderStatusEnum::ON_THE_WAY),

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
                ->action(fn (array $data) => $this->record->update([
                    'status' => OrderStatusEnum::REJECTED,
                    'cancelled_reason' => $data['cancelled_reason'],
                ]))
                ->visible(fn (): bool => $this->record->status === OrderStatusEnum::PENDING),
        ];
    }
}
