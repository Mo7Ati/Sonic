<?php

namespace App\Models;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Traits\HasWallet;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'slug', 'description', 'keywords', 'social_media', 'email', 'phone', 'password', 'category_id', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class Store extends Authenticatable implements HasMedia, Wallet
{
    use HasFactory, HasTranslations, HasWallet, InteractsWithMedia, SoftDeletes;

    protected $casts = [
        'name' => 'array',
        // 'address' => 'array',
        'description' => 'array',
        'keywords' => 'array',
        'social_media' => 'array',
        // 'delivery_area_polygon' => 'json',
        // 'profile_completed_at' => 'datetime',
    ];

    public array $translatable = ['name', 'description', 'keywords']; // , 'address'

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->getTranslation('name', 'en'));
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('name')) {
                $model->slug = Str::slug($model->getTranslation('name', 'en'));
            }
        });
    }

    // public function hasCompletedProfile(): bool
    // {
    //     return $this->profile_completed_at !== null;
    // }

    public function setPasswordAttribute(?string $value): void
    {
        if (! filled($value)) {
            return;
        }

        $this->attributes['password'] = Hash::isHashed((string) $value)
            ? $value
            : Hash::make($value);
    }

    /**
     * The categories that the store belongs to.
     *
     * @return BelongsToMany<StoreCategory>
     */
    public function storeCategories(): BelongsToMany
    {
        return $this->belongsToMany(StoreCategory::class, 'category_stores', 'store_id', 'category_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function additions()
    {
        return $this->hasMany(Addition::class);
    }

    public function options()
    {
        return $this->hasMany(Option::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function scopeSearch($query, $search)
    {
        return $query->whereAny([
            'name',
            'description',
            'address',
            'keywords',
            'social_media',
        ], 'like', "%{$search}%");
    }

    // public function scopeCategory($query, $category)
    // {
    //     return $query->whereHas('category', function ($query) use ($category) {
    //         $query->where('slug', $category);
    //     });
    // }

    public function scopeActive($query, $value = true)
    {
        return $query->where('is_active', $value);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('store_images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
            ->singleFile();

        $this->addMediaCollection('store_cover_images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
            ->singleFile();
    }

    /**
     * Apply filters to the query (scope – call as applyFilters($request)).
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeApplyFilters($query, Request $request)
    {
        return $query
            ->when($request->input('search'), fn ($q, $search) => $q->search($search))
            // ->when($request->input('category'), fn($q, $category) => $q->category($category))
            ->when($request->filled('is_active'), fn ($q) => $q->active($request->input('is_active')));
    }
}
