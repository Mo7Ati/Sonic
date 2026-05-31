<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Modules\Customer\Http\Resources\BranchResource;
use Modules\Customer\Http\Resources\PaymentMethodResource;

class BranchesController extends Controller
{
    public function index(Request $request)
    {
        $branches = Branch::query()
            ->with('store')
            ->filters()
            ->paginate(15);

        return successResponse(
            [
                'data' => BranchResource::collection($branches),
                'current_page' => $branches->currentPage(),
                'last_page' => $branches->lastPage(),
            ],
            __('messages.data_retrieved_successfully'),
        );
    }

    public function show($branch_id)
    {
        $branch = Branch::query()
            ->with([
                'store.categories',
                'availableProducts.category',
            ])
            ->findOrFail($branch_id);

        return successResponse(
            BranchResource::make($branch),
            __('messages.data_retrieved_successfully'),
        );
    }

    public function getPaymentMethods($branchId)
    {
        $methods = Branch::findOrFail($branchId)->activePaymentMethods;

        return successResponse(
            PaymentMethodResource::collection($methods),
            __('messages.data_retrieved_successfully'),
        );
    }
}
