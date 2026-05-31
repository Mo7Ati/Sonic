<?php

namespace App\Models;

use App\Enums\PaymentMethodTypeEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'branch_id',
    'type',
    'beneficiary_name',
    'account_number',
    'phone_number',
    'instructions',
    'is_active',
])]
class BranchPaymentMethod extends Model
{
    use HasFactory;

    protected $casts = [
        'type' => PaymentMethodTypeEnum::class,
        'is_active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Snapshot of this method stored on an order at checkout time, so the
     * order keeps the pay details even if the branch later edits or deletes it.
     *
     * @return array<string, mixed>
     */
    public function snapshot(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'beneficiary_name' => $this->beneficiary_name,
            'account_number' => $this->account_number,
            'phone_number' => $this->phone_number,
            'instructions' => $this->instructions,
        ];
    }
}
