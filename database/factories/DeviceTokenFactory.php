<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\DeviceToken;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeviceToken>
 */
class DeviceTokenFactory extends Factory
{
    protected $model = DeviceToken::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'expo_token' => 'ExponentPushToken['.$this->faker->unique()->lexify('??????????????????????').']',
            'platform' => $this->faker->randomElement(['ios', 'android']),
            'last_used_at' => now(),
        ];
    }
}
