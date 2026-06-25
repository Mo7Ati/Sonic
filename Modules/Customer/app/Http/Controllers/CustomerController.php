<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\PhoneVerificationService;
use Modules\Customer\Http\Requests\UpdateProfileRequest;
use Modules\Customer\Http\Resources\SplashResource;

class CustomerController extends Controller
{
    public function __construct(
        private readonly PhoneVerificationService $phoneVerificationService,
    ) {
    }

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

    public function updateProfile(UpdateProfileRequest $request)
    {
        $data = $request->validate($request->sendOtpRules());
        $customer = auth('sanctum')->user();

        $otpSent = false;

        // Update name immediately
        if (isset($data['name'])) {
            $customer->name = $data['name'];
        }

        // Phone number changed
        if (
            isset($data['phone_number']) &&
            $data['phone_number'] !== $customer->phone_number
        ) {
            $otp = $this->phoneVerificationService->sendOtp($data['phone_number']);
            $otpSent = true;
        }

        $customer->save();

        return successResponse([
            'otp_sent' => $otpSent,
            'otp' => $otp,
        ], __('messages.profile_updated_successfully'));
    }

    public function verifyNewPhone(UpdateProfileRequest $request)
    {
        $customer = auth('sanctum')->user();

        $data = $request->validate($request->verifyNewPhoneRules());
        $newPhoneNumber = $data['new_phone_number'];
        $otp = $data['otp'];

        $this->phoneVerificationService->verifyOtp(
            $newPhoneNumber,
            $otp
        );

        $customer->update([
            'phone_number' => $newPhoneNumber,
        ]);

        return successResponse();
    }
}
