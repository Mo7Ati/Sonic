<?php

namespace App\Console\Commands;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Events\OrderCreated;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('simulate:order
    {--branch= : The branch id to place the order on (defaults to a random branch with a cashier)}
    {--customer= : The customer id placing the order (defaults to a random customer with an address)}
    {--items=2 : How many distinct products to add to the order}')]
#[Description('Simulate a customer placing an order and broadcast OrderCreated to the cashier')]
class SimulateOrder extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $branch = $this->resolveBranch();

        if (! $branch) {
            $this->error('No branch with a cashier, available products and an active payment method was found.');

            return self::FAILURE;
        }

        $customer = $this->resolveCustomer();

        if (! $customer) {
            $this->error('No customer with an address was found. Create a customer with at least one address first.');

            return self::FAILURE;
        }

        $address = $customer->addresses()->inRandomOrder()->first();
        $method = $branch->activePaymentMethods()->first();

        $products = $branch->availableProducts()
            ->inRandomOrder()
            ->take(max(1, (int) $this->option('items')))
            ->get();

        if ($products->isEmpty()) {
            $this->error("Branch #{$branch->id} has no available products to order.");

            return self::FAILURE;
        }

        $order = DB::transaction(function () use ($branch, $customer, $address, $method, $products) {
            $items = $products->map(function ($product) {
                $quantity = random_int(1, 3);
                $unitPrice = (float) $product->pivot->price;

                return [
                    'product_id' => $product->id,
                    'product_data' => [
                        'id' => $product->id,
                        'name' => $product->getTranslations('name'),
                    ],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'options_data' => null,
                    'options_amount' => 0,
                    'additions_data' => null,
                    'additions_amount' => 0,
                    'total_price' => $unitPrice * $quantity,
                ];
            });

            $subtotal = $items->sum('total_price');
            $deliveryFee = (float) ($branch->delivery_fee ?? 0);

            $order = Order::create([
                'status' => OrderStatusEnum::PENDING->value,
                'payment_status' => PaymentStatusEnum::WAIT_FOR_CONFIRMATION->value,
                'payment_method_type' => $method->type->value,
                'payment_method_data' => $method->snapshot(),
                'customer_id' => $customer->id,
                'customer_data' => $customer->toArray(),
                'branch_id' => $branch->id,
                'address_id' => $address->id,
                'address_data' => $address->toArray(),
                'total_items_amount' => $subtotal,
                'delivery_amount' => $deliveryFee,
                'total' => $subtotal + $deliveryFee,
                'notes' => 'Simulated order',
            ]);

            $order->items()->createMany($items->all());

            event(new OrderCreated($order));

            return $order;
        });

        $this->info("Simulated order #{$order->id} placed on branch #{$branch->id} for customer #{$customer->id}.");
        $this->table(
            ['Order', 'Branch', 'Cashier', 'Customer', 'Items', 'Total'],
            [[
                $order->id,
                $branch->id,
                $branch->cashier?->id ?? '—',
                $customer->id,
                $products->count(),
                number_format((float) $order->total, 2),
            ]],
        );

        return self::SUCCESS;
    }

    /**
     * Resolve the branch the order will be placed on.
     */
    private function resolveBranch(): ?Branch
    {
        $query = Branch::query()
            ->whereHas('cashier')
            ->whereHas('availableProducts')
            ->whereHas('activePaymentMethods');

        if ($branchId = $this->option('branch')) {
            return $query->whereKey($branchId)->first();
        }

        return $query->inRandomOrder()->first();
    }

    /**
     * Resolve the customer placing the order.
     */
    private function resolveCustomer(): ?Customer
    {
        $query = Customer::query()->whereHas('addresses');

        if ($customerId = $this->option('customer')) {
            return $query->whereKey($customerId)->first();
        }

        return $query->inRandomOrder()->first();
    }
}
