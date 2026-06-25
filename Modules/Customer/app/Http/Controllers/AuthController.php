<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Customer;
use App\Services\PhoneVerificationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Customer\Http\Requests\AuthRequest;
use Modules\Customer\Http\Requests\ChangePhoneRequest;
use Modules\Customer\Http\Requests\UpdateProfileRequest;
use Modules\Customer\Http\Resources\CustomerResource;

class AuthController extends Controller
{
    public function __construct(
        private readonly PhoneVerificationService $phoneVerificationService,
    ) {
    }

    public function login(AuthRequest $request): JsonResponse
    {
        $validated = $request->validate($request->loginRules());

        $otp = $this->phoneVerificationService->sendOtp($validated['phone_number']);

        return successResponse([
            'otp' => $otp,
        ], __('auth.phone_verification.sent'));
    }

    public function verifyOtp(AuthRequest $request): JsonResponse
    {
        $validated = $request->validate($request->verifyOtpRules());

        $phoneNumber = $validated['phone_number'];
        $otp = $validated['otp'];

        $this->phoneVerificationService->verifyOtp($phoneNumber, $otp);

        $customer = Customer::query()->firstOrCreate(
            ['phone_number' => $phoneNumber],
            ['is_active' => true],
        );

        if ($customer->wasRecentlyCreated) {
            event(new Registered($customer));
        }

        $customer->update(['last_seen_at' => now()]);

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

        $this->phoneVerificationService->resendOtp($validated['phone_number']);

        return successResponse(null, __('auth.phone_verification.resent'));
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
    private function syncGuestData(Request $request, Customer $customer): void
    {
        $sessionId = $request->header('X-Session-Id');
        if ($sessionId) {
            Cart::mergeGuestCart($sessionId, $customer->id);
            Address::mergeGuestAddresses($sessionId, $customer->id);
        }
    }
}
