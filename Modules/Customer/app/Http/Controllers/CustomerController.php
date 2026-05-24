<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Customer\Http\Resources\SplashResource;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return successResponse(
            SplashResource::make(request()),
            __('messages.data_retrieved_successfully')
        );
    }
}
