<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Translatable\HasTranslations;
use Laravel\Fortify\TwoFactorAuthenticatable;
class Admin extends Authenticatable implements HasMedia
{
    use HasFactory, Notifiable, HasTranslations, HasRoles, TwoFactorAuthenticatable, InteractsWithMedia;
    protected $guard = ['admin'];
    protected $guard_name = 'admin';
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'LIKE', "%{$search}%")
            ->orWhere('email', 'LIKE', "%{$search}%");
    }

    public function scopeActive($query, $value = true)
    {
        return $query->where('is_active', $value);
    }
    public function scopeApplyFilters(Builder $query, Request $request)
    {
        return $query
            ->when($request->filled('is_active'), fn($q) => $q->active($request->input('is_active')))
            ->when($request->input('search'), fn($q, $search) => $q->search($search))
            ->orderBy($request->input('sort', 'id'), $request->input('direction', 'desc'));
    }

}
