<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'status',
        'payment_status',
        'cancelled_reason',
        'customer_id',
        'customer_data',
        'store_id',
        'address_id',
        'address_data',
        'total',
        'total_items_amount',
        'delivery_amount',
        'tax_amount',
        'notes',
        'stripe_session_id',
        'checkout_group_id',
    ];

    protected $casts = [
        'customer_data' => 'array',
        'address_data' => 'array',
        'total' => 'float',
        'total_items_amount' => 'float',
        'delivery_amount' => 'float',
        'tax_amount' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'status' => OrderStatusEnum::class,
        'payment_status' => PaymentStatusEnum::class,
    ];

    /*
     * Relationships
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_items', 'order_id', 'product_id')
            ->using(orderItems::class)
            ->withPivot('quantity', 'unit_price', 'product_data', 'options_amount', 'options_data', 'additions_amount', 'additions_data', 'total_price')
            ->withTimestamps();
    }
    public function items()
    {
        return $this->hasMany(orderItems::class, 'order_id');
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function scopeSearch($query, $search)
    {
        return $query
            ->where('id', 'LIKE', "%{$search}%")
            ->orWhere('status', 'LIKE', "%{$search}%")
            ->orWhere('payment_status', 'LIKE', "%{$search}%")
            ->orWhereHas('customer', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone_number', 'LIKE', "%{$search}%");
            })
            ->orWhereHas('store', function ($q) use ($search) {
                $q->where('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%");
            });
    }
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
    public function scopePaymentStatus($query, $payment_status)
    {
        return $query->where('payment_status', $payment_status);
    }

    public function scopeApplyFilters($query, Request $request)
    {
        return $query
            ->when($request->input('search'), fn($q, $search) => $q->search($search))
            ->when($request->input('status'), fn($q, $status) => $q->status($status))
            ->when($request->input('payment_status'), fn($q, $payment_status) => $q->paymentStatus($payment_status))
            ->orderBy($request->input('sort', 'id'), $request->input('direction', 'desc'));
    }
}
