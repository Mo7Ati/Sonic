<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductOption extends Pivot
{
    public $incrementing = true;

    protected $table = 'product_options';

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'quantity' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class);
    }
}
