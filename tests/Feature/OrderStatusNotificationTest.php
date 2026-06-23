<?php

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Customer;
use App\Models\Order;
use App\Notifications\OrderStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\Expo\ExpoChannel;

uses(RefreshDatabase::class);

/**
 * Build a persisted order for the given customer. FK enforcement is disabled
 * for the test database, so we can skip standing up the branch/address graph.
 */
function makeOrder(Customer $customer, OrderStatusEnum $status = OrderStatusEnum::PENDING): Order
{
    return Order::create([
        'status' => $status->value,
        'payment_status' => PaymentStatusEnum::WAIT_FOR_CONFIRMATION->value,
        'customer_id' => $customer->id,
        'customer_data' => ['id' => $customer->id],
        'address_id' => 1,
        'address_data' => [],
        'branch_id' => 1,
        'total_items_amount' => 10,
        'total' => 10,
    ]);
}

it('notifies the customer when the order status changes', function (): void {
    Notification::fake();

    $customer = Customer::factory()->create();
    $order = makeOrder($customer);

    $order->update(['status' => OrderStatusEnum::PREPARING->value]);

    Notification::assertSentTo(
        $customer,
        OrderStatusChanged::class,
        fn (OrderStatusChanged $notification): bool => $notification->status === OrderStatusEnum::PREPARING
            && $notification->order->is($order),
    );
});

it('does not notify when a non-status field changes', function (): void {
    Notification::fake();

    $customer = Customer::factory()->create();
    $order = makeOrder($customer);

    $order->update(['notes' => 'Leave at the door']);

    Notification::assertNothingSent();
});

it('sends the notification through database and expo channels', function (): void {
    Notification::fake();

    $customer = Customer::factory()->create();
    $order = makeOrder($customer);

    $order->update(['status' => OrderStatusEnum::ON_THE_WAY->value]);

    Notification::assertSentTo($customer, OrderStatusChanged::class, function ($notification, array $channels): bool {
        return in_array('database', $channels, true)
            && in_array(ExpoChannel::class, $channels, true);
    });
});
