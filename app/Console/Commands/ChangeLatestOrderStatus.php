<?php

namespace App\Console\Commands;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('order:change-status
    {--status= : The new status to apply (one of: pending, preparing, on_the_way, completed, cancelled, rejected)}')]
#[Description('Change the latest order to the given status')]
class ChangeLatestOrderStatus extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $status = $this->resolveStatus();

        if (! $status instanceof OrderStatusEnum) {
            return self::FAILURE;
        }

        $order = Order::query()->latest('id')->first();

        if (! $order) {
            $this->error('No orders found.');

            return self::FAILURE;
        }

        $previous = $order->status;

        $order->update(['status' => $status->value]);

        $this->info("Order #{$order->id} status changed from {$previous->value} to {$status->value}.");

        return self::SUCCESS;
    }

    /**
     * Resolve and validate the requested status from the option.
     */
    private function resolveStatus(): ?OrderStatusEnum
    {
        $status = $this->option('status')
            ?? $this->choice('Select the new status', OrderStatusEnum::values());

        $resolved = OrderStatusEnum::tryFrom($status);

        if (! $resolved instanceof OrderStatusEnum) {
            $this->error("Invalid status \"{$status}\". Valid statuses: ".implode(', ', OrderStatusEnum::values()).'.');

            return null;
        }

        return $resolved;
    }
}
