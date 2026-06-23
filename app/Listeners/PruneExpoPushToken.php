<?php

namespace App\Listeners;

use App\Models\DeviceToken;
use Illuminate\Notifications\Events\NotificationFailed;
use NotificationChannels\Expo\ExpoChannel;
use NotificationChannels\Expo\ExpoError;

class PruneExpoPushToken
{
    /**
     * Remove tokens Expo reports as unreachable so we stop sending to them.
     */
    public function handle(NotificationFailed $event): void
    {
        if ($event->channel !== ExpoChannel::NAME) {
            return;
        }

        $error = $event->data;

        if ($error instanceof ExpoError && $error->type->isDeviceNotRegistered()) {
            DeviceToken::query()->where('expo_token', (string) $error->token)->delete();
        }
    }
}
