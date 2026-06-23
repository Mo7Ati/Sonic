<?php

namespace App\Notifications;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Expo\ExpoChannel;
use NotificationChannels\Expo\ExpoMessage;

class OrderStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The status is captured at dispatch time so a queued job reflects the
     * transition that triggered it, even if the order advances again before
     * the job runs.
     */
    public function __construct(public Order $order, public OrderStatusEnum $status) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', ExpoChannel::class];
    }

    public function toExpo(object $notifiable): ExpoMessage
    {
        return ExpoMessage::create($this->title(), $this->body())
            ->data($this->payload())
            ->channelId('default')
            ->playSound()
            ->high();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            ...$this->payload(),
            'title' => $this->title(),
            'body' => $this->body(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return [
            'type' => 'order_status',
            'order_id' => $this->order->id,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
        ];
    }

    private function title(): string
    {
        return __('notifications.order_status.'.$this->status->value.'.title');
    }

    private function body(): string
    {
        return __('notifications.order_status.'.$this->status->value.'.body', ['id' => $this->order->id]);
    }
}
