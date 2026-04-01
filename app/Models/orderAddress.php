<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class orderAddress extends Model
{
    protected $table = 'order_address';
    public $timestamps = false;

    protected $fillable = [
        'type',
        'order_id',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'state',
        'city',
        'country',
        'postal_code'
    ];

    public function getNameAttribute()
    {
        return "$this->first_name" . " " . "$this->last_name";

    }


}
