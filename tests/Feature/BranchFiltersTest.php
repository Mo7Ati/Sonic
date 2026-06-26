<?php

use App\Models\Branch;
use App\Models\Store;
use App\Models\StoreCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createStoreInCategory(StoreCategory $category): Store
{
    $suffix = uniqid();

    $store = Store::query()->create([
        'name' => ['en' => 'Store '.$suffix, 'ar' => 'متجر'],
        'description' => ['en' => 'Description', 'ar' => 'وصف'],
        'email' => 'store'.$suffix.'@test.com',
        'phone' => '01'.$suffix,
        'password' => 'password',
        'is_active' => true,
    ]);

    $store->storeCategories()->attach($category->id);

    return $store;
}

function createBranchForCategory(StoreCategory $category, array $branchAttributes = []): Branch
{
    $store = createStoreInCategory($category);

    return Branch::query()->create(array_merge([
        'name' => ['en' => 'Branch', 'ar' => 'فرع'],
        'address' => ['en' => 'Address', 'ar' => 'عنوان'],
        'store_id' => $store->id,
        'delivery_fee' => 10,
        'delivery_time_from' => 20,
        'delivery_time_to' => 30,
        'is_active' => true,
    ], $branchAttributes));
}

it('sorts branches by delivery fee ascending', function (): void {
    $category = StoreCategory::query()->create([
        'name' => ['en' => 'Food', 'ar' => 'طعام'],
        'description' => ['en' => 'Food', 'ar' => 'طعام'],
    ]);

    $cheap = createBranchForCategory($category, [
        'name' => ['en' => 'Cheap Branch', 'ar' => 'فرع رخيص'],
        'delivery_fee' => 5,
    ]);

    $expensive = createBranchForCategory($category, [
        'name' => ['en' => 'Premium Branch', 'ar' => 'فرع غالي'],
        'delivery_fee' => 20,
    ]);

    $response = $this->getJson('/api/customer/branches?store_category_id='.$category->id.'&sort_by=delivery_fee');

    $response->assertOk()
        ->assertJsonPath('success', true);

    $ids = collect($response->json('data.data'))->pluck('id')->all();

    expect($ids)->toBe([$cheap->id, $expensive->id]);
});

it('filters branches by fast delivery', function (): void {
    $category = StoreCategory::query()->create([
        'name' => ['en' => 'Quick', 'ar' => 'سريع'],
        'description' => ['en' => 'Quick', 'ar' => 'سريع'],
    ]);

    $fast = createBranchForCategory($category, [
        'name' => ['en' => 'Fast Branch', 'ar' => 'فرع سريع'],
        'delivery_time_to' => 25,
    ]);

    createBranchForCategory($category, [
        'name' => ['en' => 'Slow Branch', 'ar' => 'فرع بطيء'],
        'delivery_time_to' => 45,
    ]);

    $response = $this->getJson('/api/customer/branches?store_category_id='.$category->id.'&fast_delivery=1');

    $response->assertOk()
        ->assertJsonPath('success', true);

    $ids = collect($response->json('data.data'))->pluck('id')->all();

    expect($ids)->toBe([$fast->id]);
});
