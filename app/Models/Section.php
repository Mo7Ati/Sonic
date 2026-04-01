<?php

namespace App\Models;

use App\Enums\HomePageSectionsType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Spatie\Translatable\HasTranslations;

class Section extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'type',
        'is_active',
        'order',
        'data',
    ];

    protected $casts = [
        'type' => HomePageSectionsType::class,
        'is_active' => 'boolean',
        'order' => 'integer',
        'data' => 'array',
    ];

    protected static function booted()
    {
        static::addGlobalScope('ordered', function (Builder $builder) {
            $builder->orderBy('order');
        });
    }

    public function scopeActive(Builder $query, $value = true): Builder
    {
        return $query->where('is_active', $value);
    }
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order');
    }
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeApplyFilters($query, Request $request)
    {
        return $query
            ->when($request->input('type'), fn($q, $type) => $q->type($type))
            ->when($request->filled('is_active'), fn($q) => $q->active($request->input('is_active')))
            ->orderBy($request->input('sort', 'id'), $request->input('direction', 'desc'));
    }
}
