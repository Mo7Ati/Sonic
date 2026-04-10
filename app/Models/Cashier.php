<?php

namespace App\Models;

use App\Enums\BranchStatusEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

#[Fillable(['name', 'phone_number', 'password', 'branch_id', 'email'])]
#[Hidden(['password'])]
class Cashier extends Authenticatable
{
    use HasFactory;

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function setPasswordAttribute(?string $value): void
    {
        if (! filled($value)) {
            return;
        }

        $this->attributes['password'] = Hash::isHashed((string) $value)
            ? $value
            : Hash::make($value);
    }

    public function getIsAvailableBranchAttribute(): bool
    {
        return $this->branch?->status === BranchStatusEnum::AVAILABLE;
    }
}
