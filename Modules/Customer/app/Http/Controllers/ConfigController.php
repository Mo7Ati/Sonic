<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Settings\CustomPages;
use App\Settings\OnboardingSettings;
use Illuminate\Http\JsonResponse;
use Modules\Customer\Http\Resources\CustomPagesResource;
use Modules\Customer\Http\Resources\OnboardingResource;

class ConfigController extends Controller
{
    /**
     * Public, app-wide static content (onboarding slides + custom pages).
     *
     * Split out of the splash aggregate so the mobile client can cache it
     * aggressively (long TTL) and refetch it independently of auth state.
     */
    public function index(): JsonResponse
    {
        return successResponse(
            [
                'onboardingSlides' => OnboardingResource::collection(app(OnboardingSettings::class)->steps),
                'customPages' => CustomPagesResource::collection(app(CustomPages::class)->pages ?? []),
            ],
            __('messages.data_retrieved_successfully'),
        );
    }
}
