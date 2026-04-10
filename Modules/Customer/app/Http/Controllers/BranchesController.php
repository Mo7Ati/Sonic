<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Modules\Customer\Http\Resources\BranchResource;

class BranchesController extends Controller
{
    public function index(Request $request)
    {
        $branches = Branch::query()
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
}
