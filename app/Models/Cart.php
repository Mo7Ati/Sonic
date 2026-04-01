<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'cookie_id',


    ];

    protected static function booted()
    {

        static::creating(function (Cart $cart) {
            $cart->id = Str::uuid();
        });

        static::addGlobalScope('cookie_id', function (Builder $builder) {
            $user_id = Auth::id();
            if (!$user_id) {
                $builder->where('cookie_id', static::getCookieId())
                    ->whereNull('user_id');
            } else {
                $builder->where('cookie_id', static::getCookieId())
                    ->where('user_id', $user_id);
            }
        });
    }

    
    public static function getCookieId()
    {
        $cookie_id = Cookie::get('cart');
        if (!$cookie_id) {
            $cookie_id = Str::uuid();
            Cookie::queue('cart', $cookie_id, 60 * 24 * 30);

        }

        return $cookie_id;
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
