<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataAkun extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'nama_akun',
        'product_id',
        'username_akun',
        'password_akun',
        'link_login_akun',
        'pj_akun',
        'harga_satuan',
        'deskripsi',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'password_akun', // Sembunyikan password dari serialization
    ];

    public function pj()
    {
        return $this->belongsTo(User::class, 'pj_akun');
    }

    /**
     * Produk induk akun ini (mis. "Grammarly 1" → produk "Grammarly").
     * Boleh kosong: akun lama yang belum ditautkan tetap jalan.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Akun ini private? Diambil dari produk induknya — TIDAK ada penanda
     * terpisah di sini, supaya tidak mungkin bentrok dengan produk.
     * Belum ditautkan → dianggap bukan private (aman: modal tidak dicatat).
     */
    public function isPrivate(): bool
    {
        return optional($this->product)->tipe_akun === 'private';
    }

    // scope
    public function scopeAvailable($query)
    {
        return $query->where('status', 'active');
    }
}
