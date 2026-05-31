<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BranchPaymentMethod;
use Illuminate\Http\JsonResponse;
use Modules\Customer\Http\Resources\PaymentMethodResource;

class PaymentMethodController extends Controller
{
    /**
     * List the active payment methods configured for a branch.
     */
    public function index(int $branchId): JsonResponse
    {
        $methods = BranchPaymentMethod::where('branch_id', $branchId)
            ->active()
            ->get();

        return successResponse(
            PaymentMethodResource::collection($methods),
            __('messages.data_retrieved_successfully'),
        );
    }
}
