<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'nama_akun',
        'username_akun',
        'password_akun',
        'link_login_akun',
        'pj_akun',
        'deskripsi',
        'harga_satuan',
        'periode',
        'status'
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $hidden = [
        'password_akun', // Sembunyikan password dari serialization
    ];

    // Accessor untuk format harga
    public function getFormattedHargaAttribute(): string
    {
        return number_format($this->harga_satuan, 2, ',', '.');
    }

    // Mutator untuk enkripsi password (opsional, tergantung kebutuhan)
    public function setPasswordAkunAttribute($value): void
    {
        $this->attributes['password_akun'] = bcrypt($value);
    }
}
