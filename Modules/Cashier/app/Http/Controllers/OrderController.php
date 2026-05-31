<?php

namespace Modules\Cashier\Http\Controllers;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Cashier\Http\Requests\Orders\UpdateOrderStatusRequest;
use Modules\Cashier\Http\Resources\OrderResource;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $branchId = Auth::guard('cashier')->user()->branch_id;

        $orders = Order::where('branch_id', $branchId)
            ->with(['customer', 'items.product'])
            ->applyFilters($request)
            ->paginate($request->input('per_page', 15));

        return successResponse(OrderResource::collection($orders)->response()->getData(true));
    }

    public function show(Order $order): JsonResponse
    {
        $branchId = Auth::guard('cashier')->user()->branch_id;

        if ($order->branch_id !== $branchId) {
            return errorResponse('Order not found.', 404);
        }

        $order->load(['customer', 'items.product', 'address']);

        return successResponse(new OrderResource($order));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $branchId = Auth::guard('cashier')->user()->branch_id;

        if ($order->branch_id !== $branchId) {
            return errorResponse('Order not found.', 404);
        }

        $data = ['status' => $request->status];

        if ($request->status === OrderStatusEnum::REJECTED->value) {
            $data['cancelled_reason'] = $request->cancelled_reason;
        }

        $order->update($data);

        return successResponse(
            new OrderResource($order->fresh(['customer', 'items.product'])),
            'Order status updated.'
        );
    }

    /**
     * Confirm the customer's (manually uploaded) transfer payment.
     * Marks the order paid and advances it from pending to preparing.
     */
    public function confirmPayment(Order $order): JsonResponse
    {
        $branchId = Auth::guard('cashier')->user()->branch_id;

        if ($order->branch_id !== $branchId) {
            return errorResponse('Order not found.', 404);
        }

        if ($order->status !== OrderStatusEnum::PENDING) {
            return errorResponse('Only pending orders can be confirmed.', 422);
        }

        if ($order->payment_status === PaymentStatusEnum::PAID) {
            return errorResponse('Payment already confirmed.', 422);
        }

        if (! $order->getFirstMedia(Order::PAYMENT_PROOF_COLLECTION)) {
            return errorResponse('No payment proof was uploaded for this order.', 422);
        }

        $order->update([
            'payment_status' => PaymentStatusEnum::PAID->value,
            'status' => OrderStatusEnum::PREPARING->value,
        ]);

        return successResponse(
            new OrderResource($order->fresh(['customer', 'items.product'])),
            'Payment confirmed.'
        );
    }
}
