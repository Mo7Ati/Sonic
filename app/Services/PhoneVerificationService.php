<?php

namespace App\Services;

use App\Jobs\SendWhatsAppOtpJob;
use App\Models\Customer;
use App\Models\PhoneVerification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PhoneVerificationService
{
    /**
     * @param  array{name: string, phone_number: string, password: string}  $data
     * @return array{expires_in: int, phone_masked: string}
     */
    public function startRegistration(array $data): array
    {
        $phone = $data['phone_number'];
        $phone_with_both_country_codes = $this->normalizePhone($phone);
        $otp = $this->generateOtp();
        $expiryMinutes = (int) config('whatsapp.otp.expiry_minutes');

        PhoneVerification::query()
            ->where('phone_number', $phone)
            ->whereNull('verified_at')
            ->delete();

        PhoneVerification::query()->create([
            'phone_number' => $phone,
            'otp_hash' => Hash::make($otp),
            'payload' => [
                'name' => $data['name'],
            ],
            'expires_at' => now()->addMinutes($expiryMinutes),
        ]);

        $this->dispatchOtp($phone_with_both_country_codes, $otp, $expiryMinutes);

        return [
            'expires_in' => $expiryMinutes * 60,
            'phone_masked' => $this->maskPhone($phone),
        ];
    }

    public function verifyRegistrationOtp(string $phoneNumber, string $code): Customer
    {
        $phone = $this->normalizePhone($phoneNumber);
        $verification = $this->findActiveVerification($phone);

        if ($verification->isExpired()) {
            throw ValidationException::withMessages([
                'code' => [__('auth.phone_verification.expired')],
            ]);
        }

        if ($verification->hasExceededMaxAttempts()) {
            throw ValidationException::withMessages([
                'code' => [__('auth.phone_verification.too_many_attempts')],
            ]);
        }

        if (!Hash::check($code, $verification->otp_hash)) {
            $verification->increment('attempts');

            throw ValidationException::withMessages([
                'code' => [__('auth.phone_verification.invalid')],
            ]);
        }

        if (Customer::query()->where('phone_number', $phone)->exists()) {
            throw ValidationException::withMessages([
                'phone_number' => [__('validation.unique', ['attribute' => 'phone number'])],
            ]);
        }

        $customer = Customer::query()->create([
            'name' => $verification->payload['name'],
            'phone_number' => $phone,
            'password' => Hash::make(Str::random(10)),
            'phone_verified_at' => now(),
        ]);

        $verification->update(['verified_at' => now()]);

        event(new Registered($customer));

        return $customer;
    }

    /**
     * @return array{expires_in: int, phone_masked: string}
     */
    public function resendOtp(string $phoneNumber): array
    {
        $phone = $this->normalizePhone($phoneNumber);
        $verification = $this->findActiveVerification($phone);

        if ($verification->isExpired()) {
            throw ValidationException::withMessages([
                'phone_number' => [__('auth.phone_verification.expired')],
            ]);
        }

        $cooldown = (int) config('whatsapp.otp.resend_cooldown_seconds');

        if ($verification->updated_at->copy()->addSeconds($cooldown)->isFuture()) {
            throw ValidationException::withMessages([
                'phone_number' => [__('auth.phone_verification.resend_cooldown')],
            ]);
        }

        $otp = $this->generateOtp();
        $expiryMinutes = (int) config('whatsapp.otp.expiry_minutes');

        $verification->update([
            'otp_hash' => Hash::make($otp),
            'attempts' => 0,
            'expires_at' => now()->addMinutes($expiryMinutes),
        ]);

        $this->dispatchOtp($phone, $otp, $expiryMinutes);

        return [
            'expires_in' => $expiryMinutes * 60,
            'phone_masked' => $this->maskPhone($phone),
        ];
    }

    public function normalizePhone(string $phone): array
    {
        $localNumber = ltrim($phone, '0');

        return [
            '+970' . $localNumber,
            '+972' . $localNumber,
        ];
    }

    private function findActiveVerification(string $phone): PhoneVerification
    {
        $verification = PhoneVerification::query()
            ->where('phone_number', $phone)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if ($verification === null) {
            throw ValidationException::withMessages([
                'phone_number' => [__('auth.phone_verification.not_found')],
            ]);
        }

        return $verification;
    }

    private function generateOtp(): string
    {
        $length = (int) config('whatsapp.otp.length');

        return str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);
    }

    private function dispatchOtp(array $phone_with_both_country_codes, string $otp, int $expiryMinutes): void
    {
        $message = __('auth.phone_verification.otp_message', [
            'code' => $otp,
            'minutes' => $expiryMinutes,
            'app' => config('app.name'),
        ]);

        SendWhatsAppOtpJob::dispatch($phone_with_both_country_codes, $message);
    }

    private function maskPhone(string $phone): string
    {
        $digits = strlen($phone);

        if ($digits <= 6) {
            return Str::mask($phone, '*', 1);
        }

        return substr($phone, 0, 4) . str_repeat('*', max(0, $digits - 7)) . substr($phone, -3);
    }
}
