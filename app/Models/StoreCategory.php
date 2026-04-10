<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name','description', 'parent_id'])]

class StoreCategory extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia, SoftDeletes;

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
    ];

    public array $translatable = ['name', 'description'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * The stores that the category belongs to.
     *
     * @return BelongsToMany<Store>
     */
    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'category_stores', 'category_id', 'store_id');
    }

    public function scopeSearch($query, $search)
    {
        return $query->whereLike('name', "%$search%")
            ->orWhereLike('description', "%$search%");
    }

    public function scopeApplyFilters($query, Request $request)
    {
        return $query
            ->when($request->input('search'), fn ($q, $search) => $q->search($search))
            ->orderBy($request->input('sort', 'id'), $request->input('direction', 'desc'));
    }
}
