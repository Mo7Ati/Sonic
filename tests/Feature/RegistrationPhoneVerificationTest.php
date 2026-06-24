<?php

use App\Jobs\SendWhatsAppOtpJob;
use App\Models\Customer;
use App\Models\PhoneVerification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['whatsapp.driver' => 'log']);
});

function registrationPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Test User',
        'phone_number' => '+970599999999',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ], $overrides);
}

it('starts registration and dispatches a whatsapp otp job', function (): void {
    Queue::fake();

    $response = $this->postJson('/api/customer/register', registrationPayload());

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['expires_in', 'phone_masked']]);

    $this->assertDatabaseHas('phone_verifications', [
        'phone_number' => '+970599999999',
    ]);

    Queue::assertPushed(SendWhatsAppOtpJob::class);
});

it('requires phone number on registration', function (): void {
    $response = $this->postJson('/api/customer/register', registrationPayload([
        'phone_number' => null,
    ]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors('phone_number');
});

it('verifies otp and creates a customer with phone verified', function (): void {
    $plainOtp = '123456';

    PhoneVerification::query()->create([
        'phone_number' => '+970599999999',
        'otp_hash' => Hash::make($plainOtp),
        'payload' => [
            'name' => 'Test User',
            'password' => Hash::make('password123'),
        ],
        'expires_at' => now()->addMinutes(10),
    ]);

    $response = $this->postJson('/api/customer/register/verify-otp', [
        'phone_number' => '+970599999999',
        'code' => $plainOtp,
    ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['customer', 'token']]);

    $customer = Customer::query()->where('phone_number', '+970599999999')->first();

    expect($customer)->not->toBeNull()
        ->and($customer->email)->toBeNull()
        ->and($customer->phone_verified_at)->not->toBeNull();
});

it('logs in with phone number and password', function (): void {
    Customer::factory()->create([
        'phone_number' => '+970588888888',
        'email' => null,
        'password' => 'password123',
    ]);

    $response = $this->postJson('/api/customer/login', [
        'phone_number' => '+970588888888',
        'password' => 'password123',
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['customer', 'token']]);
});

it('rejects an invalid otp code', function (): void {
    PhoneVerification::query()->create([
        'phone_number' => '+970599999999',
        'otp_hash' => Hash::make('123456'),
        'payload' => [
            'name' => 'Test User',
            'password' => Hash::make('password123'),
        ],
        'expires_at' => now()->addMinutes(10),
    ]);

    $response = $this->postJson('/api/customer/register/verify-otp', [
        'phone_number' => '+970599999999',
        'code' => '000000',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('code');

    $this->assertDatabaseMissing('customers', [
        'phone_number' => '+970599999999',
    ]);
});

it('rejects an expired otp', function (): void {
    PhoneVerification::query()->create([
        'phone_number' => '+970599999999',
        'otp_hash' => Hash::make('123456'),
        'payload' => [
            'name' => 'Test User',
            'password' => Hash::make('password123'),
        ],
        'expires_at' => now()->subMinute(),
    ]);

    $response = $this->postJson('/api/customer/register/verify-otp', [
        'phone_number' => '+970599999999',
        'code' => '123456',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('code');
});

it('resends otp when cooldown has passed', function (): void {
    Queue::fake();

    $verification = PhoneVerification::query()->create([
        'phone_number' => '+970599999999',
        'otp_hash' => Hash::make('123456'),
        'payload' => [
            'name' => 'Test User',
            'password' => Hash::make('password123'),
        ],
        'expires_at' => now()->addMinutes(10),
    ]);

    $verification->forceFill(['updated_at' => now()->subMinutes(2)])->saveQuietly();

    $response = $this->postJson('/api/customer/register/resend-otp', [
        'phone_number' => '+970599999999',
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true);

    Queue::assertPushed(SendWhatsAppOtpJob::class);

    expect($verification->fresh()->attempts)->toBe(0);
});

it('blocks resend during cooldown', function (): void {
    PhoneVerification::query()->create([
        'phone_number' => '+970599999999',
        'otp_hash' => Hash::make('123456'),
        'payload' => [
            'name' => 'Test User',
            'password' => Hash::make('password123'),
        ],
        'expires_at' => now()->addMinutes(10),
    ]);

    $response = $this->postJson('/api/customer/register/resend-otp', [
        'phone_number' => '+970599999999',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('phone_number');
});
