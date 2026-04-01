<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;
class Category extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasTranslations;
    protected $fillable = [
        'name',
        'slug',
        'store_id',
        'description',
        'is_active',
    ];


    protected $casts = [
        'name' => 'array',
        'description' => 'array',
    ];

    public array $translatable = ['name', 'description'];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->store_id = auth()->guard('store')->id();
            $model->slug = Str::slug($model->getTranslation('name', 'en'));
        });

        static::updating(function ($model) {
            if ($model->isDirty('name')) {
                $model->slug = Str::slug($model->getTranslation('name', 'en'));
            }
        });
    }
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }

    public function scopeApplyFilters(Builder $query, Request $request)
    {
        return $query
            ->when($request->filled('is_active'), fn($q) => $q->active($request->input('is_active')))
            ->when($request->input('search'), fn($q, $search) => $q->search($search))
            ->orderBy($request->input('sort', 'id'), $request->input('direction', 'desc'));
    }

    public function scopeSearch($query, $search)
    {
        return $query
            ->where('name', 'LIKE', "%{$search}%")
            ->orWhere('description', 'LIKE', "%{$search}%");
    }
    public function scopeActive($query, $value = true)
    {
        return $query->where('is_active', $value);
    }
}
