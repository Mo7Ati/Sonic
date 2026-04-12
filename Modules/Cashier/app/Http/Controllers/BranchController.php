<?php

namespace Modules\Cashier\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Cashier\Http\Requests\Branch\UpdateBranchStatusRequest;
use Modules\Cashier\Http\Resources\BranchResource;

class BranchController extends Controller
{
    public function show(): JsonResponse
    {
        $branch = Auth::guard('cashier')->user()->branch;

        return successResponse(new BranchResource($branch));
    }

    public function updateStatus(UpdateBranchStatusRequest $request): JsonResponse
    {
        $branch = Auth::guard('cashier')->user()->branch;
        $branch->update(['status' => $request->status]);

        return successResponse(new BranchResource($branch->fresh()), 'Branch status updated.');
    }
}
