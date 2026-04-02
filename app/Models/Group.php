<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'is_active',
        'stores',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'name' => 'array',
        'stores' => 'array',
    ];

    /**
     * Translatable attributes
     *
     * @var array<string>
     */
    public $translatable = ['name'];

    /**
     * Get the sections that belong to this group.
     */
    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    /**
     * Scope a query to only include groups of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    // public function getItemsAttribute()
    // {
    //     return Branch::query()->whereIn('store_id', $this->stores)->with('store')->get();
    // }

}
