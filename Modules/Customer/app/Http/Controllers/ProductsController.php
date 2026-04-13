<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Modules\Customer\Http\Resources\ProductResource;

class ProductsController extends Controller
{
    public function show(int $id)
    {
        $product = Product::query()
            ->where('is_active', true)
            ->where('is_accepted', true)
            ->with([
                'category',
                'options.optionGroup',
                'additions',
            ])
            ->findOrFail($id);

        return successResponse(
            ProductResource::make($product),
            __('messages.data_retrieved_successfully'),
        );
    }
}
