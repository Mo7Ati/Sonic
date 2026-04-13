<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Customer\Http\Requests\Cart\AddCartItemRequest;
use Modules\Customer\Http\Requests\Cart\UpdateCartItemRequest;
use Modules\Customer\Http\Resources\CartResource;

class CartController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $cart = Cart::resolveFor($request);

        if (! $cart) {
            return successResponse(null, __('messages.data_retrieved_successfully'));
        }

        $cart->load(['items.product', 'branch.store']);

        return successResponse(
            CartResource::make($cart),
            __('messages.data_retrieved_successfully'),
        );
    }

    public function addItem(AddCartItemRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $cart = Cart::resolveFor($request);

        if ($cart && $cart->branch_id !== $validated['branch_id']) {
            if (empty($validated['force_replace'])) {
                return errorResponse('branch_conflict', 409);
            }

            $cart->items()->delete();
            $cart->update(['branch_id' => $validated['branch_id']]);
        }

        if (! $cart) {
            $cart = Cart::resolveOrCreateFor($request, $validated['branch_id']);
        }

        $optionsAmount = collect($validated['options'] ?? [])->sum('price');
        $additionsAmount = collect($validated['additions'] ?? [])->sum('price');
        $unitPrice = $validated['unit_price'];
        $quantity = $validated['quantity'];
        $totalPrice = ($unitPrice + $optionsAmount + $additionsAmount) * $quantity;

        $cart->items()->create([
            'product_id' => $validated['product_id'],
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'options_data' => $validated['options'] ?? null,
            'options_amount' => $optionsAmount,
            'additions_data' => $validated['additions'] ?? null,
            'additions_amount' => $additionsAmount,
            'total_price' => $totalPrice,
        ]);

        $cart->load(['items.product', 'branch.store']);

        return successResponse(
            CartResource::make($cart),
            __('messages.item_added_successfully'),
        );
    }

    public function updateItem(UpdateCartItemRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();

        $cartItem = $this->findCartItem($request, $id);

        $cartItem->quantity = $validated['quantity'];
        $cartItem->recalculateTotal()->save();

        $cart = $cartItem->cart->load(['items.product', 'branch.store']);

        return successResponse(
            CartResource::make($cart),
            __('messages.item_updated_successfully'),
        );
    }

    public function removeItem(Request $request, int $id): JsonResponse
    {
        $cartItem = $this->findCartItem($request, $id);
        $cart = $cartItem->cart;

        $cartItem->delete();

        if ($cart->items()->count() === 0) {
            $cart->delete();

            return successResponse(null, __('messages.cart_cleared'));
        }

        $cart->load(['items.product', 'branch.store']);

        return successResponse(
            CartResource::make($cart),
            __('messages.item_removed_successfully'),
        );
    }

    public function clear(Request $request): JsonResponse
    {
        $cart = Cart::resolveFor($request);

        if ($cart) {
            $cart->items()->delete();
            $cart->delete();
        }

        return successResponse(null, __('messages.cart_cleared'));
    }

    /**
     * Find a cart item owned by the current user/session.
     */
    private function findCartItem(Request $request, int $id): CartItem
    {
        $query = CartItem::query();

        if ($request->user()) {
            $query->whereHas('cart', fn ($q) => $q->where('customer_id', $request->user()->id));
        } else {
            $sessionId = $request->header('X-Session-Id');
            $query->whereHas('cart', fn ($q) => $q->where('session_id', $sessionId));
        }

        return $query->findOrFail($id);
    }
}
