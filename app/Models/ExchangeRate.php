<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $fillable = [
        'date',
        'currency',
        'buy_price',
        'sell_price',
        'notes',
        'coins',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];
}
