<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Customer;
use App\Services\PhoneVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Customer\Http\Requests\AuthRequest;
use Modules\Customer\Http\Requests\UpdateProfileRequest;
use Modules\Customer\Http\Resources\CustomerResource;

class AuthController extends Controller
{
    public function __construct(
        private readonly PhoneVerificationService $phoneVerificationService,
    ) {}

    public function sendOtp(AuthRequest $request): JsonResponse
    {
        $validated = $request->validate($request->sendOtpRules());

        $result = $this->phoneVerificationService->sendOtp($validated);

        return successResponse($result, __('auth.phone_verification.sent'));
    }

    public function verifyOtp(AuthRequest $request): JsonResponse
    {
        $validated = $request->validate($request->verifyOtpRules());

        $customer = $this->phoneVerificationService->verifyOtp($validated['phone_number'], $validated['otp']);

        $this->syncGuestData($request, $customer);

        $token = $customer->createToken('customer-token')->plainTextToken;

        return successResponse([
            'customer' => CustomerResource::make($customer),
            'token' => $token,
        ], __('auth.phone_verification.verified'), $customer->wasRecentlyCreated ? 201 : 200);
    }

    public function resendOtp(AuthRequest $request): JsonResponse
    {
        $validated = $request->validate($request->sendOtpRules());

        $result = $this->phoneVerificationService->resendOtp($validated['phone_number']);

        return successResponse($result, __('auth.phone_verification.resent'));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return successResponse(null, 'Logged out successfully.');
    }

    public function user(Request $request): JsonResponse
    {
        return successResponse(CustomerResource::make($request->user()));
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user();
        $customer->update($request->validated());

        return successResponse(
            CustomerResource::make($customer->fresh()),
            __('messages.data_saved_successfully'),
        );
    }

    private function syncGuestData(Request $request, Customer $customer): void
    {
        $sessionId = $request->header('X-Session-Id');
        if ($sessionId) {
            Cart::mergeGuestCart($sessionId, $customer->id);
            Address::mergeGuestAddresses($sessionId, $customer->id);
        }
    }
}
