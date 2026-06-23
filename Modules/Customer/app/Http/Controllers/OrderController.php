<?php

namespace Modules\Customer\Http\Controllers;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Events\OrderCreated;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\BranchPaymentMethod;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Http\Requests\Orders\PlaceOrderRequest;
use Modules\Customer\Http\Resources\OrderResource;

class OrderController extends Controller
{
    /**
     * Paginated list of the authenticated customer's orders, newest first.
     */
    public function index(Request $request): JsonResponse
    {
        $customer = $request->user('sanctum');

        $orders = Order::query()
            ->where('customer_id', $customer->id)
            ->with(['branch.store', 'items'])
            ->withCount('items')
            ->when(
                $request->input('filter') === 'active',
                fn($query) => $query->whereNotIn('status', [
                    OrderStatusEnum::COMPLETED->value,
                    OrderStatusEnum::CANCELLED->value,
                    OrderStatusEnum::REJECTED->value,
                ]),
            )
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 15));

        return successResponse(
            OrderResource::collection($orders)->response()->getData(true),
            __('messages.data_retrieved_successfully'),
        );
    }

    /**
     * Single order for the authenticated customer.
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        if ($order->customer_id !== $request->user()->id) {
            return errorResponse(__('messages.order_not_found'), 404);
        }

        $order->load(['branch.store', 'items']);

        return successResponse(
            OrderResource::make($order),
            __('messages.data_retrieved_successfully'),
        );
    }

    /**
     * Place an order from the customer's cart.
     *
     * The proof of the (manual) bank/wallet transfer is uploaded in the same
     * request, so the order only reaches the branch once payment proof exists.
     * It is created as pending/unpaid until branch staff confirm the payment.
     */
    public function store(PlaceOrderRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $customer = $request->user('sanctum');

        $cart = Cart::where('customer_id', $customer->id)->first();

        if (!$cart || $cart->items()->count() === 0) {
            return errorResponse(__('messages.cart_is_empty'), 422);
        }

        $cart->load(['items.product', 'branch']);

        $address = Address::where('customer_id', $customer->id)
            ->find($validated['address_id']);

        if (!$address) {
            return errorResponse(__('messages.address_not_found'), 404);
        }

        $method = BranchPaymentMethod::where('branch_id', $cart->branch_id)
            ->where('type', $validated['payment_method_type'])
            ->active()
            ->first();

        if (!$method) {
            return errorResponse(__('messages.payment_method_unavailable'), 422);
        }

        $subtotal = $cart->subtotal;
        $deliveryFee = (float) ($cart->branch->delivery_fee ?? 0);

        $order = DB::transaction(function () use ($cart, $customer, $address, $method, $subtotal, $deliveryFee, $validated) {
            $order = Order::create([
                'status' => OrderStatusEnum::PENDING->value,
                'payment_status' => PaymentStatusEnum::WAIT_FOR_CONFIRMATION->value,
                'payment_method_type' => $method->type->value,
                'payment_method_data' => $method->snapshot(),
                'customer_id' => $customer->id,
                'customer_data' => $customer->toArray(),
                'branch_id' => $cart->branch_id,
                'address_id' => $address->id,
                'address_data' => $address->toArray(),
                'total_items_amount' => $subtotal,
                'delivery_amount' => $deliveryFee,
                'total' => $subtotal + $deliveryFee,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_data' => [
                        'id' => $item->product_id,
                        'name' => $item->product->getTranslations('name'),
                    ],
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'options_data' => $item->options_data,
                    'options_amount' => $item->options_amount,
                    'additions_data' => $item->additions_data,
                    'additions_amount' => $item->additions_amount,
                    'total_price' => $item->total_price,
                ]);
            }

            $order->addMediaFromRequest('proof')->toMediaCollection(Order::PAYMENT_PROOF_COLLECTION);

            $cart->items()->delete();
            $cart->delete();

            event(new OrderCreated($order));

            return $order;
        });

        return successResponse(
            OrderResource::make($order->load(['items', 'branch.store'])),
            __('messages.order_placed_successfully'),
            201,
        );
    }
}
