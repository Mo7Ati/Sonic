<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class orderItems extends Pivot
{
    use SoftDeletes;

    protected $table = 'order_items';
    public $incrementing = true;
    public $timestamps = true;
    protected $fillable = [
        'order_id',
        'product_id',
        'product_data',
        'options_amount',
        'options_data',
        'additions_amount',
        'additions_data',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'product_data' => 'array',
        'options_data' => 'array',
        'additions_data' => 'array',
        'unit_price' => 'float',
        'options_amount' => 'float',
        'additions_amount' => 'float',
        'total_price' => 'float',
        'quantity' => 'int',
        'deleted_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
