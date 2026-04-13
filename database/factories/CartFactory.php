<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Cart;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Cart>
 */
class CartFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'session_id' => null,
            'branch_id' => Branch::factory(),
        ];
    }

    /**
     * Create a guest cart identified by session_id.
     */
    public function guest(): static
    {
        return $this->state(fn () => [
            'customer_id' => null,
            'session_id' => (string) Str::uuid(),
        ]);
    }
}
