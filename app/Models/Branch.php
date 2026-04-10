<?php

namespace App\Models;

use App\Enums\BranchStatusEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'store_id', 'address', 'delivery_time_from', 'delivery_time_to', 'delivery_fee', 'range_of_area_polygon', 'location', 'is_active', 'status'])]
class Branch extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia, SoftDeletes;

    protected $casts = [
        'name' => 'array',
        'address' => 'array',
        'range_of_area_polygon' => 'array',
        'location' => 'array',
        'is_active' => 'boolean',
        'status' => BranchStatusEnum::class,
    ];

    public $translatable = ['name', 'address'];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (is_null($model->store_id) && auth()->guard('store')->check()) {
                $model->store_id = auth()->guard('store')->id();
            }
            if (is_null($model->location) && auth()->guard('store')->check()) {
                $model->location = [
                    'latitude' => '123',
                    'longitude' => '123',
                ];
            }
        });
    }

    /**
     * Get the store that owns the branch.
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the orders for this branch.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the products through the store relationship.
     */
    public function products()
    {
        return $this->hasManyThrough(Product::class, Store::class);
    }

    /**
     * Scope for location-based search within radius
     */
    public function scopeNearLocation($query, $latitude, $longitude, $radius = 10)
    {
        if (!$latitude || !$longitude) {
            return $query;
        }

        return $query->whereRaw("
            ST_Distance_Sphere(
                POINT(?, ?),
                POINT(JSON_EXTRACT(location, '$.longitude'), JSON_EXTRACT(location, '$.latitude'))
            ) <= ?
        ", [$longitude, $latitude, $radius * 1000]); // Convert km to meters
    }

    /**
     * Scope for store category filtering
     */
    public function scopeStoreCategory(Builder $query, ?string $storeCategoryId = null): Builder
    {
        return $query->withWhereHas('store', function ($storeQuery) use ($storeCategoryId) {
            $storeQuery->when($storeCategoryId, function ($q) use ($storeCategoryId) {
                $q->whereHas('storeCategories', function ($cq) use ($storeCategoryId) {
                    $cq->where('store_categories.id', $storeCategoryId);
                });
            });
        });
    }

    /**
     * Scope for active branches only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by distance from a location
     */
    public function scopeOrderByDistance($query, $latitude, $longitude)
    {
        if (!$latitude || !$longitude) {
            return $query;
        }

        return $query->orderByRaw("
            ST_Distance_Sphere(
                POINT(?, ?),
                POINT(JSON_EXTRACT(location, '$.longitude'), JSON_EXTRACT(location, '$.latitude'))
            )
        ", [$longitude, $latitude]);
    }

    /**
     * Scope to order by average rate
     */
    public function scopeAverageRate($q, $value)
    {
        $q->when($value, function (Builder $query, string $value) {
            $query->whereHas('store', function ($storeQuery) use ($value) {
                $storeQuery->withAvg('ratings', 'rate')
                    ->havingRaw('ratings_avg_rate >= ? AND ratings_avg_rate < ?', [$value, $value + 1]);
            });
        });
    }

    public function scopeActiveStore($q)
    {
        $q->whereHas('store', function ($storeQuery) {
            $storeQuery->where('is_active', true);
        });
    }

    public function scopeSearch(Builder $query, ?string $value): Builder
    {
        return $query->when($value, function ($q) use ($value) {
            $q->where(function ($q) use ($value) {
                $q->whereRaw("LOWER(JSON_EXTRACT(name, '$.*')) LIKE ?", ['%' . mb_strtolower($value) . '%'])
                    ->orWhereRaw("LOWER(JSON_EXTRACT(address, '$.*')) LIKE ?", ['%' . mb_strtolower($value) . '%'])
                    ->orWhereHas('store', function ($q) use ($value) {
                        $q->whereRaw("LOWER(JSON_EXTRACT(name, '$.*')) LIKE ?", ['%' . mb_strtolower($value) . '%']);
                    });
            });
        });
    }

    public function getDeliveryTimeAttribute()
    {
        return $this->delivery_time_from . '-' . $this->delivery_time_to;
    }

    public function scopeFilters(Builder $query): Builder
    {
        $request = request();

        return $query
            ->active()
            ->search($request->input('search'))
            ->storeCategory($request->input('store_category_id'))
            ->averageRate($request->input('average_rate'));
    }
}
