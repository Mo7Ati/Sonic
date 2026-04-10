<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'store_id', 'description', 'is_active'])]
class Category extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia, SoftDeletes;

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'is_active' => 'boolean',
    ];

    public array $translatable = ['name', 'description'];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->store_id = auth()->guard('store')->id();
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }

    public function scopeApplyFilters(Builder $query, Request $request)
    {
        return $query
            ->when($request->filled('is_active'), fn ($q) => $q->active($request->input('is_active')))
            ->when($request->input('search'), fn ($q, $search) => $q->search($search))
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
