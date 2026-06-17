<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    protected $fillable = [
        'code',
        'discount_percent',
        'minimum_transaction',
        'max_usage',
        'used',
        'expired_at',
    ];

    protected $casts = [
        'minimum_transaction' => 'float',
        'expired_at' => 'datetime',
    ];
}