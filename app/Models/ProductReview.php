<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Ulasan pembeli — untuk produk satuan ATAU paket bundling.
 *
 * `product_id` menyimpan id targetnya; `jenis` membedakan tabelnya
 * ('produk' → products, 'paket' → product_bundlings). Ulasan lama semuanya
 * berjenis 'produk' lewat nilai bawaan kolom.
 */
class ProductReview extends Model
{
    public const JENIS_PRODUK = 'produk';

    public const JENIS_PAKET = 'paket';

    protected $fillable = [
        'product_id',
        'jenis',
        'nama',
        'rating',
        'ulasan',
        'status',
    ];

    /** Nilai bawaan di sisi model juga, supaya instans baru sudah tahu jenisnya. */
    protected $attributes = [
        'jenis' => self::JENIS_PRODUK,
    ];

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /** Ulasan untuk satu target: produk atau paket tertentu. */
    public function scopeUntuk($query, string $jenis, $id)
    {
        return $query->where('jenis', $jenis)->where('product_id', $id);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /** Paket bundling yang diulas (hanya bermakna bila jenisnya 'paket'). */
    public function paket()
    {
        return $this->belongsTo(ProductBundlings::class, 'product_id');
    }

    /** Nama produk atau paket yang diulas, untuk halaman moderasi admin. */
    public function namaTarget(): string
    {
        return $this->jenis === self::JENIS_PAKET
            ? ($this->paket->nama_paket ?? '—')
            : ($this->product->nama_akun ?? '—');
    }
}
