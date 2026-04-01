<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class Admin extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Hash the password when a non-empty value is set; leave the stored hash unchanged when the field is omitted or blank (e.g. Filament edit without changing password).
     */
    public function setPasswordAttribute(?string $value): void
    {
        if (!filled($value)) {
            return;
        }

        $this->attributes['password'] = Hash::isHashed((string) $value)
            ? $value
            : Hash::make($value);
    }
}
