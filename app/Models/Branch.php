<?php

namespace App\Models;

use App\Enums\BranchStatusEnum;
use App\Enums\DaysEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'store_id', 'address', 'delivery_time_from', 'delivery_time_to', 'delivery_fee', 'range_of_area_polygon', 'location', 'is_active', 'status'])]
class Branch extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes, HasTranslations;

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
                    'latitude' => "123",
                    'longitude' => "123",
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

    // public function times()
    // {
    //     return $this->hasMany(BranchTimes::class);
    // }

    /**
     * Get the products through the store relationship.
     */
    public function products()
    {
        return $this->hasManyThrough(Product::class, Store::class);
    }

    // public function saveTimes($times)
    // {
    //     $this->times()->delete();

    //     return $this->times()->createMany(collect($times)->filter(fn($time) => !!$time['enabled'] ?? false)->map(function ($time) {
    //         return [
    //             'index' => DaysEnum::from($time['day'])->index(),
    //             'from' => $time['enabled'] ? $time['from'] : null,
    //             'to' => $time['enabled'] ? $time['to'] : null,
    //         ];
    //     })->toArray());
    // }

    public function scopeSearch($query, Request $request)
    {
        return $query->storeCategory($request->query('category'));
    }

    public function scopeStoreCategory($q, $value)
    {
        $q->when($value, function (Builder $query, string $value) {
            // If category is numeric, filter by ID; otherwise search by name
            if (is_numeric($value)) {
                $query->whereHas('store.category', function ($q) use ($value) {
                    $q->where('id', $value);
                });
            } else {
                $query->withWhereRelation(
                    'store.category',
                    'name',
                    'LIKE',
                    '%' . $value . '%'
                );
            }
        });
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
     * Scope for category filtering
     */
    public function scopeByCategory($query, $categoryId)
    {
        if (!$categoryId) {
            return $query;
        }

        return $query->whereHas('store.category', function ($q) use ($categoryId) {
            $q->where('id', $categoryId);
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

    public function scopeSearchWithProducts($q, $search)
    {
        $q->whereHas('store', function ($branchQuery) use ($search) {
            $branchQuery->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhereHas('products', function ($productQuery) use ($search) {
                    $productQuery->active();
                    if ($search) {
                        $productQuery->where(function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->where('keywords', 'like', "%{$search}%")
                                ->orWhere('description', 'like', "%{$search}%");
                        });
                    }
                    $productQuery->with(['category', 'additions', 'options']);
                });
        })
            ->with([
                'store',
                'store.products' => function ($productQuery) use ($search) {
                    $productQuery->active();

                    if ($search) {
                        $productQuery->where(function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->where('keywords', 'like', "%{$search}%")
                                ->orWhere('description', 'like', "%{$search}%");
                        });
                    }
                    $productQuery->with(['category', 'additions', 'options']);
                },
            ]);
    }

    public function getDeliveryTimeAttribute()
    {
        return $this->delivery_time_from . '-' . $this->delivery_time_to;
    }

    public function scopeFilters($q, $filters)
    {
        $q->where(function ($query) use ($filters) {
            if (Arr::get($filters, 'search')) {
                $query->searchWithBranches($filters['search']);
            }
            if (isset($filters['categories'])) {
                $query->storeCategories($filters['categories']);
            }
            if (Arr::get($filters, 'rate')) {
                $query->averageRate($filters['rate']);
            }
        });
    }

    public function scopeSearchWithBranches($q, $value)
    {
        $q->when($value, function (Builder $query, string $value) {
            $query->where('name', 'like', "%{$value}%")
                ->orWhere('address', 'like', "%{$value}%")
                ->orWhereHas('store', function ($storeQuery) use ($value) {
                    $storeQuery->where('name', 'like', "%{$value}%")
                        ->orWhere('description', 'like', "%{$value}%");
                });
        });
    }

    public function scopeStoreCategories($q, $value)
    {
        $categories = array_filter(
            (array) json_decode($value, true),
            fn($category) => (int) $category !== 0
        );

        if (empty($categories)) {
            return $q;
        }

        $q->whereHas('store.category', function ($categoryQuery) use ($categories) {
            $categoryQuery->whereIn('id', $categories);
        });
    }

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
}
