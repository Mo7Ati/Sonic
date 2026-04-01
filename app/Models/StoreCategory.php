<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'slug', 'description'])]

class StoreCategory extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia, SoftDeletes;

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
    ];

    public array $translatable = ['name', 'description'];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->slug = Str::slug($model->getTranslation('name', 'en'));
        });

        static::updating(function ($model) {
            if ($model->isDirty('name')) {
                $model->slug = Str::slug($model->getTranslation('name', 'en'));
            }
        });
    }

    public function stores()
    {
        return $this->hasMany(Store::class, 'category_id', 'id');
    }

    public function scopeSearch($query, $search)
    {
        return $query->whereLike('name', "%$search%")
            ->orWhereLike('description', "%$search%");
    }

    public function scopeApplyFilters($query, Request $request)
    {
        return $query
            ->when($request->input('search'), fn($q, $search) => $q->search($search))
            ->orderBy($request->input('sort', 'id'), $request->input('direction', 'desc'));
    }
}
