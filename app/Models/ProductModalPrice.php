<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductModalPrice extends Model
{
    use HasUuids;

    protected $fillable = [
        'product_id',
        'durasi_value',
        'durasi_type',
        'harga',
        'berlaku_mulai',
    ];

    protected $casts = [
        'durasi_value' => 'integer',
        'harga' => 'integer',
        'berlaku_mulai' => 'date',
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
