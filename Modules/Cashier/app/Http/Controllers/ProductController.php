<?php

namespace Modules\Cashier\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Cashier\Http\Requests\Products\UpdateBranchProductRequest;
use Modules\Cashier\Http\Resources\BranchProductResource;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $branchId = Auth::guard('cashier')->user()->branch_id;

        $products = Product::whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))
            ->with(['category', 'branches' => fn ($q) => $q->where('branches.id', $branchId), 'media'])
            ->when($request->input('search'), fn ($q, $search) => $q->search($search))
            ->when($request->input('category_id'), fn ($q, $catId) => $q->where('category_id', $catId))
            ->when($request->has('is_available'), function ($q) use ($request, $branchId) {
                $q->whereHas('branches', fn ($bq) => $bq
                    ->where('branches.id', $branchId)
                    ->wherePivot('is_available', $request->boolean('is_available'))
                );
            })
            ->orderBy($request->input('sort', 'id'), $request->input('direction', 'desc'))
            ->paginate($request->input('per_page', 20));

        return successResponse(BranchProductResource::collection($products)->response()->getData(true));
    }

    public function update(UpdateBranchProductRequest $request, Product $product): JsonResponse
    {
        $branchId = Auth::guard('cashier')->user()->branch_id;

        $exists = $product->branches()->where('branches.id', $branchId)->exists();

        if (! $exists) {
            return errorResponse('Product not found in this branch.', 404);
        }

        $product->branches()->updateExistingPivot($branchId, $request->validated());

        $product->load(['category', 'branches' => fn ($q) => $q->where('branches.id', $branchId), 'media']);

        return successResponse(new BranchProductResource($product), 'Product updated.');
    }
}
