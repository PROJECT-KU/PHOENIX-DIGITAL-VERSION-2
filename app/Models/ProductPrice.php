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
        // Dipisah ribuan: paket kredit bernilai 1500 terbaca "1.500 kredit",
        // bukan "1500 kredit". Satuan waktu tidak terpengaruh (1, 3, 12).
        return number_format($this->durasi_value, 0, ',', '.').' '.$this->durasi_type;
    }
}
