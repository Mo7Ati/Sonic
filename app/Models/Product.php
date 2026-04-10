<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'slug', 'description', 'keywords', 'store_id', 'category_id', 'is_active', 'is_accepted', 'quantity'])]

class Product extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia;

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'address' => 'array',
        'keywords' => 'array',
        'is_active' => 'boolean',
        'is_accepted' => 'boolean',
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

    public array $translatable = ['name', 'description', 'keywords'];

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

    public function scopeApplyFilters(Builder $query, Request $request)
    {
        return $query
            ->when($request->filled('is_active'), fn ($q) => $q->active($request->input('is_active')))
            ->when($request->filled('is_accepted'), fn ($q) => $q->accepted($request->input('is_accepted')))
            ->when($request->input('search'), fn ($q, $search) => $q->search($search))
            ->when($request->input('category'), fn ($q, $category) => $q->category($category))
            ->when($request->float('minPrice'), fn ($q, $minPrice) => $q->where('price', '>=', $minPrice))
            ->when($request->float('maxPrice'), fn ($q, $maxPrice) => $q->where('price', '<=', $maxPrice))
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
}
