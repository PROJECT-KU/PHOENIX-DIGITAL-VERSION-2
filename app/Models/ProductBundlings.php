<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductBundlings extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'product_bundlings';

    protected $fillable = [
        'nama_paket',
        'product_1',
        'product_2',
        'product_3',
        'product_4',
        'product_5',
        'durations',
        'harga_awal',
        'harga_bundling',
        'gambar',
        'deskripsi',
        'status',
    ];

    protected $casts = [
        'durations' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Daftar produk paket + durasinya (default 1 bulan jika belum diset)
    public function bundleProducts(): array
    {
        $out = [];
        foreach (['product_1', 'product_2', 'product_3', 'product_4', 'product_5'] as $col) {
            if (! $this->$col) {
                continue;
            }
            $dur = $this->durations[$col] ?? [];
            $out[] = [
                'product_id' => $this->$col,
                'duration_value' => (int) ($dur['value'] ?? 1),
                'duration_type' => $dur['type'] ?? 'bulan',
            ];
        }

        return $out;
    }

    public function product1()
    {
        return $this->belongsTo(Product::class, 'product_1');
    }

    public function product2()
    {
        return $this->belongsTo(Product::class, 'product_2');
    }

    public function product3()
    {
        return $this->belongsTo(Product::class, 'product_3');
    }

    public function product4()
    {
        return $this->belongsTo(Product::class, 'product_4');
    }

    public function product5()
    {
        return $this->belongsTo(Product::class, 'product_5');
    }
}
