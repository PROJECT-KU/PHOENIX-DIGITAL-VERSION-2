<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Add-on/tambahan opsional pada produk JASA — dinamis, diatur admin.
 * Contoh: "+ Cek Plagiasi Turnitin (Rp1.000)" pada Cek Plagiasi AI, atau
 * "Target < 20% (Rp50.000)" pada Jasa Parafrase.
 */
class ProductAddon extends Model
{
    use HasUuids;

    protected $fillable = [
        'product_id',
        'nama',
        'keterangan',
        'harga',
        'urutan',
        'aktif',
        'pakai_exclude',
        'cek_ai',
    ];

    protected $casts = [
        'harga' => 'integer',
        'urutan' => 'integer',
        'aktif' => 'boolean',
        'pakai_exclude' => 'boolean',
        'cek_ai' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    /** Label siap tampil: "Nama (+Rp50.000)". */
    public function label(): string
    {
        return $this->nama.' (+Rp '.number_format($this->harga, 0, ',', '.').')';
    }
}
