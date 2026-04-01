<?php

namespace App\Models;


use Bavix\Wallet\Models\Transaction as BavixTransaction;


class Transaction extends BavixTransaction
{
    protected $fillable = [
        'payable_type',
        'payable_id',
        'wallet_id',
        'uuid',
        'type',
        'amount',
        'confirmed',
        'meta',
        'created_at',
        'updated_at',
        'source_type',
        'source_id',
    ];

    public function source()
    {
        return $this->morphTo();
    }
}
