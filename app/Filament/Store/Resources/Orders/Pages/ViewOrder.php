<?php

namespace App\Filament\Store\Resources\Orders\Pages;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Filament\Store\Resources\Orders\OrderResource;
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
                        TextEntry::make('branch.name')
                            ->label(__('tables.orders.branch')),
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
}
