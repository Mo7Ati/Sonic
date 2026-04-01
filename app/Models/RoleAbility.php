<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleAbility extends Model
{
    protected $fillable = [
        'role_id' , 'ability' , 'type' ,
    ];

    public $timestamps = false ;

}
