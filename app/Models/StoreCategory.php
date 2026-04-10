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

#[Fillable(['name', 'slug', 'description', 'parent_id'])]

class StoreCategory extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia, SoftDeletes;

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
    ];

    public array $translatable = ['name', 'description'];

    protected static function booted(): void
    {
        static::creating(function (StoreCategory $model): void {
            $model->assignUniqueSlug();
        });

        static::updating(function (StoreCategory $model): void {
            if ($model->isDirty(['name', 'parent_id'])) {
                $model->assignUniqueSlug();
            }
        });
    }

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

    public function assignUniqueSlug(): void
    {
        $base = $this->computeSlugBase();
        $slug = $base;
        $suffix = 1;

        while ($this->slugExists($slug)) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        $this->slug = $slug;
    }

    protected function computeSlugBase(): string
    {
        $nameSlug = Str::slug($this->getTranslation('name', 'en'));

        if ($this->parent_id === null) {
            return $nameSlug;
        }

        $parent = $this->relationLoaded('parent')
            ? $this->parent
            : static::query()->find($this->parent_id);

        if ($parent === null) {
            return $nameSlug;
        }

        return Str::slug("{$parent->slug}-{$nameSlug}");
    }

    protected function slugExists(string $slug): bool
    {
        $query = static::query()->where('slug', $slug);

        if ($this->exists) {
            $query->whereKeyNot($this->getKey());
        }

        return $query->exists();
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
