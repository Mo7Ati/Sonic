<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use NotificationChannels\Expo\ExpoPushToken;

#[Fillable(['name', 'phone_number', 'is_active', 'last_seen_at'])]
#[Hidden(['remember_token'])]

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class);
    }

    /**
     * Route notifications for the Expo channel.
     *
     * Malformed tokens are skipped so one bad row never breaks delivery to a
     * customer's other devices.
     *
     * @return array<int, ExpoPushToken>
     */
    public function routeNotificationForExpo(): array
    {
        return $this->deviceTokens->reduce(function (array $tokens, DeviceToken $device): array {
            try {
                $tokens[] = ExpoPushToken::make($device->expo_token);
            } catch (\InvalidArgumentException) {
                // Skip a malformed token rather than failing the whole send.
            }

            return $tokens;
        }, []);
    }
}
