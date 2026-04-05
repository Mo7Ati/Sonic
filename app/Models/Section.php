<?php

namespace App\Models;

use App\Enums\SectionEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

#[Fillable(['title', 'description', 'type', 'group_id', 'data', 'is_active', 'ordered'])]
class Section extends Model
{
    use HasTranslations, SoftDeletes;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'data' => 'array',
        'is_active' => 'boolean',
        'type' => SectionEnum::class,
    ];

    public $translatable = ['title', 'description'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->ordered = Section::max('ordered') + 1;
        });
    }

    /**
     * Get the group that owns the section.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the section items that belong to this section.
     */
    public function items()
    {
        return $this->hasMany(SectionItem::class);
    }

    /**
     * Scope a query to only include sections for a specific group.
     */
    public function scopeForGroup($query, $groupId)
    {
        return $query->where('group_id', $groupId);
    }

    /**
     * Scope a query to only include sections without a group.
     */
    public function scopeWithoutGroup($query)
    {
        return $query->whereNull('group_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('ordered');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
