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
     * @param  string  $phone_number
     */
    public function sendOtp(string $phone_number): string
    {
        $phone_with_both_country_codes = $this->normalizePhone($phone_number);
        $otp = $this->generateOtp();
        $expiryMinutes = (int) config('whatsapp.otp.expiry_minutes');

        PhoneVerification::query()
            ->where('phone_number', $phone_number)
            ->whereNull('verified_at')
            ->delete();

        PhoneVerification::query()->create([
            'phone_number' => $phone_number,
            'otp_hash' => Hash::make($otp),
            'payload' => [],
            'expires_at' => now()->addMinutes($expiryMinutes),
        ]);
        return $otp;
        // $this->dispatchOtp($phone_with_both_country_codes, $otp, $expiryMinutes);
    }

    public function verifyOtp(string $phoneNumber, string $code): void
    {
        $verification = $this->findActiveVerification($phoneNumber);

        $this->assertValidOtp($verification, $code);

        $verification->update(['verified_at' => now()]);
    }

    /**
     * @return array{expires_in: int, phone_masked: string}
     */
    public function resendOtp(string $phoneNumber): void
    {
        $phone_with_both_country_codes = $this->normalizePhone($phoneNumber);
        $verification = $this->findActiveVerification($phoneNumber);

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

        $this->dispatchOtp($phone_with_both_country_codes, $otp, $expiryMinutes);
    }

    /**
     * @return array{expires_in: int, phone_masked: string}
     */
    public function sendPhoneChangeOtp(Customer $customer, string $newPhone): array
    {
        $this->assertPhoneAvailableForChange($customer, $newPhone);

        $otp = $this->generateOtp();
        $expiryMinutes = (int) config('whatsapp.otp.expiry_minutes');

        PhoneVerification::query()
            ->where('phone_number', $newPhone)
            ->whereNull('verified_at')
            ->where('payload->purpose', 'phone_change')
            ->delete();

        PhoneVerification::query()->create([
            'phone_number' => $newPhone,
            'otp_hash' => Hash::make($otp),
            'payload' => [
                'purpose' => 'phone_change',
                'customer_id' => $customer->id,
            ],
            'expires_at' => now()->addMinutes($expiryMinutes),
        ]);

        $this->dispatchOtp($this->normalizePhone($newPhone), $otp, $expiryMinutes);

        return [
            'expires_in' => $expiryMinutes * 60,
            'phone_masked' => $this->maskPhone($newPhone),
        ];
    }

    /**
     * @return array{expires_in: int, phone_masked: string}
     */
    public function resendPhoneChangeOtp(Customer $customer, string $newPhone): array
    {
        $this->assertPhoneAvailableForChange($customer, $newPhone);

        $verification = $this->findPhoneChangeVerification($customer, $newPhone);

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

        $this->dispatchOtp($this->normalizePhone($newPhone), $otp, $expiryMinutes);

        return [
            'expires_in' => $expiryMinutes * 60,
            'phone_masked' => $this->maskPhone($newPhone),
        ];
    }

    public function verifyPhoneChangeOtp(Customer $customer, string $newPhone, string $code, ?string $name = null): Customer
    {
        $this->assertPhoneAvailableForChange($customer, $newPhone);

        $verification = $this->findPhoneChangeVerification($customer, $newPhone);

        $this->assertValidOtp($verification, $code);

        $verification->update(['verified_at' => now()]);

        if (Customer::query()->where('phone_number', $newPhone)->where('id', '!=', $customer->id)->exists()) {
            throw ValidationException::withMessages([
                'phone_number' => [__('auth.phone_change.already_taken')],
            ]);
        }

        $updates = ['phone_number' => $newPhone];

        if ($name !== null) {
            $updates['name'] = $name;
        }

        $customer->update($updates);

        return $customer->fresh();
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

    private function findPhoneChangeVerification(Customer $customer, string $phone): PhoneVerification
    {
        $verification = PhoneVerification::query()
            ->where('phone_number', $phone)
            ->whereNull('verified_at')
            ->where('payload->purpose', 'phone_change')
            ->where('payload->customer_id', $customer->id)
            ->latest()
            ->first();

        if ($verification === null) {
            throw ValidationException::withMessages([
                'phone_number' => [__('auth.phone_verification.not_found')],
            ]);
        }

        return $verification;
    }

    private function assertPhoneAvailableForChange(Customer $customer, string $newPhone): void
    {
        if ($newPhone === $customer->phone_number) {
            throw ValidationException::withMessages([
                'phone_number' => [__('auth.phone_change.same_phone')],
            ]);
        }

        if (Customer::query()->where('phone_number', $newPhone)->where('id', '!=', $customer->id)->exists()) {
            throw ValidationException::withMessages([
                'phone_number' => [__('auth.phone_change.already_taken')],
            ]);
        }
    }

    private function assertValidOtp(PhoneVerification $verification, string $code): void
    {
        if ($verification->isExpired()) {
            throw ValidationException::withMessages([
                'otp' => [__('auth.phone_verification.expired')],
            ]);
        }

        if ($verification->hasExceededMaxAttempts()) {
            throw ValidationException::withMessages([
                'otp' => [__('auth.phone_verification.too_many_attempts')],
            ]);
        }

        if (!Hash::check($code, $verification->otp_hash)) {
            $verification->increment('attempts');

            throw ValidationException::withMessages([
                'otp' => [__('auth.phone_verification.invalid')],
            ]);
        }
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
