<?php

namespace App\Models;

use App\Enums\SectionItemEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SectionItem extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'section_id',
        'data',
        'ordered',
        'is_active',
        'group_id',
        'store_id',
        'store_category_id',
        'cooperative_id',
        'governorate_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'type' => SectionItemEnum::class,
    ];

    /**
     * Get the section that owns the section item.
     */
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }

    public function storeCategory()
    {
        return $this->belongsTo(StoreCategory::class, 'store_category_id', 'id');
    }

    /**
     * Scope a query to only include section items for a specific section.
     */
    public function scopeForSection($query, $sectionId)
    {
        return $query->where('section_id', $sectionId);
    }

    /**
     * Scope a query to only include section items of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to order by the ordered field.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('ordered', 'asc');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('section-item')
            ->useDisk(config('filesystems.default'))
            ->singleFile();
    }
}
