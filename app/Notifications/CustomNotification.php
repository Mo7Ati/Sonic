<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Expo\ExpoChannel;
use NotificationChannels\Expo\ExpoMessage;

/**
 * Free-form notification used for admin broadcasts (app updates, announcements).
 *
 * @param  array<string, mixed>  $data  Optional extra payload merged into the push/database body.
 */
class CustomNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $body,
        public array $data = [],
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', ExpoChannel::class];
    }

    public function toExpo(object $notifiable): ExpoMessage
    {
        return ExpoMessage::create($this->title, $this->body)
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
            'title' => $this->title,
            'body' => $this->body,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return [
            'type' => 'custom',
            ...$this->data,
        ];
    }
}
