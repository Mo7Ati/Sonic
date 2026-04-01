<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Product extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'keywords',
        'price',
        'compare_price',
        'store_id',
        'category_id',
        'is_active',
        'is_accepted',
        'quantity',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'address' => 'array',
        'keywords' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
            if (is_null($model->store_id) && auth()->guard('store')->check()) {
                $model->store_id = auth()->guard('store')->id();
            }
            $model->slug = Str::slug($model->getTranslation('name', 'en'));
        });

        static::updating(function ($model) {
            if ($model->isDirty('name')) {
                $model->slug = Str::slug($model->getTranslation('name', 'en'));
            }
        });
    }

    public array $translatable = ['name', 'description'];


    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id')
            ->withDefault(['name' => 'No Category']);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items', 'product_id', 'order_id');
    }

    public function additions()
    {
        return $this->belongsToMany(
            Addition::class,
            'product_additions',
            'product_id',
            'addition_id'
        )
            ->withPivot(['price']);
    }
    public function options()
    {
        return $this->belongsToMany(Option::class, 'product_options', 'product_id', 'option_id')
            ->withPivot('price')
            ->active();
    }

    /**
     * Scope to filter products for the current authenticated store
     * @param Builder $query
     * @return Builder
     */
    public function scopeForAuthStore(Builder $query): Builder
    {
        return $query->where('store_id', auth()->guard('store')->id());
    }

    public function scopeApplyFilters(Builder $query, Request $request)
    {
        return $query
            ->when($request->filled('is_active'), fn($q) => $q->active($request->input('is_active')))
            ->when($request->filled('is_accepted'), fn($q) => $q->accepted($request->input('is_accepted')))
            ->when($request->input('search'), fn($q, $search) => $q->search($search))
            ->when($request->input('category'), fn($q, $category) => $q->category($category))
            ->when($request->float('minPrice'), fn($q, $minPrice) => $q->where('price', '>=', $minPrice))
            ->when($request->float('maxPrice'), fn($q, $maxPrice) => $q->where('price', '<=', $maxPrice))
            ->orderBy($request->input('sort', 'id'), $request->input('direction', 'desc'));
    }

    public function scopeAccepted($query, $value = true)
    {
        return $query->where('is_accepted', $value);
    }
    public function scopeActive($query, $value = true)
    {
        return $query->where('is_active', $value);
    }

    public function scopeSearch($query, $search)
    {
        return $query->whereAny([
            'name',
            'description',
            'keywords',
        ], 'LIKE', "%{$search}%");
    }

    public function scopeCategory(Builder $query, $category_slug): Builder
    {
        return $query->whereHas('category', function ($query) use ($category_slug) {
            $query->where('slug', $category_slug);
        });
    }

    public function syncAdditions(array $additions = []): void
    {
        $this->additions()->sync(
            collect($additions)
                ->mapWithKeys(fn($item) => [
                    $item['addition_id'] => ['price' => $item['price']],
                ])
        );
    }

    public function syncOptions(array $options = []): void
    {
        $this->options()->sync(
            collect($options)
                ->mapWithKeys(fn($item) => [
                    $item['option_id'] => ['price' => $item['price']],
                ])
        );
    }

    public function getDiscountPercentageAttribute(): float|null
    {
        if ($this->compare_price && $this->compare_price > $this->price) {
            return round((($this->compare_price - $this->price) / $this->compare_price) * 100);
        }
        return null;
    }

    /**
     * Customers who have this product in their wishlist.
     */
    public function wishlistedByCustomers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'wishlist_items', 'product_id', 'customer_id');
    }

}
