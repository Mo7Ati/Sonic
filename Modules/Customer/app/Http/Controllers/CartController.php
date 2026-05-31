<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Customer\Http\Requests\Cart\AddCartItemRequest;
use Modules\Customer\Http\Requests\Cart\UpdateCartItemRequest;
use Modules\Customer\Http\Resources\CartResource;

class CartController extends Controller
{
    public function __construct(private CartService $cartService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $cart = Cart::resolveFor($request);

        if (!$cart) {
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
        $cart = $this->cartService->addItem($request);

        return successResponse(
            CartResource::make($cart),
            __('messages.item_added_successfully'),
        );
    }

    public function updateItem(UpdateCartItemRequest $request, int $id): JsonResponse
    {
        $cart = $this->cartService->updateItem($request, $id);

        return successResponse(
            CartResource::make($cart),
            __('messages.item_updated_successfully'),
        );
    }

    public function removeItem(Request $request, int $id): JsonResponse
    {
        $cart = $this->cartService->removeItem($request, $id);

        if ($cart === null) {
            return successResponse(null, __('messages.cart_cleared'));
        }

        return successResponse(
            CartResource::make($cart),
            __('messages.item_removed_successfully'),
        );
    }

    public function clear(Request $request): JsonResponse
    {
        $this->cartService->clearCart($request);

        return successResponse(null, __('messages.cart_cleared'));
    }
}
