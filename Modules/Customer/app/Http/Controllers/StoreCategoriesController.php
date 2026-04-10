<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\StoreCategory;
use Illuminate\Http\Request;
use Modules\Customer\Http\Resources\StoreCategoryResource;

class StoreCategoriesController extends Controller
{
    public function show(Request $request, $category_id)
    {
        $category = StoreCategory::with('children')->findOrFail($category_id);

        return successResponse(
            StoreCategoryResource::make($category),
            __('messages.data_retrieved_successfully')
        );
    }
}
