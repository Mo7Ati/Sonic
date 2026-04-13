<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'store_id'])]
class OptionGroup extends Model
{
    use HasTranslations, SoftDeletes;

    protected $casts = [
        'name' => 'array',
    ];

    public array $translatable = ['name'];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->store_id = auth()->guard('store')->id();
        });
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(Option::class);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'LIKE', "%{$search}%");
    }

    public function scopeApplyFilters($query, Request $request)
    {
        return $query
            ->when($request->input('search'), fn ($q, $search) => $q->search($search))
            ->orderBy($request->input('sort', 'id'), $request->input('direction', 'desc'));
    }
}
