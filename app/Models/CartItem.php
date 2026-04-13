<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'unit_price',
        'options_data',
        'options_amount',
        'additions_data',
        'additions_amount',
        'total_price',
    ];

    protected $casts = [
        'options_data' => 'array',
        'additions_data' => 'array',
        'unit_price' => 'float',
        'options_amount' => 'float',
        'additions_amount' => 'float',
        'total_price' => 'float',
        'quantity' => 'integer',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function recalculateTotal(): self
    {
        $this->total_price = ($this->unit_price + $this->options_amount + $this->additions_amount) * $this->quantity;

        return $this;
    }
}
