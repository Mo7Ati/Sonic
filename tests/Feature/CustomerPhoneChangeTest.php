<?php

use App\Jobs\SendWhatsAppOtpJob;
use App\Models\Customer;
use App\Models\PhoneVerification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['whatsapp.driver' => 'log']);
});

it('sends otp when changing to a new phone number', function (): void {
    Queue::fake();

    $customer = Customer::factory()->create([
        'phone_number' => '0599999999',
    ]);

    Sanctum::actingAs($customer);

    $response = $this->postJson('/api/customer/change-phone/send-otp', [
        'phone_number' => '0588888888',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['expires_in', 'phone_masked']]);

    $this->assertDatabaseHas('phone_verifications', [
        'phone_number' => '0588888888',
    ]);

    Queue::assertPushed(SendWhatsAppOtpJob::class);
});

it('rejects changing to the same phone number', function (): void {
    $customer = Customer::factory()->create([
        'phone_number' => '0599999999',
    ]);

    Sanctum::actingAs($customer);

    $response = $this->postJson('/api/customer/change-phone/send-otp', [
        'phone_number' => '0599999999',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('phone_number');
});

it('rejects changing to a phone number used by another customer', function (): void {
    $customer = Customer::factory()->create([
        'phone_number' => '0599999999',
    ]);

    Customer::factory()->create([
        'phone_number' => '0588888888',
    ]);

    Sanctum::actingAs($customer);

    $response = $this->postJson('/api/customer/change-phone/send-otp', [
        'phone_number' => '0588888888',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('phone_number');
});

it('verifies otp and updates the customer phone number', function (): void {
    $customer = Customer::factory()->create([
        'phone_number' => '0599999999',
        'name' => 'Old Name',
    ]);

    $plainOtp = '123456';

    PhoneVerification::query()->create([
        'phone_number' => '0588888888',
        'otp_hash' => Hash::make($plainOtp),
        'payload' => [
            'purpose' => 'phone_change',
            'customer_id' => $customer->id,
        ],
        'expires_at' => now()->addMinutes(10),
    ]);

    Sanctum::actingAs($customer);

    $response = $this->postJson('/api/customer/change-phone/verify-otp', [
        'phone_number' => '0588888888',
        'otp' => $plainOtp,
        'name' => 'New Name',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.phone_number', '0588888888')
        ->assertJsonPath('data.name', 'New Name');

    expect($customer->fresh())
        ->phone_number->toBe('0588888888')
        ->name->toBe('New Name');
});

it('rejects an invalid otp when changing phone number', function (): void {
    $customer = Customer::factory()->create([
        'phone_number' => '0599999999',
    ]);

    PhoneVerification::query()->create([
        'phone_number' => '0588888888',
        'otp_hash' => Hash::make('123456'),
        'payload' => [
            'purpose' => 'phone_change',
            'customer_id' => $customer->id,
        ],
        'expires_at' => now()->addMinutes(10),
    ]);

    Sanctum::actingAs($customer);

    $response = $this->postJson('/api/customer/change-phone/verify-otp', [
        'phone_number' => '0588888888',
        'otp' => '000000',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('otp');

    expect($customer->fresh()->phone_number)->toBe('0599999999');
});

it('updates profile name without changing phone number', function (): void {
    $customer = Customer::factory()->create([
        'phone_number' => '0599999999',
        'name' => 'Old Name',
    ]);

    Sanctum::actingAs($customer);

    $response = $this->patchJson('/api/customer/user', [
        'name' => 'Updated Name',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.name', 'Updated Name')
        ->assertJsonPath('data.phone_number', '0599999999');
});

it('requires authentication to change phone number', function (): void {
    $response = $this->postJson('/api/customer/change-phone/send-otp', [
        'phone_number' => '0588888888',
    ]);

    $response->assertUnauthorized();
});
