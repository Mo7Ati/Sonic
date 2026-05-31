<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Customer\Http\Requests\Cart\AddCartItemRequest;
use Modules\Customer\Http\Requests\Cart\UpdateCartItemRequest;

class CartService
{
    public function addItem(AddCartItemRequest $request): Cart
    {
        $validated = $request->validated();
        $cart = Cart::resolveFor($request);

        if ($cart && $cart->branch_id != $validated['branch_id']) {
            if (empty($validated['force_replace'])) {
                return throw ValidationException::withMessages([
                    'branch_id' => __('messages.cart_branch_conflict'),
                ]);
            }

            $cart->items()->delete();
            $cart->update(['branch_id' => $validated['branch_id']]);
        }

        $cart ??= Cart::resolveOrCreateFor($request, $validated['branch_id']);

        $cart->items()->create($this->buildCartItemAttributes($validated));

        $cart->load(['items.product', 'branch.store']);

        return $cart;
    }

    /**
     * Build the trustworthy cart-item attributes for a validated add-to-cart payload.
     *
     * Prices are never taken from the client: the unit price comes from the
     * product's branch pivot (falling back to the base price) and every option /
     * addition price is resolved from its pivot. Options or additions that are not
     * attached to the product (or unavailable) are rejected.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function buildCartItemAttributes(array $payload): array
    {
        $product = Branch::findOrFail($payload['branch_id'])
            ->products()
            ->where('products.id', $payload['product_id'])
            ->firstOrFail();

        $unitPrice = (float) ($product->pivot->price ?? $product->price);
        [$optionsData, $optionsAmount] = $this->optionsPricing($product, $payload['options'] ?? []);
        [$additionsData, $additionsAmount] = $this->additionsPricing($product, $payload['additions'] ?? []);
        $quantity = $payload['quantity'];

        return [
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'options_data' => $optionsData,
            'options_amount' => $optionsAmount,
            'additions_data' => $additionsData,
            'additions_amount' => $additionsAmount,
            'total_price' => ($unitPrice + $optionsAmount + $additionsAmount) * $quantity,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $optionIds
     * @return array{0: array<int, array<string, mixed>>|null, 1: float}
     */
    private function optionsPricing(Product $product, array $optionIds): array
    {
        if (empty($optionIds)) {
            return [null, 0.0];
        }

        $options = $product->options()
            ->whereIn('options.id', $optionIds)
            ->wherePivot('is_available', true)
            ->get();


        return [
            $options->map(fn($option) => [
                'id' => $option->id,
                'group_id' => $option->option_group_id,
                'name' => $option->name,
                'price' => $option->pivot->price,
            ]),
            $options->sum('pivot.price'),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $additionIds
     * @return array{0: array<int, array<string, mixed>>|null, 1: float}
     */
    private function additionsPricing(Product $product, array $additionIds): array
    {
        if (empty($additionIds)) {
            return [null, 0.0];
        }

        $additions = $product->additions()
            ->whereIn('additions.id', $additionIds)
            ->get();

        return [
            $additions->map(fn($addition) => [
                'id' => $addition->id,
                'name' => $addition->name,
                'price' => $addition->pivot->price,
            ]),
            $additions->sum('pivot.price'),
        ];
    }

    /**
     * Update a cart item.
     */
    public function updateItem(UpdateCartItemRequest $request, int $id): Cart
    {
        $validated = $request->validated();
        $cartItem = $this->findCartItem($request, $id);

        $cartItem->quantity = $validated['quantity'];
        $cartItem->recalculateTotal()->save();

        return $cartItem->cart->load(['items.product', 'branch.store']);
    }

    /**
     * Remove a cart item.
     */
    public function removeItem(Request $request, int $id): ?Cart
    {
        $cartItem = $this->findCartItem($request, $id);
        $cart = $cartItem->cart;

        $cartItem->delete();

        if ($cart->items()->count() === 0) {
            $cart->delete();

            return null;
        }

        return $cart->load(['items.product', 'branch.store']);
    }

    /**
     * Clear a cart.
     */
    public function clearCart(Request $request): void
    {
        $cart = Cart::resolveFor($request);

        if ($cart) {
            $cart->items()->delete();
            $cart->delete();
        }
    }

    /**
     * Find a cart item owned by the current user/session.
     */
    private function findCartItem(Request $request, int $id): CartItem
    {
        $query = CartItem::query();

        if ($request->user()) {
            $query->whereHas('cart', fn($q) => $q->where('customer_id', $request->user()->id));
        } else {
            $sessionId = $request->header('X-Session-Id');
            $query->whereHas('cart', fn($q) => $q->where('session_id', $sessionId));
        }

        return $query->findOrFail($id);
    }
}
