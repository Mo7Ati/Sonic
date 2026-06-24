<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Customer;
use App\Services\PhoneVerificationService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Modules\Customer\Http\Requests\Auth\ForgotPasswordRequest;
use Modules\Customer\Http\Requests\Auth\LoginRequest;
use Modules\Customer\Http\Requests\Auth\RegisterRequest;
use Modules\Customer\Http\Requests\Auth\ResendRegistrationOtpRequest;
use Modules\Customer\Http\Requests\Auth\ResetPasswordRequest;
use Modules\Customer\Http\Requests\Auth\VerifyRegistrationOtpRequest;

class AuthController extends Controller
{
    public function __construct(
        private readonly PhoneVerificationService $phoneVerificationService,
    ) {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->phoneVerificationService->startRegistration($request->validated());

        return successResponse($result, __('auth.phone_verification.sent'));
    }

    public function verifyRegistrationOtp(VerifyRegistrationOtpRequest $request): JsonResponse
    {
        $customer = $this->phoneVerificationService->verifyRegistrationOtp(
            $request->validated('phone_number'),
            $request->validated('code'),
        );

        $this->syncGuestData($request, $customer);

        $token = $customer->createToken('customer-token')->plainTextToken;

        return successResponse([
            'customer' => $customer,
            'token' => $token,
        ], __('auth.phone_verification.verified'), 201);
    }

    public function resendRegistrationOtp(ResendRegistrationOtpRequest $request): JsonResponse
    {
        $result = $this->phoneVerificationService->resendOtp($request->validated('phone_number'));

        return successResponse($result, __('auth.phone_verification.resent'));
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $phone = $request->validated('phone_number');
        $customer = Customer::where('phone_number', $phone)->first();

        if (!$customer) {
            return errorResponse('The provided credentials are incorrect.', 401);
        }

        // if (!$customer->is_active) {
        //     return errorResponse('Your account has been deactivated.', 403);
        // }

        $customer->update(['last_seen_at' => now()]);

        $this->syncGuestData($request, $customer);

        $token = $customer->createToken('customer-token')->plainTextToken;

        return successResponse([
            'customer' => $customer,
            'token' => $token,
        ], 'Login successful.');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return successResponse(null, 'Logged out successfully.');
    }

    public function user(Request $request): JsonResponse
    {
        return successResponse($request->user());
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
