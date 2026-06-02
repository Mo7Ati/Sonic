<?php

namespace Modules\Cashier\Http\Controllers;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Cashier\Http\Requests\Orders\RespondOrderRequest;
use Modules\Cashier\Http\Requests\Orders\UpdateOrderStatusRequest;
use Modules\Cashier\Http\Resources\OrderListResource;
use Modules\Cashier\Http\Resources\OrderResource;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $branchId = Auth::guard('cashier')->user()->branch_id;

        $orders = Order::where('branch_id', $branchId)
            ->applyFilters($request)
            ->paginate($request->input('per_page', 15));

        return successResponse(OrderResource::collection($orders)->response()->getData(true), 'Orders fetched successfully.');
    }

    public function show(Order $order): JsonResponse
    {
        $branchId = Auth::guard('cashier')->user()->branch_id;

        if ($order->branch_id !== $branchId) {
            return errorResponse('Order not found.', 404);
        }

        $order->load(['customer', 'items.product', 'address']);

        return successResponse(OrderResource::make($order)->serializeForShow(), 'Order fetched successfully.');
    }

    /**
     * Cashier's response to a new (pending) order: accept or reject.
     *
     * - accept: confirms the manually-uploaded transfer payment and advances
     *   the order from pending to preparing.
     * - reject: marks the order rejected together with its payment, recording
     *   the cancellation reason.
     */
    public function respond(RespondOrderRequest $request, Order $order): JsonResponse
    {
        $branchId = Auth::guard('cashier')->user()->branch_id;

        if ($order->branch_id !== $branchId) {
            return errorResponse('Order not found.', 404);
        }

        if ($order->status !== OrderStatusEnum::PENDING) {
            return errorResponse('Only pending orders can be accepted or rejected.', 422);
        }

        if ($request->action === 'reject') {
            $order->update([
                'status' => OrderStatusEnum::REJECTED->value,
                'payment_status' => PaymentStatusEnum::REJECTED->value,
                'cancelled_reason' => $request->cancelled_reason,
            ]);

            return successResponse(
                new OrderResource($order->fresh(['customer', 'items.product'])),
                'Order rejected.'
            );
        }

        // accept
        if ($order->payment_status === PaymentStatusEnum::CONFIRMED) {
            return errorResponse('Payment already confirmed.', 422);
        }

        $order->update([
            'payment_status' => PaymentStatusEnum::CONFIRMED->value,
            'status' => OrderStatusEnum::PREPARING->value,
        ]);

        return successResponse(
            new OrderResource($order->fresh(['customer', 'items.product'])),
            'Order accepted.'
        );
    }

    /**
     * Advance an already-accepted order along the fulfilment flow:
     * preparing -> on_the_way -> completed.
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $branchId = Auth::guard('cashier')->user()->branch_id;

        if ($order->branch_id !== $branchId) {
            return errorResponse('Order not found.', 404);
        }

        $order->update(['status' => $request->status]);

        return successResponse(
            new OrderResource($order->fresh(['customer', 'items.product'])),
            'Order status updated.'
        );
    }
}
