<?php

namespace App\Observers;

use App\Models\Order;
use App\Notifications\OrderStatusChanged;

class OrderObserver
{
    /**
     * Notify the customer whenever an order's status transitions.
     */
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        $order->customer?->notify(new OrderStatusChanged($order, $order->status));
    }
}
