<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbandonedCart extends Model
{
    protected $fillable = [
        'email',
        'items',
        'total',
        'reminded_at',
        'recovered_at',
    ];

    protected $casts = [
        'items' => 'array',
        'reminded_at' => 'datetime',
        'recovered_at' => 'datetime',
    ];
}
