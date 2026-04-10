<?php

namespace App\Models;

use App\Observers\ProductObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

#[ObservedBy([ProductObserver::class])]
#[Fillable(['uuid', 'name', 'description', 'keywords', 'store_id', 'category_id', 'price', 'compare_price', 'is_active', 'is_accepted'])]

class Product extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia;

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'keywords' => 'array',
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_accepted' => 'boolean',
    ];

    public array $translatable = ['name', 'description', 'keywords'];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
            if (is_null($model->store_id) && auth()->guard('store')->check()) {
                $model->store_id = auth()->guard('store')->id();
            }
        });
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
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
        )->withPivot(['price']);
    }

    public function options()
    {
        return $this->belongsToMany(
            Option::class,
            'product_options',
            'product_id',
            'option_id'
        )->withPivot('price');
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class)
            ->withPivot(['price', 'compare_price', 'is_available', 'quantity'])
            ->withTimestamps();
    }

    public function scopeApplyFilters(Builder $query, Request $request)
    {
        return $query
            ->when($request->filled('is_active'), fn($q) => $q->active($request->input('is_active')))
            ->when($request->filled('is_accepted'), fn($q) => $q->accepted($request->input('is_accepted')))
            ->when($request->input('search'), fn($q, $search) => $q->search($search))
            ->when($request->float('minPrice'), fn($q, $minPrice) => $q->where('price', '>=', $minPrice))
            ->when($request->float('maxPrice'), fn($q, $maxPrice) => $q->where('price', '<=', $maxPrice))
            ->orderBy($request->input('sort', 'id'), $request->input('direction', 'desc'));
    }

    public function scopeAccepted($query)
    {
        return $query->where('is_accepted', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, $search)
    {
        return $query->whereAny([
            'name',
            'description',
            'keywords',
        ], 'LIKE', "%{$search}%");
    }
}
