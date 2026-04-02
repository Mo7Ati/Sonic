<?php

namespace App\Models;

use App\Enums\BranchStatusEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

#[Fillable(['name', 'phone_number', 'password', 'branch_id', 'email'])]
#[Hidden(['password'])]
class Cashier extends Model
{
    use HasFactory;

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    // Hash the password when setting it
    public function setPasswordAttribute($value)
    {
        if (! filled($value)) {
            return;
        }

        $this->attributes['password'] = Hash::isHashed((string) $value)
            ? $value
            : Hash::make($value);
    }

    public function getIsAvailableBranchAttribute()
    {
        return $this->branch?->status === BranchStatusEnum::AVAILABLE;
    }
}
