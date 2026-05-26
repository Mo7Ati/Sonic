<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Support\Facades\Log;
use Modules\Customer\Http\Resources\SectionResource;

class HomeController extends Controller
{
    public function index()
    {
       

        $homePageSections = Section::active()->ordered()->get();

        return successResponse(
            SectionResource::collection($homePageSections),
            __('messages.data_retrieved_successfully')
        );
    }
}
