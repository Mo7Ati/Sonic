<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'session_id',
        'branch_id',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function getItemsCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    public function getSubtotalAttribute(): float
    {
        return $this->items->sum('total_price');
    }

    /**
     * Resolve the current cart for the request — authenticated customer or guest session.
     */
    public static function resolveFor(Request $request): ?self
    {
        $user = $request->user('sanctum');
        if ($user) {
            return static::where('customer_id', $user->id)
                ->first();
        }

        $sessionId = $request->header('X-Session-Id');

        if ($sessionId) {
            return static::where('session_id', $sessionId)->first();
        }

        return null;
    }

    /**
     * Find or create a cart for the current request context.
     */
    public static function resolveOrCreateFor(Request $request, int $branchId): self
    {
        $user = $request->user('sanctum');
        if ($user) {
            return static::firstOrCreate(
                ['customer_id' => $user->id],
                ['branch_id' => $branchId],
            );
        }

        $sessionId = $request->header('X-Session-Id');

        return static::firstOrCreate(
            ['session_id' => $sessionId],
            ['branch_id' => $branchId],
        );
    }

    /**
     * Merge a guest session cart into an authenticated customer's cart.
     * Transfers items to the customer cart or creates one from the guest cart.
     */
    public static function mergeGuestCart(string $sessionId, int $customerId): void
    {
        $guestCart = static::where('session_id', $sessionId)->first();

        if (!$guestCart) {
            return;
        }

        $customerCart = static::where('customer_id', $customerId)->first();

        if (!$customerCart) {
            $guestCart->update([
                'customer_id' => $customerId,
                'session_id' => null,
            ]);

            return;
        }

        if ($guestCart->branch_id === $customerCart->branch_id) {
            $guestCart->items->each(function (CartItem $guestItem) use ($customerCart) {
                $customerCart->items()->create($guestItem->only([
                    'product_id',
                    'quantity',
                    'unit_price',
                    'options_data',
                    'options_amount',
                    'additions_data',
                    'additions_amount',
                    'total_price',
                ]));
            });
        }

        $guestCart->items()->delete();
        $guestCart->delete();
    }
}
