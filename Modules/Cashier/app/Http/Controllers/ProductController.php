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
        $branch = Auth::guard('cashier')->user()->branch;

        $products = $branch->products()
            ->applyFilters($request)
            ->with(['category', 'media'])
            ->get();

        return successResponse(BranchProductResource::collection($products));
    }

    public function update(UpdateBranchProductRequest $request, Product $product): JsonResponse
    {
        $branch = Auth::guard('cashier')->user()->branch;

        $exists = $branch->products()->where('products.id', $product->id)->exists();

        if (!$exists) {
            return errorResponse('Product not found in this branch.', 404);
        }

        $branch->products()->updateExistingPivot($product->id, $request->validated());

        $product->load(['category', 'media']);

        return successResponse(new BranchProductResource($product), 'Product updated.');
    }
}
