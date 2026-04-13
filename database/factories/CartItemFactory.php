<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CartItem>
 */
class CartItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $unitPrice = fake()->randomFloat(2, 5, 100);
        $quantity = fake()->numberBetween(1, 5);

        return [
            'cart_id' => Cart::factory(),
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'options_data' => null,
            'options_amount' => 0,
            'additions_data' => null,
            'additions_amount' => 0,
            'total_price' => $unitPrice * $quantity,
        ];
    }

    /**
     * Create an item with custom options.
     *
     * @param  array<int, array{group_id: int, group_name: string, item_id: int, item_name: string, price: float}>  $options
     */
    public function withOptions(array $options): static
    {
        $optionsAmount = collect($options)->sum('price');

        return $this->state(fn (array $attributes) => [
            'options_data' => $options,
            'options_amount' => $optionsAmount,
            'total_price' => ($attributes['unit_price'] + $optionsAmount + $attributes['additions_amount']) * $attributes['quantity'],
        ]);
    }

    /**
     * Create an item with custom additions.
     *
     * @param  array<int, array{id: int, name: string, price: float}>  $additions
     */
    public function withAdditions(array $additions): static
    {
        $additionsAmount = collect($additions)->sum('price');

        return $this->state(fn (array $attributes) => [
            'additions_data' => $additions,
            'additions_amount' => $additionsAmount,
            'total_price' => ($attributes['unit_price'] + $attributes['options_amount'] + $additionsAmount) * $attributes['quantity'],
        ]);
    }
}
