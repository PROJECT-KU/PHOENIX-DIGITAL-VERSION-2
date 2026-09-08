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
        'jenis_layanan',
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

    /**
     * Jenis PEMERIKSAAN yang ditambahkan add-on ini, atau null bila add-on
     * bukan pemeriksaan (mis. "plagiasi di bawah 30%" — itu target parafrase,
     * bukan kuota pengecekan terpisah).
     */
    /**
     * Jenis pekerjaan yang dihasilkan add-on ini, atau null bila tidak
     * menghasilkan berkas apa pun untuk pelanggan.
     *
     * Dinyatakan tegas lewat kolomnya, TIDAK disimpulkan dari pakai_exclude
     * lagi: pada add-on jaminan, pakai_exclude berarti "pengecekannya memakai
     * setelan exclude", bukan "ini pengecekan tersendiri" — dan penyimpulan itu
     * dulu melahirkan kuota hantu yang tak pernah bisa dipakai.
     */
    public function jenisLayanan(): ?string
    {
        $jenis = trim((string) $this->jenis_layanan);

        return $jenis !== '' ? $jenis : null;
    }
}
