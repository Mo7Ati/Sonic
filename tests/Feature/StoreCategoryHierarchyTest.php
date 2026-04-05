<?php

use App\Models\StoreCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('persists parent child relationship and scopes root categories', function (): void {
    $parent = StoreCategory::query()->create([
        'name' => ['en' => 'Electronics', 'ar' => 'إلكترونيات'],
        'description' => ['en' => 'Parent', 'ar' => 'أب'],
    ]);

    $child = StoreCategory::query()->create([
        'parent_id' => $parent->id,
        'name' => ['en' => 'Phones', 'ar' => 'هواتف'],
        'description' => ['en' => 'Child', 'ar' => 'ابن'],
    ]);

    expect($child->parent_id)->toBe($parent->id)
        ->and($child->fresh()->parent?->is($parent))->toBeTrue()
        ->and($parent->children()->pluck('id')->all())->toBe([$child->id])
        ->and(StoreCategory::query()->roots()->pluck('id')->all())->toBe([$parent->id]);
});

it('generates hierarchical slugs for subcategories', function (): void {
    $parent = StoreCategory::query()->create([
        'name' => ['en' => 'Electronics', 'ar' => 'إلكترونيات'],
        'description' => ['en' => 'Parent', 'ar' => 'أب'],
    ]);

    $child = StoreCategory::query()->create([
        'parent_id' => $parent->id,
        'name' => ['en' => 'Phones', 'ar' => 'هواتف'],
        'description' => ['en' => 'Child', 'ar' => 'ابن'],
    ]);

    expect($parent->slug)->toBe('electronics')
        ->and($child->slug)->toBe('electronics-phones');
});
