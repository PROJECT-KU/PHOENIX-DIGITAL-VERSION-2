<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPrice extends Model
{
    use HasUuids;

    protected $fillable = [
        'product_id',
        'durasi_value',
        'durasi_type',
        'harga',
    ];

    protected $casts = [
        'durasi_value' => 'integer',
        'harga' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getLabelAttribute(): string
    {
        return $this->durasi_value.' '.$this->durasi_type;
    }
}
