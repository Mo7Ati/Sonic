<?php

use App\Models\Customer;
use App\Settings\AddressSettings;
use App\Settings\CustomPages;
use App\Settings\OnboardingSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('returns the authenticated customer from /me', function (): void {
    $customer = Customer::factory()->create(['name' => 'Ada']);
    Sanctum::actingAs($customer);

    $this->getJson('/api/customer/me')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.id', $customer->id)
        ->assertJsonPath('data.name', 'Ada')
        ->assertJsonPath('data.phone_number', $customer->phone_number);
});

it('rejects /me without a valid token', function (): void {
    $this->getJson('/api/customer/me')->assertUnauthorized();
});

it('returns static onboarding slides and custom pages from /config without auth', function (): void {
    app(OnboardingSettings::class)->fill(['steps' => [
        ['title' => ['en' => 'Welcome', 'ar' => 'مرحبا'], 'description' => ['en' => 'Hi', 'ar' => 'أهلا'], 'color' => '#fff', 'image' => null],
    ]])->save();

    app(CustomPages::class)->fill(['pages' => [
        ['title' => ['en' => 'About', 'ar' => 'حول'], 'content' => 'About us'],
    ]])->save();

    $this->getJson('/api/customer/config')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(1, 'data.onboardingSlides')
        ->assertJsonPath('data.onboardingSlides.0.title', 'Welcome')
        ->assertJsonCount(1, 'data.customPages')
        ->assertJsonPath('data.customPages.0.title', 'About')
        ->assertJsonPath('data.customPages.0.content', 'About us');
});

it('returns the platform address field templates from /addresses/fields', function (): void {
    $fields = [['key' => 'street', 'label' => ['en' => 'Street'], 'type' => 'text', 'required' => true]];
    app(AddressSettings::class)->fill(['fields' => $fields])->save();

    $this->getJson('/api/customer/addresses/fields')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.0.key', 'street');
});
