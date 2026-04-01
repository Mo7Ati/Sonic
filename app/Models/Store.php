<?php

namespace App\Models;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Traits\HasWallet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Cashier\Billable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Store extends Authenticatable implements HasMedia, Wallet
{
    use HasFactory, HasTranslations, TwoFactorAuthenticatable, InteractsWithMedia, Billable, HasWallet;

    protected $fillable = [
        'name',
        'slug',
        'address',
        'description',
        'keywords',
        'social_media',
        'email',
        'phone',
        'password',
        'category_id',
        'delivery_time',
        'delivery_area_polygon',
        'is_active',
        'profile_completed_at',
    ];

    protected $casts = [
        'name' => 'array',
        'address' => 'array',
        'description' => 'array',
        'keywords' => 'array',
        'social_media' => 'array',
        'delivery_area_polygon' => 'json',
        'profile_completed_at' => 'datetime',
    ];


    public array $translatable = ['name', 'description', 'address'];

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

    public function hasCompletedProfile(): bool
    {
        return $this->profile_completed_at !== null;
    }

    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = Hash::make($value);
        }
    }


    public function category()
    {
        return $this->belongsTo(StoreCategory::class);
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
    public function scopeCategory($query, $category)
    {
        return $query->whereHas('category', function ($query) use ($category) {
            $query->where('slug', $category);
        });
    }
    public function scopeActive($query, $value = true)
    {
        return $query->where('is_active', $value);
    }

    /**
     * Apply filters to the query (scope – call as applyFilters($request)).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApplyFilters($query, Request $request)
    {
        return $query
            ->when($request->input('search'), fn($q, $search) => $q->search($search))
            ->when($request->input('category'), fn($q, $category) => $q->category($category))
            ->when($request->filled('is_active'), fn($q) => $q->active($request->input('is_active')));
    }
}
