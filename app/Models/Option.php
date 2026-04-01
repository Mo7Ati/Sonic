<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Spatie\Translatable\HasTranslations;

class Option extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'store_id',
        'is_active',
    ];

    protected $casts = [
        'name' => 'array',
        'is_active' => 'boolean',
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
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_options', 'option_id', 'product_id');
    }
    public function scopeSearch($query, $search)
    {
        return $query->when($search, function ($query) use ($search) {
            $query->where('name', 'LIKE', "%{$search}%");
        });
    }

    public function scopeActive($query, $value = true)
    {
        return $query->where('is_active', $value);
    }

    public function scopeApplyFilters($query, Request $request)
    {
        return $query
            ->when($request->input('search'), fn($q, $search) => $q->search($search))
            ->when($request->filled('is_active'), fn($q) => $q->active($request->input('is_active')))
            ->orderBy($request->input('sort', 'id'), $request->input('direction', 'desc'));
    }
}
