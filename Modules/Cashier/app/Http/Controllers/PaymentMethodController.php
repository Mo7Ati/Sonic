<?php

namespace Modules\Cashier\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BranchPaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Cashier\Http\Requests\PaymentMethods\StorePaymentMethodRequest;
use Modules\Cashier\Http\Requests\PaymentMethods\UpdatePaymentMethodRequest;
use Modules\Cashier\Http\Resources\PaymentMethodResource;

class PaymentMethodController extends Controller
{
    public function index(): JsonResponse
    {
        $branchId = Auth::guard('cashier')->user()->branch_id;

        $methods = BranchPaymentMethod::where('branch_id', $branchId)
            ->orderBy('id')
            ->get();

        return successResponse(PaymentMethodResource::collection($methods));
    }

    public function store(StorePaymentMethodRequest $request): JsonResponse
    {
        $branchId = Auth::guard('cashier')->user()->branch_id;

        $method = BranchPaymentMethod::create([
            ...$request->validated(),
            'branch_id' => $branchId,
        ]);

        return successResponse(
            new PaymentMethodResource($method),
            'Payment method created.',
            201,
        );
    }

    public function update(UpdatePaymentMethodRequest $request, BranchPaymentMethod $paymentMethod): JsonResponse
    {
        if (! $this->ownsMethod($paymentMethod)) {
            return errorResponse('Payment method not found.', 404);
        }

        $paymentMethod->update($request->validated());

        return successResponse(
            new PaymentMethodResource($paymentMethod->fresh()),
            'Payment method updated.',
        );
    }

    public function destroy(BranchPaymentMethod $paymentMethod): JsonResponse
    {
        if (! $this->ownsMethod($paymentMethod)) {
            return errorResponse('Payment method not found.', 404);
        }

        $paymentMethod->delete();

        return successResponse(null, 'Payment method deleted.');
    }

    private function ownsMethod(BranchPaymentMethod $paymentMethod): bool
    {
        return $paymentMethod->branch_id === Auth::guard('cashier')->user()->branch_id;
    }
}
