<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CashFlow extends Model
{
    use HasUuids;

    protected $fillable = [
        'amount',
        'type',
        'transaction_date',
        'category',
        'description',
        'sourceable_id',
        'sourceable_type',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
    ];

    // relationship
    public function sourceable(): MorphTo
    {
        return $this->morphTo();
    }
}
