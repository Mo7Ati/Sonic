<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Customer\Http\Requests\DeviceTokens\StoreDeviceTokenRequest;

class DeviceTokenController extends Controller
{
    /**
     * Register (or refresh) the current device's Expo push token.
     *
     * Keyed on the token itself so a device that changes hands is re-pointed
     * to the customer who is currently signed in.
     */
    public function store(StoreDeviceTokenRequest $request): JsonResponse
    {
        $validated = $request->validated();

        DeviceToken::updateOrCreate(
            ['expo_token' => $validated['expo_token']],
            [
                'customer_id' => $request->user()->id,
                'platform' => $validated['platform'] ?? null,
                'last_used_at' => now(),
            ],
        );

        return successResponse(null, __('messages.data_saved_successfully'));
    }

    /**
     * Remove the current device's token (called on logout).
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'expo_token' => ['required', 'string'],
        ]);

        $request->user()
            ->deviceTokens()
            ->where('expo_token', $request->input('expo_token'))
            ->delete();

        return successResponse(null, __('messages.data_deleted_successfully'));
    }
}
