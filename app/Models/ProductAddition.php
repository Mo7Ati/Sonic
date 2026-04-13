<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductAddition extends Pivot
{
    public $incrementing = true;

    public $timestamps = false;

    protected $table = 'product_additions';

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function addition(): BelongsTo
    {
        return $this->belongsTo(Addition::class);
    }
}
