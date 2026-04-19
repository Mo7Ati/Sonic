<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Customer;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Modules\Customer\Http\Requests\Auth\ForgotPasswordRequest;
use Modules\Customer\Http\Requests\Auth\LoginRequest;
use Modules\Customer\Http\Requests\Auth\RegisterRequest;
use Modules\Customer\Http\Requests\Auth\ResetPasswordRequest;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => $request->password,
        ]);

        event(new Registered($customer));

        $sessionId = $request->header('X-Session-Id');
        if ($sessionId) {
            Cart::mergeGuestCart($sessionId, $customer->id);
            Address::mergeGuestAddresses($sessionId, $customer->id);
        }

        $token = $customer->createToken('customer-token')->plainTextToken;

        return successResponse([
            'customer' => $customer,
            'token' => $token,
        ], 'Registration successful.', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $customer = Customer::where('email', $request->email)->first();

        if (! $customer || ! Hash::check($request->password, $customer->password)) {
            return errorResponse('The provided credentials are incorrect.', 401);
        }

        if (! $customer->is_active) {
            return errorResponse('Your account has been deactivated.', 403);
        }

        $customer->update(['last_seen_at' => now()]);

        $sessionId = $request->header('X-Session-Id');
        if ($sessionId) {
            Cart::mergeGuestCart($sessionId, $customer->id);
            Address::mergeGuestAddresses($sessionId, $customer->id);
        }

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

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = Password::broker('customers')->sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return successResponse(null, __($status));
        }

        return errorResponse(__($status), 422);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::broker('customers')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Customer $customer, string $password) {
                $customer->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $customer->save();

                $customer->tokens()->delete();

                event(new PasswordReset($customer));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return successResponse(null, __($status));
        }

        return errorResponse(__($status), 422);
    }

    public function sendVerificationEmail(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return successResponse(null, 'Email already verified.');
        }

        $request->user()->sendEmailVerificationNotification();

        return successResponse(null, 'Verification link sent.');
    }

    public function verifyEmail(Request $request, int $id, string $hash): JsonResponse
    {
        $customer = Customer::findOrFail($id);

        if (! hash_equals(sha1($customer->getEmailForVerification()), $hash)) {
            return errorResponse('Invalid verification link.', 403);
        }

        if ($customer->hasVerifiedEmail()) {
            return successResponse(null, 'Email already verified.');
        }

        $customer->markEmailAsVerified();

        event(new Verified($customer));

        return successResponse(null, 'Email verified successfully.');
    }

    public function user(Request $request): JsonResponse
    {
        return successResponse($request->user());
    }
}
