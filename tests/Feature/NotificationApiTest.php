<?php

use App\Models\Customer;
use App\Notifications\CustomNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

/**
 * Persist a database notification directly so the API tests don't depend on
 * dispatching through the notification pipeline.
 *
 * @param  array<string, mixed>  $data
 */
function seedNotification(Customer $customer, array $data = [], ?string $readAt = null): void
{
    $customer->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => CustomNotification::class,
        'data' => array_merge(['type' => 'custom', 'title' => 'Hello', 'body' => 'World'], $data),
        'read_at' => $readAt,
    ]);
}

it('lists the customer notifications', function (): void {
    $customer = Customer::factory()->create();
    seedNotification($customer, ['title' => 'First']);
    seedNotification($customer, ['title' => 'Second']);

    Sanctum::actingAs($customer);

    $this->getJson('/api/customer/notifications')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(2, 'data.data');
});

it('returns the unread count', function (): void {
    $customer = Customer::factory()->create();
    seedNotification($customer);
    seedNotification($customer);
    seedNotification($customer, readAt: now()->toDateTimeString());

    Sanctum::actingAs($customer);

    $this->getJson('/api/customer/notifications/unread-count')
        ->assertOk()
        ->assertJsonPath('data.count', 2);
});

it('marks a single notification as read', function (): void {
    $customer = Customer::factory()->create();
    seedNotification($customer);

    Sanctum::actingAs($customer);

    $id = $customer->notifications()->first()->id;

    $this->postJson("/api/customer/notifications/{$id}/read")->assertOk();

    expect($customer->fresh()->unreadNotifications()->count())->toBe(0);
});

it('marks all notifications as read', function (): void {
    $customer = Customer::factory()->create();
    seedNotification($customer);
    seedNotification($customer);

    Sanctum::actingAs($customer);

    $this->postJson('/api/customer/notifications/read-all')->assertOk();

    expect($customer->fresh()->unreadNotifications()->count())->toBe(0);
});

it('does not expose another customer notifications', function (): void {
    $customer = Customer::factory()->create();
    $other = Customer::factory()->create();
    seedNotification($other);

    Sanctum::actingAs($customer);

    $this->getJson('/api/customer/notifications')
        ->assertOk()
        ->assertJsonCount(0, 'data.data');
});
