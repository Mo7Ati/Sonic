<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

class Address extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'customer_id',
        'session_id',
        'fields',
    ];

    protected $casts = [
        'fields' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Resolve addresses for the current request — authenticated customer or guest session.
     *
     * @return Builder<self>
     */
    public static function resolveQueryFor(Request $request): Builder
    {
        if ($request->user()) {
            return static::where('customer_id', $request->user()->id);
        }

        return static::where('session_id', $request->header('X-Session-Id'));
    }

    /**
     * Merge guest session addresses into an authenticated customer's account.
     */
    public static function mergeGuestAddresses(string $sessionId, int $customerId): void
    {
        static::where('session_id', $sessionId)
            ->update([
                'customer_id' => $customerId,
                'session_id' => null,
            ]);
    }
}
