<?php

use App\Models\Customer;
use App\Models\DeviceToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

const VALID_EXPO_TOKEN = 'ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]';

it('registers a device token for the authenticated customer', function (): void {
    $customer = Customer::factory()->create();
    Sanctum::actingAs($customer);

    $response = $this->postJson('/api/customer/device-tokens', [
        'expo_token' => VALID_EXPO_TOKEN,
        'platform' => 'android',
    ]);

    $response->assertOk();

    $this->assertDatabaseHas('device_tokens', [
        'customer_id' => $customer->id,
        'expo_token' => VALID_EXPO_TOKEN,
        'platform' => 'android',
    ]);
});

it('does not duplicate an existing token but updates it', function (): void {
    $customer = Customer::factory()->create();
    Sanctum::actingAs($customer);

    $this->postJson('/api/customer/device-tokens', ['expo_token' => VALID_EXPO_TOKEN, 'platform' => 'android'])->assertOk();
    $this->postJson('/api/customer/device-tokens', ['expo_token' => VALID_EXPO_TOKEN, 'platform' => 'ios'])->assertOk();

    expect(DeviceToken::where('expo_token', VALID_EXPO_TOKEN)->count())->toBe(1);
    expect(DeviceToken::where('expo_token', VALID_EXPO_TOKEN)->first()->platform)->toBe('ios');
});

it('rejects a malformed expo token', function (): void {
    $customer = Customer::factory()->create();
    Sanctum::actingAs($customer);

    $this->postJson('/api/customer/device-tokens', ['expo_token' => 'not-a-real-token'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('expo_token');
});

it('removes a device token on logout', function (): void {
    $customer = Customer::factory()->create();
    $token = DeviceToken::factory()->for($customer)->create(['expo_token' => VALID_EXPO_TOKEN]);

    Sanctum::actingAs($customer);

    $this->deleteJson('/api/customer/device-tokens', ['expo_token' => VALID_EXPO_TOKEN])->assertOk();

    $this->assertModelMissing($token);
});

it('requires authentication to register a token', function (): void {
    $this->postJson('/api/customer/device-tokens', ['expo_token' => VALID_EXPO_TOKEN])
        ->assertUnauthorized();
});
