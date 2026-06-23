<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use NotificationChannels\Expo\ExpoPushToken;

#[Fillable(['name', 'email', 'password', 'phone_number', 'is_active', 'last_seen_at', 'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]

class Customer extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_seen_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
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

    public function lastUsedAddress()
    {
        return $this->belongsTo(Address::class, 'last_used_address_id');
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
